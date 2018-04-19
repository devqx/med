<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InsuranceDAO
 *
 * @author pauldic
 */
class InsuranceDAO
{

	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Insurance.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Insurer.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Company.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/CompanyDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function addInsurance($ins, $pdo = null)
	{
		//$ins = new Insurance();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$active = $ins->getActive() ? var_export($ins->getActive(), true) : "TRUE";
			$company = ($ins->getCompany() ? $ins->getCompany()->getId() : "NULL");
			$expiration = $ins->getExpirationDate() ? quote_esc_str($ins->getExpirationDate()) : "NULL";
			$policy = $ins->getPolicyNumber() ? quote_esc_str($ins->getPolicyNumber()) : "NULL";
			$enroleeId = $ins->getEnrolleeId() ? quote_esc_str($ins->getEnrolleeId()) : 'NULL';
			$dependent = $ins->getDependent() ? $ins->getDependent()->getId() : 'NULL';
			$parentEnroleeId = $ins->getParentEnrolleeId() ? quote_esc_str($ins->getParentEnrolleeId()) : 'NULL';
			$external = var_export($ins->getExternal(), true);

			$sql = "INSERT INTO insurance (active, patient_id, insurance_scheme, insurance_expiration, policy_number, enrollee_number, coverage_type, company_id, dependent_id, parent_enrollee_id, principal_external) VALUES " . "($active, '" . $ins->getPatient()->getId() . "', " . $ins->getScheme()->getId() . ", $expiration, " . $policy . ", " . $enroleeId . ", " . ($ins->getCoverageType() === null ? "NULL" : "'" . $ins->getCoverageType() . "'") . ", $company, $dependent, $parentEnroleeId, $external)";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$ins = null;
		}
		return $ins;
	}


	function getInsurance($pid, $getFull = FALSE, $pdo = null)
	{
		$ins = new Insurance();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM insurance WHERE patient_id=" . $pid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ins->setId($row['id']);
				$pat = new PatientDemograph($row['patient_id']);
				$ins->setPatient($pat);
				$scheme = (new InsuranceSchemeDAO())->get($row['insurance_scheme'], $getFull, $pdo);
				$ins->setScheme($scheme);
				$ins->setExpirationDate($row['insurance_expiration']);
				$ins->setPolicyNumber($row['policy_number']);
				$ins->setEnrolleeId($row['enrollee_number']);
				$ins->setCoverageType($row['coverage_type']);
				$ins->setCompany((new CompanyDAO())->get($row['company_id'], $pdo));
				$ins->setActive((bool)$row['active']);
				$ins->setDependent(!is_blank($row['dependent_id']) ? new PatientDemograph($row['dependent_id']) : null );
				$ins->setParentEnrolleeId(!is_blank($row['parent_enrollee_id']) ? $row['parent_enrollee_id'] : null );
				$ins->setExternal((bool)$row['principal_external']);
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$ins = null;
			$stmt = null;
		}
		return $ins;
	}

	function updateInsurance($ins, $pdo = null)
	{
		$active = $ins->getActive() ? var_export($ins->getActive(), true) : "TRUE";
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$company = ($ins->getCompany() ? $ins->getCompany()->getId() : "NULL");
			$expiration = $ins->getExpirationDate() ? quote_esc_str($ins->getExpirationDate()) : "NULL";
			$policy = $ins->getPolicyNumber() ? quote_esc_str($ins->getPolicyNumber()) : "NULL";
			$enroleeId = $ins->getEnrolleeId() ? quote_esc_str($ins->getEnrolleeId()) : 'NULL';
			$coverage = $ins->getCoverageType() === null ? "NULL" : quote_esc_str($ins->getCoverageType());
			$dependent = $ins->getDependent() ? $ins->getDependent()->getId() : 'NULL';
			$parentEnroleeId = $ins->getParentEnrolleeId() ? quote_esc_str($ins->getParentEnrolleeId()) : 'NULL';
			$external = var_export($ins->getExternal(), true);
			$sql = "UPDATE insurance SET active=" . $active . ", insurance_scheme=" . $ins->getScheme()->getId() . ", insurance_expiration=$expiration, policy_number = $policy, enrollee_number = $enroleeId, coverage_type=$coverage, company_id=$company, dependent_id=$dependent, parent_enrollee_id=$parentEnroleeId, principal_external=$external WHERE patient_id=" . $ins->getPatient()->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$status = TRUE;
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$status = FALSE;
		}
		return $status;
	}

	function all($page = 0, $pageSize = 10, $scheme = null, $date=null, $pdo = null)
	{
		$filter = "WHERE 1";
		if ($scheme != null) {
			$filter .= " AND insurance_scheme=$scheme";
		}
		if($date != null){
			$filter .= " AND DATE(insurance_expiration) < DATE('$date')";
		}
		$sql = "SELECT i.*, pd.active AS patientActive, CONCAT_WS(' ', pd.fname, pd.mname, pd.lname) AS patientName, sch.scheme_name FROM insurance i LEFT JOIN patient_demograph pd ON pd.patient_ID=i.patient_id LEFT JOIN insurance_schemes sch ON sch.id=i.insurance_scheme $filter ORDER BY insurance_expiration DESC";
		//error_log($sql);
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
		$data = [];
		try {
			$sql .= " LIMIT $offset, $pageSize";
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {

				if ($row['patientActive']) {
					$data[] = (object)$row;
				}

			}
		} catch (PDOException $e) {
			errorLog($e);
			$data = [];
		}
		$results = (object)null;
		$results->data = $data;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function get($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM insurance WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$insurance = (new Insurance($row['id']))->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, "TRUE"))->setActive($row['active'])->setCoverageType($row['coverage_type'])->setEnrolleeId($row['enrollee_number'])->setPolicyNumber($row['policy_number'])->setScheme((new InsuranceSchemeDAO())->get($row['insurance_scheme'], FALSE, $pdo))->setCompany((new CompanyDAO())->get($row['company_id'], $pdo))->setExpirationDate($row['insurance_expiration'])->setExternal((bool)$row['principal_external']);
				return $insurance;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function getPatientInsuranceSlim($pid, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT ins.*, i2.scheme_name, i2.pay_type FROM insurance ins LEFT JOIN insurance_schemes i2 ON ins.insurance_scheme=i2.id WHERE ins.patient_id=$pid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (object)$row;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function getInsuranceSlim($sid, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT ins.*, i2.scheme_name, i2.pay_type FROM insurance ins LEFT JOIN insurance_schemes i2 ON ins.insurance_scheme=i2.id WHERE ins.insurance_scheme=$sid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (object)$row;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function getPrincipals($scheme, $term, $pdo=null){
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$searchCriteria = "(pd.patient_ID LIKE '%$term%' OR legacy_patient_id LIKE '%$term%' OR fname LIKE '%$term%' OR lname LIKE '%$term%' OR mname LIKE '%$term%' OR phonenumber LIKE '%$term%')";
			$sql = "SELECT pd.bloodgroup, pd.bloodtype, pd.title, pd.patient_ID, pd.patient_ID AS patientId, pd.legacy_patient_id, fname, lname, mname, CONCAT_WS(' ', fname, lname, mname) AS fullname, sex, date_of_birth, phonenumber FROM patient_demograph pd LEFT JOIN insurance ins ON ins.patient_id=pd.patient_ID WHERE ins.insurance_scheme=$scheme AND ins.active is true AND ins.coverage_type='principal' AND $searchCriteria";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$data = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = (object)$row;
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	function getInsuranceCurrentSize($scheme, $pdo=null){
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM insurance WHERE insurance_scheme=$scheme # AND active IS TRUE";
			//TODO do we filter only the active ones?
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			return $stmt->rowCount();
		} catch (PDOException $e) {
			errorLog($e);
			return 0;
		}
	}
	
	
}
