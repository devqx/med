<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/13/15
 * Time: 3:49 PM
 */
class DeathDAO
{
	private $conn = null;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Death.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Diagnosis.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DiagnosisDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function add($death, $pdo = null)
	{
		$patient = $death->getPatient()->getId();
		$ageAtDeath = $death->getAgeAtDeath();
		$timeOfDeath = $death->getTimeOfDeath();
		$primaryCause = $death->getDeathCausePrimary() !== null ? $death->getDeathCausePrimary()->getId() : "NULL";
		$secondaryCause = $death->getDeathCauseSecondary() !== null ? $death->getDeathCauseSecondary()->getId() : "NULL";
		$inPatient = $death->getInPatient() !== null ? $death->getInPatient()->getId() : "NULL";
		$creator = $death->getCreateUser()->getId();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT IGNORE INTO death (age_at_death, datetime_of_death, patient_id, primary_cause_id, secondary_cause_id, create_uid, in_patient_id) VALUES ('$ageAtDeath', '$timeOfDeath', $patient, $primaryCause, $secondaryCause, $creator, $inPatient)";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$death->setId($pdo->lastInsertId());
				
				return $death;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function validate($death, $pdo = null)
	{
		$validator = $death->getValidatedBy()->getId();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			
			$pdo->beginTransaction();
			$sql = "UPDATE death SET validated_by_id='$validator', validate_on=NOW() WHERE id = " . $death->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$pdo->commit();
			return true;
		} catch (PDOException $e) {
			errorLog($e);
			return false;
		}
	}
	
	function get($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM death WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$death = (new Death($row['id']))->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], true, $pdo, true))->setCertNumber($row['cert_number'])->setAgeAtDeath($row['age_at_death'])->setTimeOfDeath($row['datetime_of_death'])->setInPatient((new InPatientDAO())->getInPatient($row['in_patient_id'], true, $pdo))->setDeathCausePrimary((new DiagnosisDAO())->getDiagnosis($row['primary_cause_id'], $pdo))->setDeathCauseSecondary((new DiagnosisDAO())->getDiagnosis($row['secondary_cause_id'], $pdo))->setValidatedBy((new StaffDirectoryDAO())->getStaff($row['validated_by_id'], false, $pdo))->setCreateUser((new StaffDirectoryDAO())->getStaff($row['create_uid'], false, $pdo))->setValidatedOn($row['validate_on'])->setCreateDate($row['create_date']);
				return $death;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function all($page, $pageSize, $pdo = null)
	{
		$sql = "SELECT * FROM death";
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
		
		$deaths = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM death LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$deaths[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$deaths = [];
			$stmt = null;
		}
		
		$results = (object)null;
		$results->data = $deaths;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function findPatient($filter, $page, $pageSize, $pdo = null)
	{
		$sql = "SELECT * FROM death d LEFT JOIN patient_demograph p ON p.patient_ID=d.patient_id WHERE d.patient_id LIKE '%$filter%' OR p.fname LIKE '%$filter%' OR p.lname LIKE '%$filter%'";
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
		
		$deaths = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM death d LEFT JOIN patient_demograph p ON p.patient_ID=d.patient_id WHERE d.patient_id LIKE '%$filter%' OR p.fname LIKE '%$filter%' OR p.lname LIKE '%$filter%' LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$deaths[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$deaths = [];
		}
		
		$results = (object)null;
		$results->data = $deaths;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
}