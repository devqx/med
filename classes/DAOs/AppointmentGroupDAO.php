<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AppointmentGroupDAO
 *
 * @author pauldic
 */
class AppointmentGroupDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Resource.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AppointmentGroup.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AppointmentInvitee.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ResourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentInviteeDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentResourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AptClinicDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function add($ag, $pdo = null)
	{
		//$ag = new AppointmentGroup();
		$clinic = $ag->getClinic() ? $ag->getClinic()->getId() : "NULL";
		$type = $ag->getType() ? quote_esc_str($ag->getType()) : quote_esc_str("Visit");
		//just to hold data before we deprecate this field

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$notInTransaction = !$pdo->inTransaction();
			
			try {
				$pdo->beginTransaction();
			} catch (PDOException $e) {
			}
			
			$sql = "INSERT INTO appointment_group (creator, clinic_id, type, is_all_day, description, patient_id, department_id) VALUES " . "('" . $ag->getCreator()->getId() . "', $clinic, $type, " . var_export($ag->isAllDay(), true) . ", '" . escape($ag->getDescription()) . "', " . ($ag->getPatient() == null || $ag->getPatient()->getId() === "" ? 'NULL' : "'" . $ag->getPatient()->getId() . "'") . ", " . ($ag->getDepartment() != null ? $ag->getDepartment()->getId() : "NULL") . ")";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$ag->setId($pdo->lastInsertId());

				$apps = $ag->getAppointments();
				$apps[0]->setGroup($ag);
				$apps_ = (new AppointmentDAO())->add($apps, $pdo);
				if (sizeof($apps_) == 0) {
					if ($notInTransaction) {
						$pdo->rollBack();
					}
					return null;
				}
				if ($ag->getInvitees() !== null && sizeof($ag->getInvitees()) > 0) {
					$invitees = $ag->getInvitees();
					foreach ($invitees as $invitee){
						$invitee->setGroup($ag);
						foreach ($apps as $apt) {
							if($ag->isAllDay()){
								$apt->setEndTime($apt->getStartTime());
							}
							if($invitee->overlaps( date('Y-m-d H:i:s', strtotime($apt->getStartTime())), date('Y-m-d H:i:s', strtotime($apt->getEndTime())), $pdo )){
								if ($notInTransaction) {$pdo->rollBack();}
								require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AppointmentExist.php';
								return (new AppointmentExist());
							}
						}
					}

					$invitees_ = (new AppointmentInviteeDAO())->add($invitees, $pdo);
					if (sizeof($invitees_) === 0) {
						if ($notInTransaction) {
							$pdo->rollBack();
						}
						return null;
					}
				}
				if ($ag->getResource() !== null && sizeof($ag->getResource()) > 0) {
					$resources = $ag->getResource();
					foreach ($resources as $resource){
						//$resource = new AppointmentResource();
						$resource->setGroup($ag);
						foreach ($apps as $apt) {
							if($ag->isAllDay()){
								$apt->setEndTime($apt->getStartTime());
							}
							if($resource->overlaps( date('Y-m-d H:i:s', strtotime($apt->getStartTime())), date('Y-m-d H:i:s', strtotime($apt->getEndTime())), $pdo )){
								if ($notInTransaction) {$pdo->rollBack();}
								require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ResourceUnavailable.php';
								require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ResourceDAO.php';
								return (new ResourceUnavailable( (new ResourceDAO())->getResource($resource->getResource()->getId(), $pdo)->getName() ));
							}
						}
						if($resource->add($pdo)==null){
							if ($notInTransaction) {
								$pdo->rollBack();
							}
							return null;
						}
					}
				}
			} else {
				if ($notInTransaction) {
					$pdo->rollBack();
				}
				return null;
			}
			if ($notInTransaction) {
				$pdo->commit();
			}
		} catch (PDOException $e) {
			errorLog($e);
			$ag = null;
		}
		return $ag;
	}
	
	function checkAppointByClinic($date, $clinicId, $pdo=null){
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$aptClinicLimit = (new AptClinicDAO())->get($clinicId, $pdo)->getALimit();
			$sql = "SELECT * FROM appointment_group ag LEFT JOIN appointment ap ON ap.group_id=ag.id WHERE ag.clinic_id=$clinicId AND DATE('$date')= DATE(start_time)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() < $aptClinicLimit ){
				return true;
			}
			return false;
		}catch (PDOException $e){
			errorLog($e);
			return false;
		}

	}

	function getAppointmentGroup($agid, $getFull = FALSE, $pdo = null)
	{
		$group = new AppointmentGroup();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM appointment_group WHERE id=" . $agid;
//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$group->setId($row['id']);
				$group->setCreateTime($row['create_time']);
				if ($getFull) {
					$creator = (new StaffDirectoryDAO())->getStaff($row['creator'], FALSE, $pdo);
					//$resource = (new ResourceDAO())->getResource($row['resource_id'], $pdo);
					$resource = (new AppointmentResourceDAO())->getForGroup($row['id'], $pdo);
					$patient = (new PatientDemographDAO())->getPatientMin($row['patient_id'], $pdo);
					$invitees = (new AppointmentInviteeDAO())->getAppointmentInviteesByGroup($row['id'], TRUE, $pdo);
				} else {
					$creator = new StaffDirectory();
					$creator->setId($row['creator']);
					$resource = [];
					//$resource = new Resource($row['resource_id']);
					if ($row['patient_id'] != null) {
						$patient = new PatientDemograph();
						$patient->setId($row['patient_id']);
					} else {
						$patient = null;
					}
					$invitees = [];
				}
				$group->setCreator($creator);
				$group->setType($row['type']);
				$group->setIsAllDay($row['is_all_day']);
				$group->setResource($resource);
				$group->setDescription($row['description']);
				$group->setPatient($patient);
				$group->setInvitees($invitees);
				$group->setClinic( (new AptClinicDAO())->get($row['clinic_id'], $pdo));
			} else {
				$group = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$group = null;
		}
		return $group;
	}

	function getAppointmentGroups($getFull = FALSE, $pdo = null)
	{
		$groups = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM appointment_group";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$group = new AppointmentGroup();
				$group->setId($row['id']);
				$group->setCreateTime($row['create_time']);
				if ($getFull) {
					$creator = (new StaffDirectoryDAO())->getStaff($row['creator'], FALSE, $pdo);
					$resource = (new ResourceDAO())->getResource($row['resource_id'], $pdo);
					$patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
				} else {
					$creator = new StaffDirectory();
					$creator->setId($row['creator']);
					$resource = new Resource();
					$resource->setId($row['resource_id']);
					if ($row['patient_id'] != null) {
						$patient = new PatientDemograph();
						$patient->setId($row['patient_id']);
					} else {
						$patient = null;
					}
				}
				$group->setCreator($creator);
				$group->setType($row['type']);
				$group->setIsAllDay($row['is_all_day']);
				$group->setResource($resource);
				$group->setDescription($row['description']);
				$group->setPatient($patient);
				$groups[] = $group;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$groups = array();
		}
		return $groups;
	}

}
