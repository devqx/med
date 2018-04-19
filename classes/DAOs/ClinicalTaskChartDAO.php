<?php

/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/7/16
 * Time: 11:00 PM
 */
class ClinicalTaskChartDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTaskChart.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/NursingServiceDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function addClinicalTaskChart($ctc, $pdo = null)
	{
		$taskId = $ctc->getClinicalTaskData()->getId();
		$value = ($ctc->getValue() != null) ? $ctc->getValue() : "NULL";
		@session_start();$collectedBy = $ctc->getCollectedBy() ? $ctc->getCollectedBy()->getId() : $_SESSION['staffID'];
		$aid = ($ctc->getInPatient() != null) ? $ctc->getInPatient()->getId() : "NULL";
		$patient = $ctc->getPatient()->getId();
		$nursingService = ($ctc->getNursingService() != null) ? $ctc->getNursingService()->getId() : "NULL";
		$comment = !is_blank($ctc->getComment()) ? quote_esc_str($ctc->getComment()) : 'NULL';
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$canCommit = true;
			try {
				$pdo->beginTransaction();
			} catch (PDOException $e) {
				$canCommit = FALSE;
			}
			$sql = "INSERT INTO clinical_task_chart (admission_id, patient_id, clinical_task_data_id, nursing_service_id, `value`, collected_by, `comment`) VALUES ($aid, $patient, $taskId, $nursingService, '$value', $collectedBy, $comment)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$ctc->setId($pdo->lastInsertId());
				if ($canCommit) {
					$pdo->commit();
				}
				return $ctc;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function get($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM clinical_task_chart WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ctc = new ClinicalTaskChart();
				$ctc->setId($row['id']);
				$ctc->setInPatient(($row['admission_id'] == '') ? '' : (new InPatientDAO())->getInPatient($row['admission_id'], FALSE, $pdo));
				$ctc->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, TRUE));
				$ctc->setClinicalTaskData((new ClinicalTaskDataDAO())->getTaskDatum($row['clinical_task_data_id'], TRUE, $pdo));
				$ctc->setNursingService(($row['nursing_service_id'] == '') ? '' : (new NursingServiceDAO())->get($row['nursing_service_id'], $pdo));
				$ctc->setValue($row['value']);
				$ctc->setCollectedBy((new StaffDirectoryDAO())->getStaff($row['collected_by'], FALSE, $pdo));
				$ctc->setCollectedDate($row['collected_date']);
				$ctc->setComment($row['comment']);
				return $ctc;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function all($ctc_, $page=0, $pageSize = 10, $taskType=null, $pdo = null)
	{
		$ctc = [];
		$patient_id = ($ctc_->getPatient() == null) ? "NULL" : $ctc_->getPatient()->getId();
		$admission_id = ($ctc_->getInPatient() == null || $ctc_->getInPatient()->getId() == '') ? '' : 'AND ctc.admission_id=' . $ctc_->getInPatient()->getId();
		//$task_type = $taskType != null ? " AND ctd.type_id = $taskType" : '';
		$task_type = "";
		if ($taskType !== null && !in_array($taskType, ['Medication', 'Others'])) {
			//$task_type = $taskType != null ? " AND ctd.type_id = $taskType" : '';
			$task_type = $taskType != null ? " AND v.name=".quote_esc_str($taskType) : '';
		} else	if ($taskType !== null && $taskType=='Medication') {
			$task_type = " AND (ctd.drug_id IS NOT NULL or ctd.drug_generic_id IS NOT NULL)";
		} else	if ($taskType !== null && $taskType=='Others') {
			$task_type = " AND ctd.description IS NOT NULL";
		}
		$sql = "SELECT ctc.* FROM clinical_task_chart ctc LEFT JOIN clinical_task_data ctd ON ctc.clinical_task_data_id=ctd.id LEFT JOIN vital v ON v.id=ctd.type_id WHERE ctc.patient_id=$patient_id $admission_id $task_type";
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

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ctc[] = $this->get($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$stmt = null;
			$ctc = [];
		}

		$results = (object)null;
		$results->data = $ctc;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}
}