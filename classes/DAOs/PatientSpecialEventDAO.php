<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/9/15
 * Time: 12:31 PM
 */
class PatientSpecialEventDAO
{
	private $conn = null;

	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientSpecialEvent.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function add($event, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$patient_id = $event->getPatient()->getId();
			$note = escape($event->getNote());
			$alert_date = $event->getAlertDate() ? quote_esc_str($event->getAlertDate()):'NULL';
			$staff = $event->getNotedBy()->getId();

			$sql = "INSERT INTO special_event (patient_id, note, noted_by, `date`,alert_date) VALUES ($patient_id, '$note', '$staff', NOW(),$alert_date)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				$event->setId($pdo->lastInsertId());
				return $event;
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
			$sql = "SELECT * FROM special_event WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$event = (new PatientSpecialEvent($row['id']))
					->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, null))
					->setNote($row['note'])
					->setNotedBy((new StaffDirectoryDAO())->getStaff($row['noted_by'], FALSE, $pdo))
					->setDate($row['date'])
					->setDismissed(boolval($row['dismissed']));
				return $event;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function all($pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM special_event";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$events = [];

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$event = (new PatientSpecialEvent($row['id']))
					->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, null))
					->setNote($row['note'])
					->setNotedBy((new StaffDirectoryDAO())->getStaff($row['noted_by'], FALSE, $pdo))
					->setDate($row['date'])
					->setDismissed(boolval($row['dismissed']));
				$events[] = $event;
			}
			return $events;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}

	function getForPatient($pid, $hidden=FALSE, $pdo = null)
	{

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$alert_date = date('Y-m-d H:i:00');

			$sql = "SELECT * FROM special_event WHERE patient_id=$pid AND (alert_date <= '$alert_date' OR alert_date IS NULL)";
			$sql .= (!$hidden) ? ' AND dismissed IS FALSE': '';
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$events = [];

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$event = (new PatientSpecialEvent($row['id']))
					->setPatient(null) // to save memory
					->setNote($row['note'])
					->setNotedBy((new StaffDirectoryDAO())->getStaff($row['noted_by'], FALSE, $pdo))
					->setDate($row['date'])
					->setDismissed(boolval($row['dismissed']));
				$events[] = $event;
			}
			return $events;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}

	function countForPatient($pid, $pdo = null)
	{
		try {
		    $alert_date = date('Y-m-d H:i:00');

			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT COUNT(*) AS num FROM special_event WHERE dismissed IS false AND patient_id=$pid AND (alert_date <= '$alert_date' OR alert_date IS NULL)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return $row['num'];
			}
			return 0;
		} catch (PDOException $e) {
			errorLog($e);
			return 0;
		}
	}


	function dismiss($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE special_event SET `dismissed`=TRUE WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() > 0) {
				return TRUE;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	function undismiss($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE special_event SET `dismissed`= FALSE WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() > 0) {
				return TRUE;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}