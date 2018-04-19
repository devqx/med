<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ClinicalTaskDataDAO
 *
 * @author pauldic
 */
class ClinicalTaskDataDAO
{

	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Drug.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTask.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/VitalSign.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTaskData.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTaskChart.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskChartDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function addClinicalTaskData($ctds, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			foreach ($ctds as $ctd) {
				//$ctd = new ClinicalTaskData();
				if ($ctd->getStartTime() == 0) {
					$ctd->setStartTime(date("Y-m-d H:i:s"));
				}
				$ctd->setClinicalTask($ctds[0]->getClinicalTask());
				$ctdid = $this->hasClinicalTaskDatum($ctd, $pdo);
				if (!$ctdid === FALSE && !($ctd->getDrug()==null && $ctd->getGeneric()==null && $ctd->getType()==null)) {//!in_array($ctd->getType(), ["Others"])
					$ctd->setId($ctdid);
					if (!$this->updateTaskData($ctd, $pdo)) {
						return [];
					}
					continue;
				}

				$private = $ctd->getPrivate() ? var_export($ctd->getPrivate(), true) : var_export(FALSE, true);
				$type = $ctd->getType() ? $ctd->getType()->getId() : 'NULL';
				$sql = "INSERT INTO clinical_task_data (clinical_task_id, drug_id, drug_generic_id, dose, frequency, task_count, type_id, created_by, description, entry_time, private) VALUES (" .
					$ctds[0]->getClinicalTask()->getId() . ", " . ($ctd->getDrug() === null ? "NULL" : $ctd->getDrug()->getId()) . ", " . ($ctd->getGeneric() === null ? "NULL" : $ctd->getGeneric()->getId()) . ", '" . $ctd->getDose() . "', '" . $ctd->getFrequency() . "', " . $ctd->getTaskCount() . ", $type, '" . $ctd->getCreatedBy()->getId() . "', " . ($ctd->getDescription() !== null && $ctd->getDescription() !== "" ? "'" . escape($ctd->getDescription()) . "'" : "NULL") . ", '" . $ctd->getStartTime() . "', $private)";
				$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt->execute();
			}

			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$ctds = [];
		}
		return $ctds;
	}

	private function hasClinicalTaskDatum($ctd, $pdo = null)
	{
		$ctdid = FALSE;
		$query = ($ctd->getDrug() === null && $ctd->getGeneric() === null) ? ($ctd->getType() ? " type_id=" . $ctd->getType()->getId(): " type_id IS NULL") : (($ctd->getDrug() === null) ? " drug_generic_id=" . $ctd->getGeneric()->getId() : " drug_id=" . $ctd->getDrug()->getId());
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT id FROM clinical_task_data WHERE clinical_task_id=" . $ctd->getClinicalTask()->getId() . " AND `status` = 'Active' AND " . $query;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ctdid = $row['id'];
			} else {
				$ctdid = FALSE;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$stmt = null;
			$ctdid = FALSE;
		}
		return $ctdid;
	}

	function getClinicalTaskDatum($ctid, $status = ['Active'], $getFull = FALSE, $pdo = null)
	{
		$ctd = new ClinicalTaskData();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT d.*, c.patient_id, c.in_patient_id  FROM clinical_task_data d LEFT JOIN clinical_task c ON c.id =d.clinical_task_id WHERE d.status IN ('" . implode("', '", $status) . "') AND d.id=$ctid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				if ($getFull) {
					$ct = (new ClinicalTaskDAO())->getClinicalTask($row['clinical_task_id'], FALSE, $pdo);
					$reading = (new VitalSignDAO())->getPatientVitalSigns($row['patient_id'], $row['in_patient_id'], null, FALSE, $pdo);
					$drug = ($row['drug_id'] === null ? null : (new DrugDAO())->getDrug($row['drug_id'], TRUE, $pdo));
					$generic = ($row['drug_generic_id'] === null ? null : (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], TRUE, $pdo));
					$cancelledBy = ($row['cancelled_by'] === null ? null : (new StaffDirectoryDAO())->getStaff($row['cancelled_by'], FALSE, $pdo));
					$createdBy = ($row['created_by'] === null ? null : (new StaffDirectoryDAO())->getStaff($row['created_by'], FALSE, $pdo));
				} else {
					$ct = new ClinicalTask($row['clinical_task_id']);
					$drug = $row['drug_id'] === null ? null : new Drug($row["drug_id"]);
					$generic = $row['drug_generic_id'] === null ? null :new DrugGeneric($row["drug_generic_id"]);
					$reading = [];
					$cancelledBy = new StaffDirectory($row['cancelled_by']);
					$createdBy = new StaffDirectory($row['created_by']);
				}
				$ctd->setId($row['id']);
				$ctd->setClinicalTask($ct);
				$ctd->setDrug($drug);
				$ctd->setGeneric($generic);
				$ctd->setDose($row['dose']);
				$ctd->setFrequency($row['frequency']);
				$ctd->setEntryTime($row['entry_time']);
				$ctd->setLastRoundTime($row['last_round_time']);
				$ctd->setEndRoundTime($row['end_round_time']);
				$ctd->setRoundCount($row['round_count']);
				$ctd->setTaskCount($row['task_count']);
				$ctd->setStatus($row['status']);
				$ctd->setBilled($row['billed']);
				$ctd->setType((new VitalDAO())->get($row['type_id'], $pdo));
				$ctd->setCancelReason($row['cancel_reason']);
				$ctd->setCancelledBy($cancelledBy);
				$ctd->setCancelTime($row['cancel_time']);
				$ctd->setCreatedBy($createdBy);

				$ctd->setLastReading($row['type_id'] != NULL ? (new VitalSignDAO())->getPatientLastVitalSign($row['patient_id'], (new VitalDAO())->get($row['type_id'], $pdo)->getName(), $row['in_patient_id'], FALSE, $pdo) : NULL);
				$ctd->setReadings($reading);
				
				if ($row['last_round_time'] === null || $row['last_round_time'] === "") {
					$ctd->setNextRoundTime((new DateTime($row['entry_time']))->format('Y-m-d H:i:s'));
				} else {
					$ctd->setNextRoundTime((new DateTime($row['next_round_time']))->format('Y-m-d H:i:s'));
				}
			} else {
				$ctd = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$stmt = null;
			$ctd = null;
		}
		return $ctd;
	}

	function getPatientClinicalTaskData($pid, $status = ['Active'], $getFull = FALSE, $pdo = null)
	{
		$ctds = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT v.name AS type_name, d.*, c.patient_id, c.in_patient_id FROM clinical_task_data d LEFT JOIN vital v ON v.id=d.type_id LEFT JOIN clinical_task c ON c.id =d.clinical_task_id WHERE d.status IN ('" . implode("', '", $status) . "') AND c.patient_id=" . $pid . " ORDER BY d.status, d.next_round_time";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ctd = new ClinicalTaskData($row['id']);
				if ($getFull) {
					$ct = (new ClinicalTaskDAO())->getClinicalTask($row['clinical_task_id'], FALSE, $pdo);
					$reading = (new VitalSignDAO())->getPatientVitalSigns($row['patient_id'], $row['in_patient_id'], null, FALSE, $pdo);
					$drug = ($row['drug_id'] === null ? null : (new DrugDAO())->getDrug($row['drug_id'], TRUE, $pdo));
					$generic = ($row['drug_generic_id'] === null ? null : (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], TRUE, $pdo));
					$cancelledBy = ($row['cancelled_by'] === null ? null : (new StaffDirectoryDAO())->getStaff($row['cancelled_by'], FALSE, $pdo));
				} else {
					$ct = new ClinicalTask($row['clinical_task_id']);
					$drug = $row['drug_id'] === null ? null : new Drug($row["drug_id"]);
					$generic = $row['drug_generic_id'] === null ? null : new DrugGeneric($row["drug_generic_id"]);
					$reading = [];
					$cancelledBy = new StaffDirectory($row['cancelled_by']);
				}
				$ctd->setClinicalTask($ct);
				$ctd->setDrug($drug);
				$ctd->setGeneric($generic);
				$ctd->setDose($row['dose']);
				$ctd->setFrequency($row['frequency']);
				$ctd->setEntryTime($row['entry_time']);
				$ctd->setLastRoundTime($row['last_round_time']);
				$ctd->setEndRoundTime($row['end_round_time']);
				$ctd->setRoundCount($row['round_count']);
				$ctd->setTaskCount($row['task_count']);
				$ctd->setStatus($row['status']);
				$ctd->setBilled($row['billed']);
				$ctd->setType( (new VitalDAO())->get($row['type_id'], $pdo));
				$ctd->setCancelReason($row['cancel_reason']);
				$ctd->setCancelledBy($cancelledBy);
				$ctd->setCancelTime($row['cancel_time']);
				$ctd->setCreatedBy($row['created_by']);
				if ($row['last_round_time'] === null || $row['last_round_time'] === "") {
					$ctd->setNextRoundTime((new DateTime($row['entry_time']))->format('Y-m-d H:i:s'));
				} else {
					$ctd->setNextRoundTime((new DateTime($row['next_round_time']))->format('Y-m-d H:i:s'));
				}
				$ctd->setLastReading((new VitalSignDAO())->getPatientLastVitalSign($row['patient_id'], $row['type_name'], $row['in_patient_id'], FALSE, $pdo));
				$ctd->setReadings($reading);
				$ctds[] = $ctd;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ctds = [];
		}
		return $ctds;
	}

	function getTaskDatum($ctDatumId, $getFull = FALSE, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT d.*, c.patient_id, c.in_patient_id, v.name AS type_name  FROM clinical_task_data d LEFT JOIN vital v ON v.id=d.type_id LEFT JOIN clinical_task c ON c.id =d.clinical_task_id WHERE d.id=$ctDatumId";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ctd = new ClinicalTaskData($row['id']);
				if ($getFull) {
					$ct = (new ClinicalTaskDAO())->getClinicalTask($row['clinical_task_id'], FALSE, $pdo);
					$reading = ($row['type_id'] == null && $row['drug_generic_id'] == null && $row['drug_id'] == null) ? //not medication
						(new VitalSignDAO())->getPatientVitalSigns($row['patient_id'], $row['in_patient_id'], null, FALSE, $pdo) :
						(new PrescriptionDataDAO())->getFullfilledPrescriptionData($row['in_patient_id'], $row['drug_id'], $getFull, $pdo);
					$cancelledBy = ($row['cancelled_by'] === null ? null : (new StaffDirectoryDAO())->getStaff($row['cancelled_by'], FALSE, $pdo));
					$createdBy = (new StaffDirectoryDAO())->getStaffMin($row['created_by'], $pdo);
				} else {
					$ct = new ClinicalTask($row['clinical_task_id']);
					$reading = [];
					$cancelledBy = new StaffDirectory($row['cancelled_by']);
					$createdBy = new StaffDirectory($row['created_by']);
				}
				$ctd->setClinicalTask($ct);
				$ctd->setDrug(($row['drug_id'] === null ? null : (new DrugDAO())->getDrug($row['drug_id'], TRUE, $pdo)));
				$ctd->setGeneric(($row['drug_generic_id'] === null ? null : (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], TRUE, $pdo)));
				$ctd->setDose($row['dose']);
				$ctd->setFrequency($row['frequency']);
				$ctd->setEntryTime($row['entry_time']);
				$ctd->setDescription($row['description'] !== "" ? $row['description'] : null);
				$ctd->setLastRoundTime($row['last_round_time']);
				$ctd->setEndRoundTime($row['end_round_time']);
				$ctd->setRoundCount($row['round_count']);
				$ctd->setTaskCount($row['task_count']);
				$ctd->setStatus($row['status']);
				$ctd->setBilled($row['billed']);
				$ctd->setType((new VitalDAO())->get($row['type_id'], $pdo));
				$ctd->setCancelReason($row['cancel_reason']);
				$ctd->setCancelledBy($cancelledBy);
				$ctd->setCancelTime($row['cancel_time']);
				$ctd->setCreatedBy($createdBy);
				$ctd->setLastReading(($row['type_id'] !== null ) ? (new VitalSignDAO())->getPatientLastVitalSign($row['patient_id'], $row['type_name'], $row['in_patient_id'], FALSE, $pdo) : null);
				$ctd->setReadings($reading);
				
				if ($row['last_round_time'] === null || $row['last_round_time'] === "") {
					$ctd->setNextRoundTime((new DateTime($row['entry_time']))->format('Y-m-d H:i:s'));
				} else {
					$ctd->setNextRoundTime((new DateTime($row['next_round_time']))->format('Y-m-d H:i:s'));
				}

				return $ctd;
			} else {
				return null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			$stmt = null;
			return null;
		}
	}

	function getClinicalTaskData($ctid, $getFull = FALSE, $pdo = null)
	{
		$ctds = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT v.name AS type_name, d.*, c.patient_id, c.in_patient_id FROM clinical_task_data d LEFT JOIN clinical_task c ON c.id =d.clinical_task_id LEFT JOIN vital v ON v.id=d.type_id WHERE d.clinical_task_id=$ctid AND d.private is false ORDER BY d.last_round_time";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ctd = new ClinicalTaskData($row['id']);
				if ($getFull) {
					$ct = (new ClinicalTaskDAO())->getClinicalTask($row['clinical_task_id'], FALSE, $pdo);
					$reading = ($row['type_id'] !== NULL) ?
						(new VitalSignDAO())->getPatientVitalSigns($row['patient_id'], $row['in_patient_id'], null, FALSE, $pdo) :
						(new PrescriptionDataDAO())->getFullfilledPrescriptionData($row['in_patient_id'], $row['drug_id'], $getFull, $pdo);
					$cancelledBy = ($row['cancelled_by'] === null ? null : (new StaffDirectoryDAO())->getStaff($row['cancelled_by'], FALSE, $pdo));
					$createdBy = (new StaffDirectoryDAO())->getStaffMin($row['created_by'], $pdo);
				} else {
					$ct = new ClinicalTask($row['clinical_task_id']);
					$reading = [];
					$cancelledBy = new StaffDirectory($row['cancelled_by']);
					$createdBy = (new StaffDirectoryDAO())->getStaffMin($row['created_by'], $pdo);;
					//$createdBy = new StaffDirectory($row['created_by']);
				}
				$ctd->setClinicalTask($ct);
				$ctd->setDrug(($row['drug_id'] === null ? null : (new DrugDAO())->getDrug($row['drug_id'], TRUE, $pdo)));
				$ctd->setGeneric(($row['drug_generic_id'] === null ? null : (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], TRUE, $pdo)));
				$ctd->setDose($row['dose']);
				$ctd->setFrequency($row['frequency']);
				$ctd->setEntryTime($row['entry_time']);
				$ctd->setDescription($row['description'] !== "" ? $row['description'] : null);
				$ctd->setLastRoundTime($row['last_round_time']);
				$ctd->setEndRoundTime($row['end_round_time']);
				$ctd->setRoundCount($row['round_count']);
				$ctd->setTaskCount($row['task_count']);
				$ctd->setStatus($row['status']);
				$ctd->setBilled($row['billed']);
				$ctd->setType((new VitalDAO())->get($row['type_id'], $pdo));
				$ctd->setCancelReason($row['cancel_reason']);
				$ctd->setCancelledBy($cancelledBy);
				$ctd->setCancelTime($row['cancel_time']);
				$ctd->setCreatedBy($createdBy);
				$ctd->setLastReading(($row['type_id'] !== null) ? (new VitalSignDAO())->getPatientLastVitalSign($row['patient_id'], $row['type_name'], $row['in_patient_id'], FALSE, $pdo) : null);
				$ctd->setReadings($reading);
				
				if ($row['last_round_time'] === null || $row['last_round_time'] === "") {
					$ctd->setNextRoundTime((new DateTime($row['entry_time']))->format('Y-m-d H:i:s'));
				} else {
					$ctd->setNextRoundTime((new DateTime($row['next_round_time']))->format('Y-m-d H:i:s'));
				}
				$ctds[] = $ctd;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ctds = [];
		}
		return $ctds;
	}

	function getIVFClinicalTaskData($ctid, $getFull = FALSE, $pdo = null)
	{
		$ctds = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT v.name AS type_name, d.*, c.patient_id, c.in_patient_id FROM clinical_task_data d LEFT JOIN clinical_task c ON c.id =d.clinical_task_id LEFT JOIN vital v ON v.id=d.type_id WHERE d.clinical_task_id=$ctid ORDER BY d.last_round_time";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ctd = new ClinicalTaskData($row['id']);
				if ($getFull) {
					$ct = (new ClinicalTaskDAO())->getClinicalTask($row['clinical_task_id'], FALSE, $pdo);
					$reading = ($row['type_id'] !== NULL) ?
						(new VitalSignDAO())->getPatientVitalSigns($row['patient_id'], $row['in_patient_id'], null, FALSE, $pdo) :
						(new PrescriptionDataDAO())->getFullfilledPrescriptionData($row['in_patient_id'], $row['drug_id'], $getFull, $pdo);
					$cancelledBy = ($row['cancelled_by'] === null ? null : (new StaffDirectoryDAO())->getStaff($row['cancelled_by'], FALSE, $pdo));
					$createdBy = (new StaffDirectoryDAO())->getStaffMin($row['created_by'], $pdo);
				} else {
					$ct = new ClinicalTask($row['clinical_task_id']);
					$reading = [];
					$cancelledBy = new StaffDirectory($row['cancelled_by']);
					$createdBy = (new StaffDirectoryDAO())->getStaffMin($row['created_by'], $pdo);;
					//$createdBy = new StaffDirectory($row['created_by']);
				}
				$ctd->setClinicalTask($ct);
				$ctd->setDrug(($row['drug_id'] === null ? null : (new DrugDAO())->getDrug($row['drug_id'], TRUE, $pdo)));
				$ctd->setGeneric(($row['drug_generic_id'] === null ? null : (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], TRUE, $pdo)));
				$ctd->setDose($row['dose']);
				$ctd->setFrequency($row['frequency']);
				$ctd->setEntryTime($row['entry_time']);
				$ctd->setDescription($row['description'] !== "" ? $row['description'] : null);
				$ctd->setLastRoundTime($row['last_round_time']);
				$ctd->setEndRoundTime($row['end_round_time']);
				$ctd->setRoundCount($row['round_count']);
				$ctd->setTaskCount($row['task_count']);
				$ctd->setStatus($row['status']);
				$ctd->setBilled($row['billed']);
				$ctd->setType((new VitalDAO())->get($row['type_id'], $pdo));
				$ctd->setCancelReason($row['cancel_reason']);
				$ctd->setCancelledBy($cancelledBy);
				$ctd->setCancelTime($row['cancel_time']);
				$ctd->setCreatedBy($createdBy);
				$ctd->setLastReading(($row['type_id'] !== NULL) ? (new VitalSignDAO())->getPatientLastVitalSign($row['patient_id'], $row['type_name'], $row['in_patient_id'], FALSE, $pdo) : null);
				$ctd->setReadings($reading);
				
				if ($row['last_round_time'] === null || $row['last_round_time'] === "") {
					$ctd->setNextRoundTime((new DateTime($row['entry_time']))->format('Y-m-d H:i:s'));
				} else {
					$ctd->setNextRoundTime((new DateTime($row['next_round_time']))->format('Y-m-d H:i:s'));
				}
				$ctds[] = $ctd;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ctds = [];
		}
		return $ctds;
	}

	function getAllClinicalTaskDatum($page, $pageSize, $patientType, $status = ['Active'], $getFull = FALSE, $ward = null, $patient = null, $pdo = null)
	{
		$filter = ($patientType == 'op') ? " AND c.in_patient_id IS NULL" : " AND c.in_patient_id IS NOT NULL";
		$sql = "SELECT v.name AS type_name, d.*, c.patient_id, c.in_patient_id, w.id AS ward_id FROM clinical_task_data d LEFT JOIN clinical_task c ON c.id =d.clinical_task_id LEFT JOIN vital v ON v.id=d.type_id LEFT JOIN in_patient i ON c.in_patient_id=i.id LEFT JOIN bed b ON i.bed_id=b.id LEFT JOIN room r ON r.id=b.room_id LEFT JOIN ward w ON w.id=r.ward_id  WHERE d.status IN ('" . implode("', '", $status) . "')$filter";
		if ($ward !== null) {
			$sql .= " AND w.id = " . $ward;
		}
		if ($patient !== null) {
			$sql .= " AND c.patient_id = " . $patient;
		}
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
		$ctds = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;

			$sql .= " ORDER BY d.last_round_time LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ctd = new ClinicalTaskData($row['id']);
				if ($getFull) {
					$ct = (new ClinicalTaskDAO())->getClinicalTask($row['clinical_task_id'], TRUE, $pdo);
					$reading = (new VitalSignDAO())->getPatientVitalSigns($row['patient_id'], $row['in_patient_id'], null, FALSE, $pdo);
					$cancelledBy = ($row['cancelled_by'] === null ? null : (new StaffDirectoryDAO())->getStaff($row['cancelled_by'], FALSE, $pdo));
				} else {
					$ct = new ClinicalTask($row['clinical_task_id']);
					$reading = [];
					$cancelledBy = new StaffDirectory($row['cancelled_by']);
				}
				$ctd->setClinicalTask($ct);
				$ctd->setDrug(($row['drug_id'] === null ? null : (new DrugDAO())->getDrug($row['drug_id'], FALSE, $pdo)));
				$ctd->setGeneric(($row['drug_generic_id'] === null ? null : (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], FALSE, $pdo)));
				$ctd->setDose($row['dose']);
				$ctd->setFrequency($row['frequency']);
				$ctd->setEntryTime($row['entry_time']);
				$ctd->setLastRoundTime($row['last_round_time']);
				$ctd->setEndRoundTime($row['end_round_time']);
				$ctd->setRoundCount($row['round_count']);
				$ctd->setTaskCount($row['task_count']);
				$ctd->setStatus($row['status']);
				$ctd->setBilled($row['billed']);
				$ctd->setType((new VitalDAO())->get($row['type_id'], $pdo));
				$ctd->setDescription($row['description'] !== "" ? $row['description'] : null);
				$ctd->setCancelReason($row['cancel_reason']);
				$ctd->setCancelledBy($cancelledBy);
				$ctd->setCancelTime($row['cancel_time']);
				$ctd->setCreatedBy($row['created_by']);
				$ctd->setLastReading(($row['type_id'] !== NULL) ? (new VitalSignDAO())->getPatientLastVitalSign($row['patient_id'], $row['type_name'], $row['in_patient_id'], FALSE, $pdo) : null);
				$ctd->setReadings($reading);
				if ($row['last_round_time'] === null || $row['last_round_time'] === "") {
					$ctd->setNextRoundTime((new DateTime($row['entry_time']))->format('Y-m-d H:i:s'));
				} else {
					$ctd->setNextRoundTime((new DateTime($row['next_round_time']))->format('Y-m-d H:i:s'));
				}

				$ctds[] = $ctd;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ctds = [];
		}
		$results = (object)null;
		$results->data = $ctds;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}
	
	function getPatientIPClinicalTaskData($patient = null, $inPatientId=null, $status = ['Active'], $getFull = FALSE, $page, $pageSize, $task_type=null, $pdo = null)
	{
		$filter = ($inPatientId == null) ? " AND c.in_patient_id IS NULL" : " AND c.in_patient_id = $inPatientId";
		$sql = "SELECT v.name AS type_name, d.*, c.patient_id, c.in_patient_id, w.id AS ward_id FROM clinical_task_data d LEFT JOIN vital v ON v.id=d.type_id LEFT JOIN clinical_task c ON c.id =d.clinical_task_id LEFT JOIN in_patient i ON c.in_patient_id=i.id LEFT JOIN bed b ON i.bed_id=b.id LEFT JOIN room r ON r.id=b.room_id LEFT JOIN ward w ON w.id=r.ward_id  WHERE d.status IN ('" . implode("', '", $status) . "')$filter";
		
		if ($patient !== null) {
			$sql .= " AND c.patient_id = $patient";
		}
		
		if ($task_type !== null) {
			$sql .= " AND v.name = ".quote_esc_str($task_type);
		}
		
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
		$ctds = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;

			$sql .= " ORDER BY d.last_round_time LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ctd = new ClinicalTaskData($row['id']);
				if ($getFull) {
					$ct = (new ClinicalTaskDAO())->getClinicalTask($row['clinical_task_id'], TRUE, $pdo);
					$reading = (new VitalSignDAO())->getPatientVitalSigns($row['patient_id'], $row['in_patient_id'], null, FALSE, $pdo);
					$cancelledBy = ($row['cancelled_by'] === null ? null : (new StaffDirectoryDAO())->getStaff($row['cancelled_by'], FALSE, $pdo));
				} else {
					$ct = new ClinicalTask($row['clinical_task_id']);
					$reading = [];
					$cancelledBy = new StaffDirectory($row['cancelled_by']);
				}
				$ctd->setClinicalTask($ct);
				$ctd->setDrug(($row['drug_id'] === null ? null : (new DrugDAO())->getDrug($row['drug_id'], FALSE, $pdo)));
				$ctd->setGeneric(($row['drug_generic_id'] === null ? null : (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], FALSE, $pdo)));
				$ctd->setDose($row['dose']);
				$ctd->setFrequency($row['frequency']);
				$ctd->setEntryTime($row['entry_time']);
				$ctd->setLastRoundTime($row['last_round_time']);
				$ctd->setEndRoundTime($row['end_round_time']);
				$ctd->setRoundCount($row['round_count']);
				$ctd->setTaskCount($row['task_count']);
				$ctd->setStatus($row['status']);
				$ctd->setBilled($row['billed']);
				$ctd->setType((new VitalDAO())->get($row['type_id'], $pdo));
				$ctd->setDescription($row['description'] !== "" ? $row['description'] : null);
				$ctd->setCancelReason($row['cancel_reason']);
				$ctd->setCancelledBy($cancelledBy);
				$ctd->setCancelTime($row['cancel_time']);
				$ctd->setCreatedBy( (new StaffDirectoryDAO())->getStaff($row['created_by'], FALSE, $pdo));
				$ctd->setLastReading(($row['type_id'] !== NULL) ? (new VitalSignDAO())->getPatientLastVitalSign($row['patient_id'], $row['type_name'], $row['in_patient_id'], FALSE, $pdo) : null);
				$ctd->setReadings($reading);
				if ($row['last_round_time'] === null || $row['last_round_time'] === "") {
					$ctd->setNextRoundTime((new DateTime($row['entry_time']))->format('Y-m-d H:i:s'));
				} else {
					$ctd->setNextRoundTime((new DateTime($row['next_round_time']))->format('Y-m-d H:i:s'));
				}
				
				//$ctd->setClinicalTask( (new ClinicalTaskDAO())->getClinicalTask($row['clinical_task_id'], false, $pdo) );

				$ctds[] = $ctd;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ctds = [];
		}
		$results = (object)null;
		$results->data = $ctds;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	
	function getPatientIPClinicalTaskDataSlim($patient = null, $inPatientId=null, $status = ['Active'], $getFull = FALSE, $page, $pageSize, $task_type=null, $pdo = null)
	{
		$filter = ($inPatientId == null) ? " AND c.in_patient_id IS NULL AND d.private IS FALSE" : " AND c.in_patient_id = $inPatientId";
		$sql = "SELECT v.name AS type_name, d.*, drugs.name AS drug_name, dg.form AS generic_form, dg.weight AS generic_weight, dg.name AS generic_name, dg.id AS generic_id, c.patient_id, c.in_patient_id, w.id AS ward_id, CONCAT_WS(' ', sd.lastname, sd.firstname) AS created_by_name FROM clinical_task_data d LEFT JOIN clinical_task c ON c.id =d.clinical_task_id LEFT JOIN drugs ON d.drug_id=drugs.id LEFT JOIN drug_generics dg ON dg.id=d.drug_generic_id LEFT JOIN staff_directory sd ON sd.staffId=d.created_by LEFT JOIN in_patient i ON c.in_patient_id=i.id LEFT JOIN bed b ON i.bed_id=b.id LEFT JOIN room r ON r.id=b.room_id LEFT JOIN ward w ON w.id=r.ward_id LEFT JOIN vital v ON v.id=d.type_id WHERE d.status IN ('" . implode("', '", $status) . "')$filter";
		
		if ($patient !== null) {
			$sql .= " AND c.patient_id = $patient";
		}
		
		if ($task_type !== null && !in_array($task_type, ['Medication', 'Others'])) {
			$sql .= " AND v.name = ".quote_esc_str($task_type);
		} else	if ($task_type !== null && $task_type=='Medication') {
			$sql .= " AND (d.drug_id IS NOT NULL or d.drug_generic_id IS NOT NULL)";
		} else	if ($task_type !== null && $task_type=='Others') {
			$sql .= " AND d.description IS NOT NULL";
		}
		
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
		$ctds = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;

			$sql .= " ORDER BY d.next_round_time LIMIT $offset, $pageSize";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$row['last_reading'] = (($row['type_id'] !== NULL) ? (new VitalSignDAO())->getPatientLastVitalSign($row['patient_id'], $row['type_name'], $row['in_patient_id'], FALSE, $pdo) : null);
				if ($row['last_round_time'] === null || $row['last_round_time'] === "") {
					$row['next_round_time'] = (new DateTime($row['entry_time']))->format('Y-m-d H:i:s');
					//$ctd->setNextRoundTime((new DateTime($row['entry_time']))->format('Y-m-d H:i:s'));
				} else {
					$row['next_round_time'] = (new DateTime($row['next_round_time']))->format('Y-m-d H:i:s');
				}
				
				$ctds[] = (object)$row;
				//$ctds[] = $ctd;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ctds = [];
		}
		$results = (object)null;
		$results->data = $ctds;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}
	
	function getPatientIvfClinicalTaskDataSlim($patient = null, $instanceId=null, $status = ['Active'], $getFull = FALSE, $page, $pageSize, $task_type=null, $pdo = null)
	{
		$filter = ($instanceId !== null) ? " AND c.source_instance_id = $instanceId AND c.source='ivf'" : " AND c.source_instance_id IS NULL";
		$sql = "SELECT v.name AS type_name, d.*, drugs.name AS drug_name, dg.form AS generic_form, dg.weight AS generic_weight, dg.name AS generic_name, dg.id AS generic_id, c.patient_id, c.in_patient_id, w.id AS ward_id, CONCAT_WS(' ', sd.lastname, sd.firstname) AS created_by_name FROM clinical_task_data d LEFT JOIN clinical_task c ON c.id =d.clinical_task_id LEFT JOIN drugs ON d.drug_id=drugs.id LEFT JOIN drug_generics dg ON dg.id=d.drug_generic_id LEFT JOIN staff_directory sd ON sd.staffId=d.created_by LEFT JOIN in_patient i ON c.in_patient_id=i.id LEFT JOIN bed b ON i.bed_id=b.id LEFT JOIN room r ON r.id=b.room_id LEFT JOIN ward w ON w.id=r.ward_id LEFT JOIN vital v ON v.id=d.type_id WHERE d.status IN ('" . implode("', '", $status) . "')$filter";
		
		if ($patient !== null) {
			$sql .= " AND c.patient_id = $patient";
		}
		
		if ($task_type !== null && !in_array($task_type, ['Medication', 'Others'])) {
			$sql .= " AND v.name = ".quote_esc_str($task_type);
		} else	if ($task_type !== null && $task_type=='Medication') {
			$sql .= " AND (d.drug_id IS NOT NULL or d.drug_generic_id IS NOT NULL)";
		} else	if ($task_type !== null && $task_type=='Others') {
			$sql .= " AND d.description IS NOT NULL";
		}
		
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
		$ctds = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;

			$sql .= " ORDER BY d.next_round_time LIMIT $offset, $pageSize";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$row['last_reading'] = (($row['type_id'] !== NULL) ? (new VitalSignDAO())->getPatientLastVitalSign($row['patient_id'], $row['type_name'], $row['in_patient_id'], FALSE, $pdo) : null);
				if ($row['last_round_time'] === null || $row['last_round_time'] === "") {
					$row['next_round_time'] = (new DateTime($row['entry_time']))->format('Y-m-d H:i:s');
					//$ctd->setNextRoundTime((new DateTime($row['entry_time']))->format('Y-m-d H:i:s'));
				} else {
					$row['next_round_time'] = (new DateTime($row['next_round_time']))->format('Y-m-d H:i:s');
				}
				
				$ctds[] = (object)$row;
				//$ctds[] = $ctd;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ctds = [];
		}
		$results = (object)null;
		$results->data = $ctds;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function getAllClinicalTaskDatumSlim($page, $pageSize, $patientType, $status = ['Active'], $getFull = FALSE, $ward = null, $patient = null, $taskType=null, $pdo = null)
	{
		$filter = ($patientType == 'op') ? " AND c.in_patient_id IS NULL" : " AND c.in_patient_id IS NOT NULL";
		$sql = "SELECT v.name AS type_name, d.*, c.patient_id, c.in_patient_id, w.id AS ward_id, i.bed_id, dg.weight AS drugGenericWeight, dg.form AS drugGenericForm, dg.name AS genericName, d2.name AS drugName, CONCAT_WS(' ', pd.lname, pd.mname, pd.fname) AS patientName, b.name AS bedName, w.name AS wardName FROM clinical_task_data d LEFT JOIN vital v ON v.id=d.type_id LEFT JOIN drug_generics dg ON dg.id=d.drug_generic_id LEFT JOIN drugs d2 ON d2.id=d.drug_id LEFT JOIN clinical_task c ON c.id =d.clinical_task_id LEFT JOIN in_patient i ON c.in_patient_id=i.id LEFT JOIN patient_demograph pd ON pd.patient_ID=c.patient_id LEFT JOIN bed b ON i.bed_id=b.id LEFT JOIN room r ON r.id=b.room_id LEFT JOIN ward w ON w.id=r.ward_id WHERE d.private is false AND d.status IN ('" . implode("', '", $status) . "')$filter";
		if ($ward !== null) {
			$sql .= " AND w.id = " . $ward;
		}
		if ($patient !== null) {
			$sql .= " AND c.patient_id = " . $patient;
		}

		if($taskType !== null && !in_array($taskType, ['Medication', 'Others'])) {
			$sql .= " AND v.name = ".quote_esc_str($taskType);
		} else if ($taskType !== null && $taskType == 'Medication'){
			$sql .= " AND (d.drug_id IS NOT NULL OR d.drug_generic_id IS NOT NULL) ";
		} else if ($taskType !== null && $taskType == 'Others'){
			$sql .= " AND d.description IS NOT NULL";
		}
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			errorLog($e);
			error_log("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$ctds = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//$sql .= " ORDER BY d.last_round_time";
			
			$sql .= " ORDER BY d.next_round_time LIMIT $offset, $pageSize";
			//error_log($sql);
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$row['lastReading'] = ($row['type_id'] !== NULL) ? (new VitalSignDAO())->getPatientLastVitalSign($row['patient_id'], $row['type_name'], $row['in_patient_id'], FALSE, $pdo) : null;
				$row['nextRoundTime'] = $row['next_round_time'];

				$ctds[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ctds = [];
		}
		$results = (object)null;
		$results->data = $ctds;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function getAllClinicalTaskData($status = ['Active', 'Discharged', 'Ended', 'Cancelled'], $getFull = FALSE, $pdo = null)
	{
		$ctds = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT  d.*, c.patient_id, c.in_patient_id  FROM clinical_task_data d LEFT JOIN clinical_task c ON c.id =d.clinical_task_id WHERE d.status IN ('" . implode("', '", $status) . "') ORDER BY d.last_round_time";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ctd = new ClinicalTaskData($row['id']);
				if ($getFull) {
					$ct = (new ClinicalTaskDAO())->getClinicalTask($row['clinical_task_id'], FALSE, $pdo);
					$reading = (new VitalSignDAO())->getPatientVitalSigns($row['patient_id'], $row['in_patient_id'], null, FALSE, $pdo);
					$cancelledBy = ($row['cancelled_by'] === null ? null : (new StaffDirectoryDAO())->getStaff($row['cancelled_by'], FALSE, $pdo));
				} else {
					$ct = new ClinicalTask($row['clinical_task_id']);
					$reading = [];
					$cancelledBy = new StaffDirectory($row['cancelled_by']);
				}
				$ctd->setClinicalTask($ct);
				$ctd->setDrug(($row['drug_id'] === null ? null : (new DrugDAO())->getDrug($row['drug_id'], FALSE, $pdo)));
				$ctd->setGeneric(($row['drug_generic_id'] === null ? null : (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], FALSE, $pdo)));
				$ctd->setDose($row['dose']);
				$ctd->setFrequency($row['frequency']);
				$ctd->setEntryTime($row['entry_time']);
				$ctd->setLastRoundTime($row['last_round_time']);
				$ctd->setEndRoundTime($row['end_round_time']);
				$ctd->setRoundCount($row['round_count']);
				$ctd->setTaskCount($row['task_count']);
				$ctd->setStatus($row['status']);
				$ctd->setBilled($row['billed']);
				$ctd->setType((new VitalDAO())->get($row['type_id'], $pdo));
				$ctd->setDescription($row['description'] !== "" ? $row['description'] : null);
				$ctd->setCancelReason($row['cancel_reason']);
				$ctd->setCancelledBy($cancelledBy);
				$ctd->setCancelTime($row['cancel_time']);
				$ctd->setCreatedBy($row['created_by']);
				$ctd->setLastReading(($row['type_id'] !== NULL) ? (new VitalSignDAO())->getPatientLastVitalSign($row['patient_id'], $row['type_name'], $row['in_patient_id'], FALSE, $pdo) : null);
				$ctd->setReadings($reading);
				if ($row['last_round_time'] === null || $row['last_round_time'] === "") {
					$ctd->setNextRoundTime((new DateTime($row['entry_time']))->format('Y-m-d H:i:s'));
				} else {
					$ctd->setNextRoundTime((new DateTime($row['last_round_time']))->add(new DateInterval('PT' . $row['frequency'] . 'M'))->format('Y-m-d H:i:s'));
				}

				$ctds[] = $ctd;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ctds = [];
		}
		return $ctds;
	}

	function getPatientTaskData($pid, $status = ['Active', 'Discharged', 'Ended', 'Cancelled'], $getFull = FALSE, $aid, $pdo = null)
	{
		$ctds = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT v.name AS type_name, d.*, c.patient_id, c.in_patient_id FROM clinical_task_data d LEFT JOIN vital v ON v.id=d.type_id LEFT JOIN clinical_task c ON c.id =d.clinical_task_id WHERE d.status IN ('" . implode("', '", $status) . "') AND c.patient_id = $pid " . (!is_blank($aid) ? " AND c.in_patient_id=$aid" : " AND c.in_patient_id IS NULL") . " ORDER BY d.last_round_time";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ctd = new ClinicalTaskData($row['id']);
				if ($getFull) {
					$ct = (new ClinicalTaskDAO())->getClinicalTask($row['clinical_task_id'], FALSE, $pdo);
					$reading = (new VitalSignDAO())->getPatientVitalSigns($row['patient_id'], $row['in_patient_id'], null, FALSE, $pdo);
					$cancelledBy = ($row['cancelled_by'] === null ? null : (new StaffDirectoryDAO())->getStaff($row['cancelled_by'], FALSE, $pdo));
				} else {
					$ct = new ClinicalTask($row['clinical_task_id']);
					$reading = [];
					$cancelledBy = new StaffDirectory($row['cancelled_by']);
				}
				$ctd->setClinicalTask($ct);
				$ctd->setDrug(($row['drug_id'] === null ? null : (new DrugDAO())->getDrug($row['drug_id'], TRUE, $pdo)));
				$ctd->setGeneric(($row['drug_generic_id'] === null ? null : (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], TRUE, $pdo)));
				$ctd->setDose($row['dose']);
				$ctd->setFrequency($row['frequency']);
				$ctd->setEntryTime($row['entry_time']);
				$ctd->setLastRoundTime($row['last_round_time']);
				$ctd->setEndRoundTime($row['end_round_time']);
				$ctd->setRoundCount($row['round_count']);
				$ctd->setTaskCount($row['task_count']);
				$ctd->setStatus($row['status']);
				$ctd->setBilled($row['billed']);
				$ctd->setType((new VitalDAO())->get($row['type_id'], $pdo));
				$ctd->setDescription($row['description'] !== "" ? $row['description'] : null);
				$ctd->setCancelReason($row['cancel_reason']);
				$ctd->setCancelledBy($cancelledBy);
				$ctd->setCancelTime($row['cancel_time']);
				$ctd->setCreatedBy((new StaffDirectoryDAO())->getStaff($row['created_by'], FALSE, $pdo));
				$ctd->setLastReading(($row['type_id'] !== NULL) ? (new VitalSignDAO())->getPatientLastVitalSign($row['patient_id'], $row['type_name'], $row['in_patient_id'], FALSE, $pdo) : null);
				$ctd->setReadings($reading);
				if ($row['last_round_time'] === null || $row['last_round_time'] === "") {
					$ctd->setNextRoundTime((new DateTime($row['entry_time']))->format('Y-m-d H:i:s'));
				} else {
					$ctd->setNextRoundTime((new DateTime($row['last_round_time']))->add(new DateInterval('PT' . $row['frequency'] . 'M'))->format('Y-m-d H:i:s'));
				}

				$ctds[] = $ctd;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ctds = [];
		}
		return $ctds;
	}

	function updateTask($type, $aid, $did = null, $pdo = null, $taskId = null, $ct_chart_data = null)
	{
		$status = FALSE;
		if ($aid === null) {
			//it means this is an o/p clinical task
		}
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//try to guess the task type
			$task = $this->getClinicalTaskDatum($taskId, ['Active'], FALSE, $pdo);
			
			//$nextRoundTime = (new DateTime($row['last_round_time']))->add(new DateInterval('PT' . $row['frequency'] . 'M'))->format('Y-m-d H:i:s');
			if($task->getType() == NULL && ($task->getDrug() != NULL || $task->getGeneric() != NULL) && $did !== null && $aid !== null){
				//medication for IP
				//if ($type === "Medication" && $did !== null && $aid !== null) {//
				$sql = "UPDATE clinical_task_data SET round_count=(round_count+1), last_round_time=NOW(), next_round_time=(NOW() + INTERVAL `frequency` MINUTE) WHERE (drug_id='" . $did . "' OR drug_generic_id='" . $did . "') AND clinical_task_id=(SELECT id FROM clinical_task WHERE in_patient_id=" . $aid . " ORDER BY id DESC LIMIT 1 )";
			//} else if ($type === "Medication" && $did !== null && $aid === null) {//
			} else if ($task->getType() == NULL && ($task->getDrug() != NULL || $task->getGeneric() != NULL) && $did !== null && $aid === null) {//fixme
				//medication for OP
				$sql = "UPDATE clinical_task_data SET round_count=(round_count+1), last_round_time=NOW(), next_round_time=(NOW() + INTERVAL `frequency` MINUTE) WHERE (drug_id='" . $did . "' OR drug_generic_id='" . $did . "') AND id=" . $taskId;
			} else if ($task->getType() != NULL && $aid !== null) { //vital sign for IP
				//$aid is not consistent
				$sql = "UPDATE clinical_task_data SET round_count=(round_count+1), last_round_time=NOW(), next_round_time=(NOW() + INTERVAL `frequency` MINUTE) WHERE type_id=" . $task->getType()->getId() . " AND clinical_task_id=(SELECT id FROM clinical_task WHERE in_patient_id=" . $aid->getId() . " ORDER BY id DESC LIMIT 1 )";
			} else if ($task->getType() != NULL && $aid === null) { //vital sign for OP
				$sql = "UPDATE clinical_task_data SET round_count=(round_count+1), last_round_time=NOW(), next_round_time=(NOW() + INTERVAL `frequency` MINUTE) WHERE type_id=" . $task->getType()->getId() . " AND id=" . $taskId;
			//} else if ($type === "Others") {
			} else if ($task->getType() == NULL && $task->getDrug() == NULL && $task->getGeneric() == NULL) {
				//other task for OP/IP
				$sql = "UPDATE clinical_task_data SET round_count=(round_count+1), last_round_time=NOW(), next_round_time=(NOW() + INTERVAL `frequency` MINUTE) WHERE id = $taskId";
			} else {
				//there's no query to execute. Someburri's not properly set
				return FALSE;
			}

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$ip = (isset($_POST['aid']) && trim($_POST['aid']) !== "") ? new InPatient($_POST['aid']) : null;
			$clinicalTask = (new ClinicalTaskDataDAO())->getTaskDatum($taskId, TRUE, $pdo)->getClinicalTask();

			$ctc_ = new ClinicalTaskChart();
			$ctc_->setInPatient($ip);
			$ctc_->setPatient($clinicalTask->getPatient());
			$ctc_->setClinicalTaskData(new ClinicalTaskData($_POST['taskId']));
			$ctc_->setNursingService($ct_chart_data->NursingService);
			$ctc_->setValue($ct_chart_data->Value);
			$ctc_->setCollectedBy($ct_chart_data->Staff);
			$ctc_->setComment($ct_chart_data->Comment);
			$ctc = (new ClinicalTaskChartDAO())->addClinicalTaskChart($ctc_, $pdo);

			//if outpatient, you need to add this medication as a regimen and bill for it.

			$status = TRUE;
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$status = FALSE;
		}
		return $status;
	}

	function updateTaskData($ctd, $pdo = null)
	{
		try {
			//$ctd = new ClinicalTaskData();
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;

			$sql = "UPDATE clinical_task_data SET entry_time='" . $ctd->getStartTime() . "', round_count=0,last_round_time=NULL, dose='" . $ctd->getDose() . "', frequency='" . $ctd->getFrequency() . "', next_round_time=('".$ctd->getStartTime()."' + INTERVAL ".$ctd->getFrequency()." MINUTE), `status`='Active', created_by='" . $ctd->getCreatedBy()->getId() . "' WHERE id=" . $ctd->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$status = TRUE;
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$status = FALSE;
			errorLog($e);
		}
		return $status;
	}

	function cancelTask($id, $reason, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$reason = quote_esc_str($reason);
			$sql = "UPDATE clinical_task_data SET `status`='Cancelled', cancel_reason=$reason, cancel_time=NOW(), cancelled_by={$_SESSION['staffID']} WHERE id=" . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$status = TRUE;
			} else {
				$status = FALSE;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$status = FALSE;
		}
		return $status;
	}

	function setTaskBilled($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE clinical_task_data SET billed=TRUE WHERE id=" . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$status = TRUE;
			} else {
				$status = FALSE;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$status = FALSE;
		}
		return $status;
	}

}
