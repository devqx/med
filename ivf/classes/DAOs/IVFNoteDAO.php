<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/20/16
 * Time: 2:10 PM
 */
class IVFNoteDAO
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
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/IVFSimulation.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/IVFNote.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/SimulationDataDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/IVFEnrollmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $pdo=null){
		$sql = "SELECT * FROM ivf_note WHERE id=$id";

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new IVFNote($row['id']))->setNote($row['note'])->setInstance(new IVFEnrollment($row['instance_id']))->setDate($row['date'])->setUser((new StaffDirectoryDAO())->getStaff($row['user_id'], FALSE, $pdo));
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

	function forInstance($enrolmentId, $page, $pageSize, $pdo=null){
		$sql = "SELECT * FROM ivf_note WHERE instance_id=$enrolmentId ORDER BY `date` DESC";
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
				$data[] = $this->get($row['id'], $pdo);
			}
			//return $data;
		}catch (PDOException $e){
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