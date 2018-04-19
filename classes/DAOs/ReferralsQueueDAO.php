<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/7/16
 * Time: 8:38 AM
 */
class ReferralsQueueDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ReferralsQueue.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function filter($patientId = null, $acknowledged = FALSE, $specializationId=null, $page=0, $pageSize=10, $pdo = null)
	{
		$sql = "SELECT * FROM referrals_queue rq LEFT JOIN patient_demograph pd ON pd.patient_ID=rq.patient_id WHERE pd.active is true";
		$sql .= ($patientId !== null) ? " AND rq.patient_id=" . $patientId : "";
		$sql .= ($acknowledged) ? " AND rq.acknowledged IS TRUE" : " AND rq.acknowledged IS FALSE";
		$sql .= ($specializationId != null) ? " AND rq.specialization_id=" . $specializationId : " ";

		$total = 0;
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e){
			error_log("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$rfQs = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$rfQs[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$rfQs = [];
		}

		$results = (object)null;
		$results->data = $rfQs;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function get($id, $pdo = null)
	{
		if ($id == null) return null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM referrals_queue WHERE id= " . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new ReferralsQueue())->setId($row['id'])->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo))->setDoctor((new StaffDirectoryDAO())->getStaff($row['doctor_id'], FALSE, $pdo))->setWhen($row['datetime'])->setAcknowledged((bool)$row['acknowledged'])->setSpecialization( (new StaffSpecializationDAO())->get($row['specialization_id'], $pdo) )->setNote($row['note'])->setExternal((bool)$row['external']);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function filterall($id, $pdo = null)
	{
		if ($id == null) return null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM referrals_queue rq LEFT JOIN patient_demograph pd ON pd.patient_ID=rq.patient_id WHERE pd.active is true AND rq.id= $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new ReferralsQueue())->setId($row['id'])->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo))->setDoctor((new StaffDirectoryDAO())->getStaff($row['doctor_id'], FALSE, $pdo))->setWhen($row['datetime'])->setAcknowledged((bool)$row['acknowledged'])->setSpecialization( (new StaffSpecializationDAO())->get($row['specialization_id'], $pdo) )->setNote($row['note'])->setExternal((bool)$row['external']);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}