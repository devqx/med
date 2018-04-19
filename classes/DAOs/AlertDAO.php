<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/7/15
 * Time: 1:13 PM
 */
class AlertDAO
{
	private $conn = null;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Alert.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function add($alert, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$patient_id = $alert->getPatient()->getId();
			$type = $alert->getType();
			$message = $alert->getMessage();
			$sql = "INSERT INTO alert (patient_id, type, message, `read`) VALUES ($patient_id, '$type', '$message', FALSE)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				$alert->setId($pdo->lastInsertId());
				return $alert;
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
			$sql = "SELECT * FROM alert WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$alert = new Alert($row['id']);
				$alert->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, null));
				$alert->setType($row['type']);
				$alert->setMessage($row['message']);
				$alert->setReadBy((new StaffDirectoryDAO())->getStaff($row['read_by'], false, $pdo));
				$alert->setTime($row['time']);
				$alert->setRead((bool)$row['read']);
				return $alert;
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
			$sql = "SELECT * FROM alert";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$alerts = [];
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$alert = new Alert($row['id']);
				$alert->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, null));
				$alert->setType($row['type']);
				$alert->setMessage($row['message']);
				$alert->setReadBy((new StaffDirectoryDAO())->getStaff($row['read_by'], false, $pdo));
				$alert->setTime($row['time']);
				$alert->setRead((bool)$row['read']);
				
				$alerts[] = $alert;
			}
			return $alerts;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	function getForPatient($pid, $read = true, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM alert WHERE patient_id = $pid AND `read` = " . var_export($read, true);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$alerts = [];
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$alert = new Alert($row['id']);
				$alert->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, null));
				$alert->setType($row['type']);
				$alert->setMessage($row['message']);
				$alert->setReadBy((new StaffDirectoryDAO())->getStaff($row['read_by'], false, $pdo));
				$alert->setTime($row['time']);
				$alert->setRead((bool)$row['read']);
				
				$alerts[] = $alert;
			}
			return $alerts;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	function dismiss($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE alert SET `read`=TRUE WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() > 0) {
				return true;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}