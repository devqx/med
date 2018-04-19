<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/19/16
 * Time: 10:15 AM
 */
class IVFTreatmentDAO
{
	private $conn = null;

	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/IVFTreatment.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFDrugDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM ivf_treatment WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$enrolment = (new IVFEnrollment($row['enrollment_id']));
				$user = (new StaffDirectoryDAO())->getStaff($row['user_id'], FALSE, $pdo);
				return (new IVFTreatment($row['id']))->setEnrolment($enrolment)->setDate($row['date'])->setDayOfCycle($row['day_of_cycle'])->setDrug( (new IVFDrugDAO())->get($row['drug_id']))->setValue($row['value'])->setFindings($row['findings'])->setDuration($row['duration'])->setComment($row['comment'])->setUser($user);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function forInstance($instanceId, $page = 0, $pageSize = 10, $pdo = null)
	{
		$sql = "SELECT * FROM ivf_treatment WHERE enrollment_id=$instanceId";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			errorLog($e);
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$enrolment = (new IVFEnrollment($row['enrollment_id']));
				$user = (new StaffDirectoryDAO())->getStaff($row['user_id'], FALSE, $pdo);
				$data[] = (new IVFTreatment($row['id']))->setEnrolment($enrolment)->setDate($row['date'])->setDayOfCycle($row['day_of_cycle'])->setDrug( (new IVFDrugDAO())->get($row['drug_id']))->setValue($row['value'])->setFindings($row['findings'])->setDuration($row['duration'])->setComment($row['comment'])->setUser($user);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$data = [];
		}
		$results = (object)null;
		$results->data = $data;
		$results->total = $total;
		$results->page = $page;
		return $results;
	}
}