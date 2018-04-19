<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/24/16
 * Time: 12:55 PM
 */
class InpatientObservationDAO
{
	private $conn = null;

	function __construct() {
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InpatientObservation.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InPatient.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function add($observation, $pdo=NULL){
		//$observation = new InpatientObservation();
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO(): $pdo;
			$ipId = $observation->getInPatient()->getId();
			$date = ($observation->getDateEntered() == NULL) ? "NOW()" : $observation->getDateEntered();
			$userId = ($observation->getUser() == NULL) ? $_SESSION['staffID'] : $observation->getUser()->getId();
			$note = escape($observation->getNote());
			$sql = "INSERT INTO ip_observation (in_patient_id, `date`, user_id, note) VALUES ($ipId, $date, $userId, '$note')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()==1){
				$observation->setId($pdo->lastInsertId());
				return $observation;
			}
			return NULL;
		}catch (PDOException $e){
			errorLog($e);
			return NULL;
		}
	}

	function get($id, $pdo=NULL){
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO(): $pdo;
			$sql = "SELECT * FROM ip_observation WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ip = (new InPatientDAO())->getInPatient($row['in_patient_id'], FALSE, $pdo);
				$user = (new StaffDirectoryDAO())->getStaff($row['user_id'], FALSE, $pdo);
				return (new InpatientObservation($row['id']))->setInPatient($ip)->setDateEntered($row['date'])->setUser($user)->setNote($row['note']);
			}
			return NULL;
		}catch (PDOException $e){
			errorLog($e);
			return NULL;
		}
	}

	function forIpInstance($ipId, $page = 0, $pageSize = 10, $pdo=NULL){
		$sql = "SELECT * FROM ip_observation WHERE in_patient_id=$ipId";

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
			$pdo = $pdo == NULL ? $this->conn->getPDO(): $pdo;
			$sql .= " ORDER BY `date` DESC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				//save some memory by ignoring this property in this function
				$ip = null; //(new InPatientDAO())->getInPatient($row['in_patient_id'], FALSE, $pdo);
				$user = (new StaffDirectoryDAO())->getStaff($row['user_id'], FALSE, $pdo);
				$data[] = (new InpatientObservation($row['id']))->setInPatient($ip)->setDateEntered($row['date'])->setUser($user)->setNote($row['note']);
			}
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