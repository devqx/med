<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/17/14
 * Time: 1:37 PM
 */
class LabGroupDAO
{

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabSpecimenDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabGroup.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientLabs.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabSpecimen.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function getPatientLabGroups($pid, $page = 0, $pageSize = 10, $getFull = FALSE, $pdo = null)
	{
		$sql = "SELECT * FROM lab_requests WHERE patient_id = $pid ORDER BY time_entered DESC";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$labGroups = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$labGroup = new LabGroup();
				$spc = array();
				$pref_specs_ids = !empty($row['preferred_specimens']) ? explode(",", $row['preferred_specimens']) : [];
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, null);
					$requestedBy = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
					foreach ($pref_specs_ids as $ps) {
						$spc[] = (new LabSpecimenDAO())->getSpecimen($ps, $pdo);
					}
					$requestData = (new PatientLabDAO())->getPatientLabsByGroupCode($row['lab_group_id'], $row['patient_id'], TRUE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$requestedBy = new StaffDirectory($row['requested_by']);
					$hosp = new Clinic($row['hospid']);
					foreach ($pref_specs_ids as $ps) {
						$spc[] = new LabSpecimen($ps);
					}
					$requestData = [];
				}
				$labGroup->setId($row['id']);
				$labGroup->setGroupName($row['lab_group_id']);
				$labGroup->setPatient($pat);
				$labGroup->setRequestedBy($requestedBy);
				$labGroup->setRequestTime($row['time_entered']);
				$labGroup->setRequestNote($row['request_note']);
				$labGroup->setPreferredSpecimens($spc);
				$labGroup->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				$labGroup->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				$labGroup->setRequestData($requestData);
				$labGroup->setUrgent((bool)$row['urgent']);
				$labGroups[] = $labGroup;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$labGroups = null;
		}

		$results = (object)null;
		$results->data = $labGroups;
		$results->total = $total;
		$results->page = $page;
		return $results;
	}

	function getPatientLabGroupsSummary($pid, $page = 0, $pageSize = 10, $getFull = FALSE, $start = null, $stop = null, $pdo = null)
	{
		if ($start == null) {
			$dateStart = '1970-01-01';
		} else {
			$dateStart = date("Y-m-d", strtotime($start));
		}
		if ($stop == null) {
			$dateStop = date("Y-m-d");
		} else {
			$dateStop = date("Y-m-d", strtotime($stop));
		}

		if (isset($start, $stop)) {
			list($dateStart, $dateStop) = [min($dateStart, $dateStop), max($dateStart, $dateStop)];
		}
		$sql = "SELECT * FROM lab_requests WHERE patient_id = $pid AND DATE(time_entered) BETWEEN '$dateStart' AND '$dateStop' ORDER BY time_entered DESC";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$labGroups = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM lab_requests WHERE patient_id = $pid AND DATE(time_entered) BETWEEN '$dateStart' AND '$dateStop' ORDER BY time_entered DESC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$labGroup = new LabGroup();
				$spc = array();
				$pref_specs_ids = !empty($row['preferred_specimens']) ? explode(",", $row['preferred_specimens']) : [];
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, null);
					$requestedBy = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
					foreach ($pref_specs_ids as $ps) {
						$spc[] = (new LabSpecimenDAO())->getSpecimen($ps, $pdo);
					}
					$requestData = (new PatientLabDAO())->getPatientLabsByGroupCode($row['lab_group_id'], TRUE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$requestedBy = new StaffDirectory($row['requested_by']);
					$hosp = new Clinic($row['hospid']);
					foreach ($pref_specs_ids as $ps) {
						$spc[] = new LabSpecimen($ps);
					}
					$requestData = [];
				}
				$labGroup->setId($row['id']);
				$labGroup->setGroupName($row['lab_group_id']);
				$labGroup->setPatient($pat);
				$labGroup->setRequestedBy($requestedBy);
				$labGroup->setRequestTime($row['time_entered']);
				$labGroup->setRequestNote($row['request_note']);
				$labGroup->setPreferredSpecimens($spc);
				$labGroup->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				$labGroup->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				$labGroup->setRequestData($requestData);
				$labGroup->setUrgent((bool)$row['urgent']);
				$labGroups[] = $labGroup;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$labGroups = null;
		}

		$results = (object)null;
		$results->data = $labGroups;
		$results->total = $total;
		$results->page = $page;
		return $results;
	}

	function getLabGroup($id, $patientId, $getFull = FALSE, $pdo = null)
	{
		$labGroup = new LabGroup();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM lab_requests WHERE lab_group_id='$id' AND patient_id=$patientId";

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$spc = array();
				$pref_specs_ids = !empty($row['preferred_specimens']) ? explode(",", $row['preferred_specimens']) : [];
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$requestedBy = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
					$requestData = (new PatientLabDAO())->getPatientLabsByGroupCode($row['lab_group_id'], null,TRUE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$requestedBy = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
					$hosp = new Clinic($row['hospid']);
					$requestData = [];
				}
				foreach ($pref_specs_ids as $ps) {
					$spc[] = (new LabSpecimenDAO())->getSpecimen($ps, $pdo);
				}
				$labGroup->setId($row['id']);
				$labGroup->setGroupName($row['lab_group_id']);
				$labGroup->setPatient($pat);
				$labGroup->setRequestedBy($requestedBy);
				$labGroup->setRequestTime($row['time_entered']);
				$labGroup->setRequestNote($row['request_note']);
				$labGroup->setPreferredSpecimens($spc);
				$labGroup->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				$labGroup->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				$labGroup->setClinic($hosp);
				$labGroup->setRequestData($requestData);
				$labGroup->setUrgent((bool)$row['urgent']);
			} else {
				$labGroup = null;
				$stmt = null;
			}
		} catch (PDOException $e) {
			error_log($e);
			$labGroup = null;
		}
		return $labGroup;
	}

//    function getLab($)
}
