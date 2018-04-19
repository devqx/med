<?php

/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 7/24/15
 * Time: 11:22 AM
 */
class ApprovedQueueDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ApprovedQueue.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientOphthalmologyDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDentistryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
			if (!isset($_SESSION)) session_start();
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($qid, $pdo = null)
	{
		$queue = new ApprovedQueue();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM approved_queue WHERE id=" . $qid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$queue->setId($row['id']);
				$queue->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, TRUE));
				$queue->setType($row['type']);
				if ($row['type'] == 'Lab') {
					$req = (new PatientLabDAO())->getLab($row['request_id'], $pdo);
				} else if ($row['type'] == 'Imaging') {
					$req = (new PatientScanDAO())->getScan($row['request_id'], $pdo);
				} else if ($row['type'] == 'Ophthalmology') {
					$req = (new PatientOphthalmologyDAO())->get($row['request_id'], $pdo);
				} else if (is_dir("dentistry") && $row['type'] == 'Dentistry') {
					$req = (new PatientDentistryDAO())->get($row['request_id'], $pdo);
				} else {
					$req = $row['request_id'];
				}
				$queue->setRequest($req);
				$queue->setApprovedTime($row['approved_time']);
				$queue->setReadStatus($row['queue_read']);
			} else {
				$queue = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$queue = null;
		}
		return $queue;
	}

	function getApprovedQueue($page = 0, $pageSize = 10, $patFilter = null, $spdo = null)
	{
		$patients = array();
		$extra = MainConfig::$approvedQueueDailyOnly ? ' DATE(q.approved_time) = DATE(NOW())' : '1';
		$sql = "SELECT q.patient_id FROM approved_queue q WHERE $extra";

		$SQL = "SELECT d.*, d.patient_id AS patientId FROM patient_demograph d WHERE d.patient_ID IN($sql) AND (d.fname LIKE '%$patFilter%' OR d.patient_ID LIKE '%$patFilter%' OR d.lname LIKE '$patFilter%')";
		try {
			$spdo = $spdo == null ? $this->conn->getPDO() : $spdo;
			$stmt = $spdo->prepare($SQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$patients[] = $row;
			}
			return $patients;

		} catch (PDOException $se) {
			error_log($se);
			return [];
		}
	}


	function allUnread($filter, $page, $pageSize, $patientId = null, $pdo = null)
	{
		$filter_ = ($filter != '') ? " AND type='$filter'" : '';
		$extra = MainConfig::$approvedQueueDailyOnly ? ' AND DATE(approved_time) = DATE(NOW())' : '';
		$patientFilter = $patientId !== null ? ' AND patient_id=' . $patientId : '';

		$sql = "SELECT * FROM approved_queue WHERE queue_read IS FALSE $filter_ $extra$patientFilter ORDER BY approved_time";

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

		$queues = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$queues[] = $this->get($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$queues = array();
		}

		$results = (object)null;
		$results->data = $queues;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function countQueue($pdo = null)
	{
		$count = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$extra = "";
			if (MainConfig::$approvedQueueDailyOnly) {
				$extra = " AND DATE(approved_time) = DATE(NOW())";
			}
			$sql = "SELECT COUNT(*) AS x FROM approved_queue WHERE queue_read IS FALSE $extra ORDER BY approved_time";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$count = $row['x'];
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$count = 0;
		}
		return $count;
	}

	function setRead($qid, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE approved_queue SET queue_read = TRUE WHERE id = " . $qid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$stmt = null;
				return TRUE;
			}
			return FALSE;
		} catch (PDOException $e) {
			return FALSE;
		}
	}
}