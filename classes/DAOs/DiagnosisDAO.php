<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/3/14
 * Time: 11:33 AM
 */
class DiagnosisDAO
{

	private $conn = null;

	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Diagnosis.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function getDiagnosis($id, $pdo = null)
	{
		$diagnosis = new Diagnosis();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM diagnoses WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$diagnosis->setId($row['id']);
				$diagnosis->setCode($row['code']);
				$diagnosis->setType($row['type']);
				$diagnosis->setName($row['case']);
				$parent = ($row['parent_id'] == null) ? null : $this->getDiagnosis($row['parent_id'], $pdo);
				$diagnosis->setParent($parent);
				$diagnosis->setOi($row['oi']);
			} else {
				$diagnosis = null;
			}
			$stmt = null;
		} catch (PDOException $E) {
			$diagnosis = null;
		}
		return $diagnosis;
	}

	function getDiagnoses($pdo = null)
	{
		$diagnoses = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM diagnoses";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$diagnosis = $this->getDiagnosis($row['id']);
				$diagnoses[] = $diagnosis;
			}
			$stmt = null;
		} catch (PDOException $E) {
			$diagnosis = null;
		}
		return $diagnoses;
	}

	function findDiagnoses($search, $type = null, $pdo = null)
	{
		$filter = 1;
		if ($type != null) {
			$filter = " `type`='$type'";
		}
		$diagnoses = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM diagnoses WHERE $filter AND (`case` LIKE '%$search%' OR `code` LIKE '$search%')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$diagnosis = $this->getDiagnosis($row['id']);
				$diagnoses[] = $diagnosis;
			}
			$stmt = null;
		} catch (PDOException $E) {
			errorLog($E);
		}
		return $diagnoses;
	}

	function addDiagnosis($diagnosis, $pdo = null)
	{
		//TODO: finish up
	}

	function getPatientDiagnoses($pid, $pdo = null)
	{

	}
}