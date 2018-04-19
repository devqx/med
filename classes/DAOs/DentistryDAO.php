<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/13/14
 * Time: 2:21 PM
 */

class DentistryDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Dentistry.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DentistryCategory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryCategoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo = null)
	{
		$scan = new Dentistry();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT s.*, c.selling_price FROM dentistry s LEFT JOIN insurance_items_cost c ON c.item_code=s.billing_code WHERE s.id = $id AND c.insurance_scheme_id = 1";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$scan->setId($row['id'])->setName($row['name'])->setCode($row['billing_code'])->setBasePrice($row['selling_price'])->setCategory((new DentistryCategoryDAO())->get($row['category_id'], $pdo));
			} else {
				$scan = null;
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$scan = null;
		}
		return $scan;
	}
	
	function getByCode($code, $pdo = null)
	{
		$service = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT s.*, c.selling_price FROM dentistry s LEFT JOIN insurance_items_cost c ON c.item_code=s.billing_code WHERE s.billing_code = '$code'# AND c.insurance_scheme_id=1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				// return (object)$row;
				$service = new Dentistry();
				$service->setId($row['id']);
				$service->setName($row['name']);
				$service->setCode($row['billing_code']);
				$service->setBasePrice($row['selling_price']);
				$service->setCategory((new DentistryCategoryDAO())->get($row['category_id'], $pdo));
			} else {
				error_log("......failed to get item " . $stmt->rowCount());
				$service = null;
			}
			//$stmt = NULL;
			//$sql = NULL; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			errorLog($e);
			$service = null;
		}
		return $service;
	}
	
	function getServices($pdo = null)
	{
		$services = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT s.*, c.selling_price FROM dentistry s LEFT JOIN insurance_items_cost c ON s.billing_code=c.item_code WHERE c.insurance_scheme_id=1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$service = new Dentistry();
				$service->setId($row['id']);
				$service->setName($row['name']);
				$service->setCode($row['billing_code']);
				$service->setBasePrice($row['selling_price']);
				$service->setCategory((new DentistryCategoryDAO())->get($row['category_id'], $pdo));
				
				$services[] = $service;
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$services = [];
		}
		return $services;
	}
	
	function findServices($search, $pdo = null)
	{
		$services = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT s.*, c.selling_price FROM dentistry s LEFT JOIN insurance_items_cost c ON s.billing_code=c.item_code WHERE c.insurance_scheme_id=1 AND s.name LIKE '%$search%' ";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$service = new Dentistry();
				$service->setId($row['id']);
				$service->setName($row['name']);
				$service->setCode($row['billing_code']);
				$service->setBasePrice($row['selling_price']);
				$service->setCategory((new DentistryCategoryDAO())->get($row['category_id'], $pdo));
				
				$services[] = $service;
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$services = [];
		}
		
		return $services;
	}
	
	function add($service, $price, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$code = ("DT" . generateBillableItemCode('dentistry', $pdo));
			$service->setCode($code);
			$name = escape($service->getName());
			$category_id = $service->getCategory()->getId();
			$sql = "INSERT INTO dentistry (`name`, billing_code, category_id) VALUES ('$name', '$code',$category_id)";
			error_log("sql::::::::;".json_encode($sql));
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$insureBI = new InsuranceBillableItem();
			$insureBI->setItem($service);
			$insureBI->setItemDescription($service->getName());
			$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(14, $pdo));
			
			$clinic = new Clinic();
			$clinic->setId(1);
			$insureBI->setClinic($clinic);
			
			$insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
			if ($insBI == null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}
			
			$insureIC = new InsuranceItemsCost();
			$insureIC->setItem($service);
			$insureIC->setSellingPrice($price);
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
			if ($stmt->rowCount() == 1) {
				$service->setId($pdo->lastInsertId());
				$pdo->commit();
			} else {
				$service = null;
				$pdo->rollBack();
			}
			$stmt = null;
			$sql = null; // is it necessary ? does it save memory?
		} catch (PDOException $e) {
			$service = null;
		}
		return $service;
	}
	
	
	function update($service, $price, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$sql = "UPDATE dentistry SET `name` = '" . escape($service->getName()) . "', category_id = " . $service->getCategory()->getId() . " WHERE id = " . $service->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$clinic = new Clinic(1);
			
			$insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($service->getCode(), true, $pdo);
			$insureBI->setItemDescription($service->getName());
			$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(14, $pdo));
			$insureBI->setClinic($clinic);
			$insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);
			
			if ($insBI == null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}
			$insureIC = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($service->getCode(), 1, false, true, $pdo);
			
			$insureIC->selling_price = ($price);
			$insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);
			
			if ($insIC == null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}
			$pdo->commit();
			$stmt = null;
		} catch (Exception $e) {
			$service = null;
			errorLog($e);
			return null;//$e->getMessage();
			
		}
		return $service;
	}
	
	function getOrCreate($service, $pdo=null){
		try{
		  $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
		  $return = $this->findServices($service->getName(), $pdo)[0];
			if(!$return == null){
		  	return $return;
		  }else{
		  return $this->add($service, $service->getBasePrice(), $pdo);
		  }
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
}