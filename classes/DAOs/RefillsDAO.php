<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/23/16
 * Time: 11:51 AM
 */
class RefillsDAO
{
	
	private $conn = null;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PrescriptionData.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function all($page = 0, $pageSize = 10, $from = null, $to = null, $pdo = null)
	{
		if ($from == null) {
			$dateStart = '1970-01-01';
		} else {
			$dateStart = date("Y-m-d", strtotime($from));
		}
		if ($to == null) {
			$dateStop = date("Y-m-d");
		} else {
			$dateStop = date("Y-m-d", strtotime($to));
		}
		if (isset($from, $to)) {
			//swap the dates, since mysql does not really obey negative date between`s
			//and assign in a single line. double line assignment fails
			//because by the time the later comparison is called,
			//they would be equal and things are not consistent anymore
			list($dateStart, $dateStop) = [min($dateStart, $dateStop), max($dateStart, $dateStop)];
		}
		
		$refills = [];
		$sql = "SELECT pr.patient_id, pr.group_code, pr.service_centre_id, prd.* FROM patient_regimens pr LEFT JOIN patient_regimens_data prd ON pr.group_code=prd.group_code WHERE DATE(prd.refill_date) BETWEEN DATE('$dateStart') AND DATE('$dateStop') AND prd.refill_number > 0 AND prd.refillable IS TRUE ";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO:: CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $ex) {
			errorLog($ex);
		}
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$report = [];
				$report['id'] = $row['id'];
				$report['drug'] = $row['drug_id'] != NULL ? (new DrugDAO())->getDrug($row['drug_id'], true, $pdo)->getName() : '--';
				$report['generic'] = (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], true, $pdo);
				$report['patient'] = (new PatientDemographDAO())->getPatient($row['patient_id'], $pdo);
				$report['refill_number'] = $row['refill_number'];
				$report['refill_date'] = $row['refill_date'];
				$refills[] = $report;
			}
		} catch (PDOException $e) {
			errorLog($e);
			$refills = [];
		}
		
		$results = (object)null;
		$results->data = $refills;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	
}