<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InsuranceSchemeDAO
 *
 * @author pauldic
 */
class InsuranceSchemeDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Badge.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Insurer.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsurerDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Registration.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceTypeDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BadgeDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
			
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	
	function addInsuranceScheme($sch, $pdo = null)
	{
		//$sch = new InsuranceScheme();
		$email_split = implode(",", $sch->getEmail());
		$badgeId = ($sch->getBadge() != null) ? $sch->getBadge()->getId() : "NULL";
		$insuranceType = ($sch->getInsuranceType() != null) ? $sch->getInsuranceType()->getId() : "NULL";
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$phone = $sch->getPhone() ? quote_esc_str($sch->getPhone()) : "NULL";
			//$email = $sch->getEmail() ? quote_esc_str($sch->getEmail()) : "NULL";
			$url = $sch->getLogoUrl() ? quote_esc_str($sch->getLogoUrl()) : "NULL";
			$clinical_services_rate = $sch->getClinicalServicesRate() ? $sch->getClinicalServicesRate() : 0;
			$enroleesMax = $sch->getEnroleesMax() ? $sch->getEnroleesMax() : 1000;
			$isReference = $sch->isReference() ? var_export($sch->isReference(), true) : 'FALSE';
			$pdo->beginTransaction();
				$sql = "INSERT INTO insurance_schemes (scheme_name, badge_id, scheme_owner_id, pay_type, insurance_type_id,reg_cost_company, reg_cost_individual, hospid, credit_limit, email, phone, logo_url, clinical_services_rate, receivables_account_id, discount_account_id, partner_id, enrolees_max, is_reference)  VALUES " . "('" . escape($sch->getName()) . "', $badgeId, " . $sch->getInsurer()->getId() . ", '" . $sch->getType() . "', $insuranceType, " . $sch->getCompanyRegCost() . ", " . $sch->getIndividualRegCost() . ", " . $sch->getHospital()->getId() . ", " . $sch->getCreditLimit() . ", '" . $email_split . "', $phone, $url, $clinical_services_rate, " . ($sch->getReceivablesAccount() !== null ? $sch->getReceivablesAccount() : "NULL") . ", " . ($sch->getDiscountAccount() !== null ? $sch->getDiscountAccount() : "NULL") . ", " . ($sch->getPartner() !== null ? $sch->getPartner() : "NULL") . ", $enroleesMax, $isReference)";
				//error_log($sql);
				
				$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt->execute();
				
				if ($stmt->rowCount() > 0) {
					$sch->setId($pdo->lastInsertId());
					
					//if insurance, and scheme_registration_cost_for company is more than zero, charge that insurance provider
					if ($sch->getType() == "insurance" && $sch->getCompanyRegCost() > 0) {
						$bil = new Bill();
						$bil->setPatient(null);
						$bil->setDescription("Program Registration Fee");
						$bil->setItem(new Registration());
						$bil->setSource((new BillSourceDAO())->findSourceById(10, $pdo));
						$bil->setTransactionType("credit");
						$bil->setTransactionDate(date("Y-m-d H:i:s"));
						$bil->setAmount($sch->getCompanyRegCost());
						$bil->setDiscounted(null);
						$bil->setDiscountedBy(null);
						$bil->setClinic($sch->getHospital());
						$bil->setBilledTo($sch);
						$bill = (new BillDAO())->addBill($bil, 1, $pdo, null);
						if ($bill) {
							$pdo->commit();
							return $sch;
						} else {
							$pdo->rollBack();
							return null;
						}
					}
					
					$pdo->commit();
					
				} else {
					$sch = null;
				}
			
			$stmt = null;
		} catch (PDOException $e) {
			$sch = null;
			errorLog($e);
		}
		return $sch;
	}
	
	function get($sid, $getFull = false, $pdo = null)
	{
		$ins = new InsuranceScheme();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM insurance_schemes WHERE id=" . $sid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ins->setId($row['id']);
				$ins->setName($row['scheme_name']);
				$ins->setType($row['pay_type']);
				$ins->setBadge((new BadgeDAO())->get($row['badge_id'], $pdo));
				$ins->setIndividualRegCost($row['reg_cost_individual']);
				$ins->setCompanyRegCost($row['reg_cost_company']);
				if ($getFull) {
					$insurer = (new InsurerDAO())->getInsurer($row['scheme_owner_id'], false, $pdo);
					$clinic = (new ClinicDAO())->getClinic($row['hospid'], false, $pdo);
				} else {
					$insurer = new Insurer();
					$insurer->setId($row['scheme_owner_id']);
					$clinic = new Clinic();
					$clinic->setId($row['hospid']);
				}
				
				$ins->setInsurer($insurer);
				$ins->setHospital($clinic);
				$ins->setCreditLimit($row['credit_limit']);
				$ins->setInsuranceType((new InsuranceTypeDAO())->get($row['insurance_type_id'], $pdo));
				$ins->setEmail($row['email']);
				$ins->setPhone($row['phone']);
				$ins->setLogoUrl($row['logo_url']);
				$ins->setClinicalServicesRate($row['clinical_services_rate']);
				$ins->setEnroleesMax($row['enrolees_max']);
				$ins->setIsReference((bool)$row['is_reference']);
				if (MainConfig::$erpEnabled) {
					$ins->setReceivablesAccount($row['receivables_account_id']);
					$ins->setDiscountAccount($row['discount_account_id']);
					$ins->setPartner($row['partner_id']);
				}
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ins = null;
		}
		return $ins;
	}
	
	function findInsuranceScheme($name, $getFull = false, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM insurance_schemes WHERE scheme_name='$name'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return $this->get($row['id'], $getFull, $pdo);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function getInsuranceSchemes($getFull = false, $insurer=null, $pdo = null)
	{
		$schemes = array();
		$filter = "";
		if($insurer !=null){
			$filter = "WHERE scheme_owner_id=$insurer";
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM insurance_schemes $filter ORDER BY scheme_name";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$schemes[] = $this->get($row['id'], $getFull, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$schemes = [];
		}
		return $schemes;
	}
	
	function getInsuranceSchemesByOwner($insrId, $getFull = false, $pdo = null)
	{
		$inss = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM insurance_schemes where scheme_owner_id=$insrId";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$inss[] = $this->get($row['id'], $getFull, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = $pdo = null;
			$inss = [];
		}
		return $inss;
	}
	
	function getSchemePatientsCount($sid, $pdo = null)
	{
		//        $pats = array();
		$pats = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT COUNT(*) AS x FROM insurance WHERE insurance_scheme = $sid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pats = $row['x'];
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pats = 0;
		}
		return $pats;
	}
	
	function getSchemePatients($sid, $pdo = null)
	{
		$pats = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT i.patient_id, CONCAT_WS(' ', pd.fname, pd.mname, pd.lname) AS patientName, i.enrollee_number, pd.active, pd.phonenumber FROM insurance i LEFT JOIN patient_demograph pd ON pd.patient_ID=i.patient_id WHERE insurance_scheme = $sid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$report = (object)null;
				$report->PatientName = $row['patientName'];
				$report->PatientId = $row['patient_id'];
				$report->active = $row['active'];
				$report->Phone = $row['phonenumber'];
				$report->EnrolleeNumber = $row['enrollee_number'];
				$pats[] = $report;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pats = array();
		}
		return $pats;
	}
	
	function getSchemeReceivableAccountBySource($scheme, $source, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT receivable_account_id FROM revenue_account WHERE bill_source_id=$source AND insurance_scheme_id=$scheme";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return $row['receivable_account_id'];
			}
			return "";
		} catch (PDOException $e) {
			errorLog($e);
			return "";
		}
	}
	
	function setSchemeReceivableAccountBySource($scheme, $source, $account_id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO revenue_account SET bill_source_id=$source, insurance_scheme_id=$scheme, receivable_account_id=$account_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return $account_id;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function updateInsuranceScheme($scheme, $pdo = null)
	{
		//$scheme = new InsuranceScheme();
		$badge_id = ($scheme->getBadge() != null) ? $scheme->getBadge()->getId() : "NULL";
		$insuranceType = ($scheme->getInsuranceType() != null) ? $scheme->getInsuranceType()->getId() : "NULL";
		
		if (MainConfig::$erpEnabled) {
			$erpPart = " ,receivables_account_id = " . $scheme->getReceivablesAccount() . ", ";
			$erpPart .= " discount_account_id = " . $scheme->getDiscountAccount() . ", ";
			$erpPart .= " partner_id = " . $scheme->getPartner() . "";
		} else {
			$erpPart = "";
		}
		try {
			$phone = $scheme->getPhone() ? quote_esc_str($scheme->getPhone()) : "NULL";
			$email = $scheme->getEmail() ? quote_esc_str($scheme->getEmail()) : "NULL";
			$url = $scheme->getLogoUrl() ? quote_esc_str($scheme->getLogoUrl()) : "NULL";
			$clinical_services_rate = $scheme->getClinicalServicesRate() ? $scheme->getClinicalServicesRate() : 0;
			$enrolees_max = $scheme->getEnroleesMax() ? $scheme->getEnroleesMax() : 1000;
			$isReference = $scheme->isReference() ? var_export($scheme->isReference(), true) : 'FALSE';
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE insurance_schemes SET scheme_name='" . escape($scheme->getName()) . "', scheme_owner_id=" . $scheme->getInsurer()->getId() . ", pay_type='" . $scheme->getType() . "', insurance_type_id=$insuranceType, badge_id=$badge_id, reg_cost_individual=" . $scheme->getIndividualRegCost() . ", reg_cost_company=" . $scheme->getCompanyRegCost() . ", credit_limit = " . $scheme->getCreditLimit() . ", email=$email, phone=$phone, logo_url=$url, clinical_services_rate=$clinical_services_rate, enrolees_max=$enrolees_max, is_reference=$isReference $erpPart WHERE id=" . $scheme->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$status = true;
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$status = false;
		}
		return $status;
	}
	
}
