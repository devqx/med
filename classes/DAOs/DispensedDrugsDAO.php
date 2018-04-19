<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DispensedDrugsDAO
 *
 * @author pauldic
 */
class DispensedDrugsDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DispensedDrugs.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Drug.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugBatchDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $ex) {
			
		}
	}
	
	function add($disp, $pdo = null)
	{
		//$disp = new DispensedDrugs();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$serviceCenter = (new DrugBatchDAO())->getBatch($disp->getBatch()->getId(), $pdo)->getServiceCentre();
			$type = !is_blank($disp->getType()) ? quote_esc_str($disp->getType()) : '';
			$sql = "INSERT INTO dispensed_drugs SET drug_id = " . $disp->getDrug()->getId() . ", batch_id= " . $disp->getBatch()->getId() . ", patient_id = '" . $disp->getPatient()->getId() . "', quantity = " . $disp->getQuantity() . ", unfilled_quantity = " . $disp->getQuantityOverflow() . ", billed_to=" . $disp->getPatient()->getScheme()->getId() . ", date_dispensed=NOW(), pharmacist_id = " . $disp->getPharmacist()->getId() . ", service_center_id=" . $serviceCenter->getId().", transaction_type=$type";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
		return $disp;
	}
	
	function get($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM dispensed_drugs WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new DispensedDrugs($row['id']))->setDrug((new DrugDAO())->getDrug($row['drug_id'], true, $pdo))->setBatch((new DrugBatchDAO())->getBatch($row['batch_id'], $pdo))//->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo))
					->setQuantity($row['quantity'])//->setBilledTo((new InsuranceSchemeDAO())->get($row['billed_to'], false, $pdo))
					->setDispensedDate($row['date_dispensed'])->setPharmacist((new StaffDirectoryDAO())->getStaff($row['pharmacist_id'], false, $pdo))->setServiceCenter((new ServiceCenterDAO())->get($row['service_center_id'], $pdo));
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
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
		
		$disps = [];
		$total = 0;
		
		try {
			$sql = "SELECT SUM(quantity) AS quantity, drug_id FROM dispensed_drugs WHERE DATE(date_dispensed) BETWEEN DATE('$dateStart') AND DATE('$dateStop') GROUP BY drug_id";
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
				$report = [];
				$report['drug'] = (new DrugDAO())->getDrug($row['drug_id'], TRUE, $pdo);
				$report['quantity'] = $row['quantity'];
				
				$disps[] = $report;
				//$disps[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$disps = [];
		}
		
		$results = (object)null;
		$results->data = $disps;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}
	
	/*function allByDrug($drugId, $from = null, $to = null, $pdo = null)
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
		
		$disps = [];
		$total = 0;
		try {
			$sql = "SELECT SUM(quantity) AS quantity FROM dispensed_drugs WHERE DATE(date_dispensed) BETWEEN DATE('$dateStart') AND DATE('$dateStop') GROUP BY drug_id";
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$disps[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$disps = [];
		}
		
		$results = (object)null;
		$results->data = $disps;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}*/
}