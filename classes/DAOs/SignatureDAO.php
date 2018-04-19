<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/25/16
 * Time: 1:24 PM
 */
class SignatureDAO
{
	private $conn = null;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Signature.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo = null)
	{
		if ($id === null || is_blank($id)) {
			return null;
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM signature WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			//$stmt->bindColumn(2, $data, PDO::PARAM_LOB);
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$patient = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo);
				return (new Signature($row['id']))->setActive($row['active'])->setBlob(@hex2bin($row['signature']))->setDate($row['date_added'])->setPatient($patient);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	public function filter($patientId = null, $page, $pageSize, $pdo = null)
	{
		$data = new ArrayObject();
		
		$sql = "SELECT * FROM signature WHERE  active IS TRUE";
		$sql .= ($patientId != null) ? " AND patient_id=$patientId" : '';
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
				$data[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$data = new ArrayObject();
		}
		
		$results = (object)null;
		$results->data = $data;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function getPatientSignature($patient_id, $pdo = null)
	{
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM signature WHERE patient_id = $patient_id AND active IS TRUE LIMIT 1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			//$stmt->bindColumn(2, $data, PDO::PARAM_LOB);
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$patient = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo);
				return (new Signature($row['id']))->setActive($row['active'])->setBlob(@hex2bin($row['signature']))->setDate($row['date_added'])->setPatient($patient);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
}