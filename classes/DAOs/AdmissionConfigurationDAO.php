<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdmissionConfigurationDAO
 *
 * @author pauldic
 */
class AdmissionConfigurationDAO
{
	
	private $conn;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		require_once $_SERVER['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
		require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Clinic.php";
		require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/InsuranceScheme.php";
		require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/InsuranceItemsCost.php";
		require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/InsuranceBillableItem.php";
		require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/AdmissionConfiguration.php";
		require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsuranceItemsCostDAO.php";
		require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsuranceBillableItemDAO.php";
		require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/BillSourceDAO.php";
		require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";
		try {
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			errorLog($e);
		}
	}
	
	function addAdmissionConfiguration($conf, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			try {
				$pdo->beginTransaction();
			} catch (PDOException $e) {
				//Transaction is already started
			}
			$conf->setCode("AD" . generateBillableItemCode('admission_config', $pdo));
			$sql = "INSERT INTO admission_config (billing_code, item_name)  VALUES ('" . $conf->getCode() . "', '" . escape($conf->getName()) . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$insureBI = new InsuranceBillableItem();
			$insureBI->setItem($conf);
			$insureBI->setItemDescription($conf->getName());
			$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(5, $pdo));
			$clinic = new Clinic();
			$clinic->setId(1);
			$insureBI->setClinic($clinic);
			$insureBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
			if ($insureBI === null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}
			
			$insureIC = new InsuranceItemsCost();
			$insureIC->setItem($conf);
			$insureIC->setSellingPrice($conf->getDefaultPrice());
			$insureSch = new InsuranceScheme();
			$insureSch->setId(1);
			$insureIC->setInsuranceScheme($insureSch);
			$insureIC->setClinic($clinic);
			$insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($insureIC, $pdo);
			if ($insIC == null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}
			if ($stmt->rowCount() > 0) {
				$conf->setId($pdo->lastInsertId());
				$pdo->commit();
			} else {
				$conf = null;
				$pdo->rollBack();
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			if ($pdo != null)
				$pdo->rollBack();
			$stmt = null;
			$conf = null;
		}
		return $conf;
	}
	
	function getPrice($acid, $pid, $pdo = null)
	{
		$price = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT c.selling_price FROM insurance_items_cost c LEFT JOIN admission_config d ON d.billing_code = c.item_code WHERE d.id = $acid AND c.insurance_scheme_id = (SELECT insurance_scheme FROM insurance WHERE patient_id = '$pid')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$price = $row['selling_price'];
			} else {
				$price = $this->getDefaultPrice($acid, $pdo);
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$price = null;
			$stmt = null;
		}
		return $price;
	}
	
	function getDefaultPrice($did, $pdo = null)
	{
		$price = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT c.selling_price FROM insurance_items_cost c LEFT JOIN admission_config d ON d.billing_code = c.item_code WHERE d.id = $did AND c.insurance_scheme_id = 1";
			//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$price = $row['selling_price'];
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$price = null;
			$stmt = null;
		}
		return $price;
	}
	
	function getAdmissionConfiguration($acid, $pdo = null)
	{
		$conf = new AdmissionConfiguration();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT d.*, ic.selling_price AS default_price FROM admission_config d LEFT JOIN insurance_items_cost ic ON d.billing_code = ic.item_code WHERE ic.insurance_scheme_id = 1 AND d.id = " . $acid;
			//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$conf->setId($row["id"]);
				$conf->setCode($row['billing_code']);
				$conf->setName($row["item_name"]);
				$conf->setDefaultPrice($row['default_price']);
			} else {
				$conf = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$conf = null;
			$stmt = null;
		}
		return $conf;
	}
	
	function getAdmissionConfigurationByCode($acid, $pdo = null)
	{
		$conf = new AdmissionConfiguration();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT d.*, ic.selling_price AS default_price FROM admission_config d LEFT JOIN insurance_items_cost ic ON d.billing_code = ic.item_code WHERE ic.insurance_scheme_id = 1 AND d.billing_code = '$acid'";
			//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				// return (object)$row;
				$conf->setId($row["id"]);
				$conf->setCode($row['billing_code']);
				$conf->setName($row["item_name"]);
				$conf->setDefaultPrice($row['default_price']);
			} else {
				$conf = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$conf = null;
			$stmt = null;
		}
		return $conf;
	}
	
	function getAdmissionConfigurations($pdo = null)
	{
		$confs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT d.*, ic.selling_price AS default_price FROM admission_config d LEFT JOIN insurance_items_cost ic ON d.billing_code = ic.item_code WHERE ic.insurance_scheme_id = 1 ORDER BY item_name";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$conf = new AdmissionConfiguration();
				$conf->setId($row["id"]);
				$conf->setCode($row['billing_code']);
				$conf->setName($row["item_name"]);
				$conf->setDefaultPrice($row['default_price']);
				
				$confs[] = $conf;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			errorLog($e);
			$confs = [];
		}
		return $confs;
	}
	
	public function update($service, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			
			if (!$pdo->inTransaction()) {
				$pdo->beginTransaction();
			}
			$sql = "UPDATE admission_config SET item_name = '" . $service->getName() . "' WHERE id=" . $service->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() >= 0) {
				$insureIC = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($service->getCode(), 1, false, true, $pdo);
				
				$insureIC->selling_price = ($service->getDefaultPrice());
				//                $insureIC->setSellingPrice($service->getDefaultPrice());
				$insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);
				if ($insIC == null) {
					$pdo->rollBack();
					$stmt = null;
					return false;
				}
				$pdo->commit();
				$stmt = null;
				return true;
			}
			$pdo->rollBack();
			return false;
		} catch (PDOException $e) {
			errorLog($e);
			return false;
		}
	}
	
}
