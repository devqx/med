<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WardDAO
 *
 * @author pauldic
 */
class WardDAO
{
	private $conn = null;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Block.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/CostCenter.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Ward.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BlockDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/CostCenterDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function addWard($w, $pdo = null)
	{
		//$w = new Ward();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$code = "WR" . generateBillableItemCode('ward', $pdo);
			$w->setCode($code);
			$sql = "INSERT INTO ward SET `name` = '" . $w->getName() . "', block_id = '" . $w->getBlock()->getId() . "', billing_code = '$code', cost_centre_id=" . $w->getCostCentre()->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$w->setId($pdo->lastInsertId());
			$insureBI = new InsuranceBillableItem();
			$insureBI->setItem($w);
			$insureBI->setItemDescription(escape($w->getName()));
			$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(17, $pdo));
			
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
			$insureIC->setItem($w);
			$insureIC->setSellingPrice($w->getBasePrice());
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
				$pdo->commit();
			} else {
				$w = null;
				$pdo->rollBack();
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			$w = null;
			$stmt = null;
		} catch (Exception $e) {
			$w = null;
			$stmt = null;
		}
		
		return $w;
	}
	
	function getWard($id, $getFull = false, $pdo = null)
	{
		if (is_null($id))
			return null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM ward WHERE id = " . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				if ($getFull) {
					return (new Ward($row['id']))->setBlock((new BlockDAO())->getBlock($row['block_id'], false, $pdo))->setCostCentre((new CostCenterDAO())->get($row['cost_centre_id'], $pdo))->setCode($row['billing_code'])->setBasePrice((new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($row['billing_code'], $pdo))->setName($row['name']);
				} else {
					return (new Ward($row['id']))->setBlock((new BlockDAO())->getBlock($row['block_id'], false, $pdo))->setCostCentre((new CostCenterDAO())->get($row['cost_centre_id'], $pdo))->setCode($row['billing_code'])->setBasePrice((new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($row['billing_code'], $pdo))->setName($row['name']);
				}
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function getWards($getFull = false, $pdo = null)
	{
		$wards = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM ward ORDER BY `name`";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$wards[] = $this->getWard($row['id'], $getFull, $pdo);
			}
			return $wards;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	function getWardsByBlock($bid, $getFull = false, $pdo = null)
	{
		$wards = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM ward  WHERE block_id = $bid ORDER BY `name`";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$wards[] = $this->getWard($row['id'], $getFull, $pdo);
			}
		} catch (PDOException $e) {
			$stmt = null;
			$wards = [];
		}
		return $wards;
	}
	
	function updateWard($w, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$sql = "UPDATE ward SET `name` = '" . escape($w->getName()) . "', block_id = " . $w->getBlock()->getId() . ", cost_centre_id=" . $w->getCostCentre()->getId() . " WHERE id = " . $w->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$clinic = new Clinic();
			$clinic->setId(1);
			$insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($w->getCode(), true, $pdo);
			$insureBI->setItemDescription($w->getName());
			$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(17, $pdo));
			$insureBI->setClinic($clinic);
			$insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);
			if ($insBI == null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}
			
			$insureIC = new InsuranceItemsCost();
			$insureIC->setItem($w);
			$insureIC->setSellingPrice($w->getBasePrice());
			$insureSch = new InsuranceScheme();
			$insureSch->setId(1);
			$insureIC->setInsuranceScheme($insureSch);
			$insureIC->setClinic($clinic);
			$insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);
			if ($insIC == null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}
			$pdo->commit();
			$stmt = null;
			return $w;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function getByCode($billingCode, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM ward WHERE billing_code = '$billingCode'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				// return (object)$row;
				return $this->getWard($row['id'], true, $pdo);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
}
