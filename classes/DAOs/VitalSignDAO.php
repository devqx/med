<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of VitalSignDAO
 *
 * @author pauldic
 */
class VitalSignDAO
{
	
	private $conn = null;
	private $all = [];
	private $vitalsRange;
	
	//    private $all = ['Temperature', 'Surface Area', 'Respiration', 'Pulse', 'MUAC', 'Height', 'Head Circumference', 'Weight', 'Blood Pressure'];
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Alert.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.config.vitals.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/VitalSign.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InPatient.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AlertDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.config.vitals.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
			$this->vitalsRange = new VitalsConfig();
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function addVitalSign($vs, $pdo = null)
	{
		// Transactionally dependent add
		$canCommit = false;
		// $vs = new VitalSign();
		$type = $vs->getType();
		$value = $vs->getValue();
		$encounter = $vs->getEncounter() ? $vs->getEncounter()->getId() : "NULL";
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO vital_sign (patient_id, `value`, in_patient_id, type, read_by, encounter_id) VALUES ('" . $vs->getPatient()->getId() . "', '" . $vs->getValue() . "', " . ($vs->getInPatient() === null ? 'NULL' : $vs->getInPatient()->getId()) . ", '" . $vs->getType() . "', '" . $vs->getReadBy()->getId() . "', $encounter)";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$vs->setId($pdo->lastInsertId());
				
				if ($type == 'Pulse' && ($value > VitalsConfig::$maxPulse || $value < VitalsConfig::$minPulse)) {
					$alert = new Alert();
					$alert->setMessage("Pulse Value ($value) is not within the NORMAL range");
					$alert->setType("Pulse");
					$alert->setPatient($vs->getPatient());
					
					@(new AlertDAO())->add($alert, $pdo);
				} else if ($type == 'Blood Pressure') {
					$bp = explode("/", $value);
					$bp_min_range = explode("/", VitalsConfig::$minNormalBP);
					$bp_max_range = explode("/", VitalsConfig::$maxNormalBP);
					$abnormal_high = ($bp[0] > $bp_max_range[0] || $bp[1] > $bp_max_range[1]) ? true : false;
					$abnormal_low = ($bp[0] < $bp_min_range[0] || $bp[1] < $bp_min_range[1]) ? true : false;
					$abnormal = ($abnormal_high === true || $abnormal_low === true) ? true : false;
					
					if ($abnormal === true) {
						$alert = new Alert();
						$alert->setMessage("B/P Value ($value) is not within the NORMAL range");
						$alert->setType("Blood Pressure");
						$alert->setPatient($vs->getPatient());
						
						@(new AlertDAO())->add($alert, $pdo);
					}
					
				} else if ($type == 'Temperature') {
					if (floatval($value) > VitalsConfig::$maxNormalTemperature || floatval($value) < VitalsConfig::$minNormalTemperature) {
						$alert = new Alert();
						$alert->setMessage("Temperature Value (" . $value . ") is not within the NORMAL range");
						$alert->setType("Temperature");
						$alert->setPatient($vs->getPatient());
						
						@(new AlertDAO())->add($alert, $pdo);
					}
				} else if ($type == 'Glucose') {
					if (floatval($value) > VitalsConfig::$normalGlucose) {
						$alert = new Alert();
						$alert->setMessage("Blood Glucose value $value is not normal ");
						$alert->setType("Glucose");
						$alert->setPatient($vs->getPatient());
						
						@(new AlertDAO())->add($alert, $pdo);
					}
				} else if ($type == 'Protein') {
					if (floatval($value) > VitalsConfig::$normalProtein) {
						$alert = new Alert();
						$alert->setMessage("Blood Protein value of $value is not normal ");
						$alert->setType("Protein");
						$alert->setPatient($vs->getPatient());
						
						@(new AlertDAO())->add($alert, $pdo);
					}
				}
			} else {
				$vs = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$vs = null;
		}
		return $vs;
	}
	
	function getVitalSignsByType($pid, $type, $pdo = null)
	{
		$signs = [];
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT *, UNIX_TIMESTAMP(read_date) * 1000 AS dDate FROM vital_sign WHERE type_id=$type AND patient_id=$pid ORDER BY read_date ASC";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$signs[] = $this->get($row['id'], false, $pdo);
			}
			return $signs;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	function get($vid, $getFull = false, $pdo = null)
	{
		$sign = new VitalSign();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT *, UNIX_TIMESTAMP(read_date) * 1000 AS dDate FROM vital_sign WHERE id=" . $vid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$sign->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo);
					$ip = (new InPatientDAO())->getInPatient($row['in_patient_id'], false, $pdo);
					$reader = (new StaffDirectoryDAO())->getStaff($row['read_by'], false, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$ip = new InPatient($row['in_patient_id']);
					$reader = (new StaffDirectoryDAO())->getStaffMin($row['read_by'], $pdo);
				}
				$sign->setPatient($pat);
				$sign->setReadDate($row['read_date']);
				$sign->setUnixTime($row['dDate']);
				$sign->setValue($row['value']);
				$sign->setInPatient($ip);
				$vital = (new VitalDAO())->get($row['type_id'], $pdo);
				$sign->setType($vital);
				
				$abnormal = false;
				
				$values = array_filter(explode('/', $sign->getValue()));
				if ($vital->getMaximum() && $vital->getMinimum()) {
					if (count($values) == 1) {
						$val = $values[0];
						if (floatval($val) > floatval($vital->getMaximum()) || floatval($val) < floatval($vital->getMinimum())) {
							$abnormal = true;
						}
					} else if (count($values) == 2) {
						$maxs = array_filter(explode("/", $vital->getMaximum()));
						$mins = array_filter(explode("/", $vital->getMinimum()));
						if ((floatval($values[0]) > floatval($maxs[0]) || floatval($values[0]) < floatval($mins[0])) || (floatval($values[1]) > floatval($maxs[1]) || floatval($values[1]) < floatval($mins[1]))) {
							$abnormal = true;
						}
					}
				}
				
				$sign->setAbnormal($abnormal)->setReadBy($reader)->setEncounter((new EncounterDAO())->get($row['encounter_id'], false, $pdo));
				
			} else {
				$sign = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$sign = null;
		}
		return $sign;
	}
	
	
	function getPatientLastVitalSigns($pid, $in_patient_id = null, $getFull = false, $types = [], $pdo = null)
	{
		$signs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if (count($types) == 0) {
				$sql = "SELECT DISTINCT type_id FROM vital_sign WHERE patient_id=" . $pid;
			} else {
				$sql = "SELECT DISTINCT type_id FROM vital_sign vs LEFT JOIN vital v ON v.id=vs.type_id WHERE patient_id=" . $pid . " AND v.name IN ('" . implode("', '", $types) . "') ORDER BY FIELD(v.name,'" . implode("', '", $types) . "')";
			}
			if (!is_null($in_patient_id))
				$sql .= " AND in_patient_id=" . $in_patient_id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$sql = "SELECT *, UNIX_TIMESTAMP(read_date)*1000 AS dDate FROM vital_sign WHERE patient_id='" . $pid . "'  AND type_id=" . $row['type_id'] . " ORDER BY read_date DESC LIMIT 1";
				$stmt1 = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt1->execute();
				while ($row1 = $stmt1->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
					$signs[] = $this->get($row1['id'], $getFull, $pdo);
				}
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$signs = [];
		}
		return $signs;
	}
	
	function getPatientDemographLastVitalSigns($pid, $in_patient_id = null, $getFull = false, $types = [], $pdo = null)
	{
		$signs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$filter = "";
			if (!is_null($in_patient_id)) {
				$filter .= " AND in_patient_id=" . $in_patient_id;
			}
			if (count($types) == 0) {
				$sql = "SELECT DISTINCT type_id FROM vital_sign vs WHERE patient_id=" . $pid . $filter;
			} else {
				$sql = "SELECT DISTINCT type_id FROM vital_sign vs LEFT JOIN vital v ON v.id=vs.type_id WHERE patient_id=" . $pid . " $filter AND v.name IN ('" . implode("', '", $types) . "') ORDER BY FIELD(v.name,'" . implode("', '", $types) . "')";
			}
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$sql = "SELECT vs.*, UNIX_TIMESTAMP(read_date)*1000 AS dDate FROM vital_sign vs WHERE patient_id=$pid  AND type_id={$row['type_id']} AND DATE(read_date) = DATE(NOW()) ORDER BY read_date DESC LIMIT 1";
				//error_log($sql);
				$stmt1 = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt1->execute();
				while ($row1 = $stmt1->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
					$signs[] = $this->get($row1['id'], false, $pdo);
				}
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$signs = [];
		}
		return $signs;
	}
	
	function getPatientLastVitalSign($pid, $type, $in_patient_id = null, $getFull = false, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT vs.*, UNIX_TIMESTAMP(read_date)*1000 AS dDate FROM vital_sign vs LEFT JOIN vital v ON v.id=vs.type_id WHERE patient_id='" . $pid . "' AND v.name='" . $type . "' AND " . ($in_patient_id !== null ? " in_patient_id='" . $in_patient_id . "'" : " in_patient_id IS NULL ") . " ORDER BY read_date DESC LIMIT 1";
			//            $sql = "SELECT * FROM vital_sign WHERE patient_id='" . $pid . "' ORDER BY  read_date DESC LIMIT 1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$sign = $this->get($row['id'], FALSE, $pdo);
			} else {
				$sign = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$sign = null;
		}
		return $sign;
	}
	
	/**
	 *
	 * @param number $pid
	 * @param number $in_patient_id
	 * @param mixed  $type must be an array of types. or null to select all types
	 * @param bool   $getFull
	 * @param object $pdo
	 *
	 * @return array
	 */
	function getPatientVitalSigns($pid, $in_patient_id = null, $type = null, $getFull = false, $pdo = null)
	{
		$signs = array();
		
		$type = ($type === null) ? getTypeOptions('type', 'vital_sign', $pdo) : $type;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT vs.*, UNIX_TIMESTAMP(read_date)*1000 AS dDate FROM vital_sign vs LEFT JOIN vital v ON v.id=vs.type_id WHERE patient_id='$pid'" . ($in_patient_id === null ? "" : " AND in_patient_id=$in_patient_id");
			$sql .= ($type !== null && is_array($type) ? "  AND v.name IN ('" . implode("', '", $type) . "')" : "") . " ORDER BY v.name, read_date";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$signs[] = $this->get($row['id'], FALSE, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$signs = array();
		}
		return $signs;
	}
	
	function getVitalSigns($in_patient_id = null, $getFull = false, $pdo = null)
	{
		$signs = array();
		try {
			$ipid = $in_patient_id === null ? "" : " WHERE in_patient_id=" . $in_patient_id;
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT *, UNIX_TIMESTAMP(read_date)*1000 AS dDate FROM vital_sign";
			$sql .= $ipid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$signs[] = $this->get($row['id'], FALSE, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$signs = array();
		}
		return $signs;
	}
	
	function getEncounterVitalSigns($encounter_id = null, $getFull = false, $pdo = null)
	{
		$signs = array();
		try {
			$encounter = $encounter_id === null ? "" : " WHERE encounter_id=" . $encounter_id;
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT *, UNIX_TIMESTAMP(read_date)*1000 AS dDate FROM vital_sign";
			$sql .= $encounter;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$signs[] = $this->get($row['id'], FALSE, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$signs = array();
		}
		return $signs;
	}
	
}
