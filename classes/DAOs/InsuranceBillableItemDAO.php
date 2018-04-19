<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InsuranceBillableItemDAO
 *
 * @author pauldic
 */
class InsuranceBillableItemDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Vaccine.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function addInsuranceBillableItem($item, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO insurance_billable_items (item_code, item_description, item_group_category_id, hospid)  VALUES " . "('" . $item->getItem()->getCode() . "', '" . escape($item->getItemDescription()) . "', '" . $item->getItemGroupCategory()->getId() . "', " . $item->getClinic()->getId() . ")";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$item->setId($pdo->lastInsertId());
			$stmt = null;
		} catch (PDOException $e) {
			$item = null;
			errorLog($e);
		}
		return $item;
	}
	
	function getInsuranceBillableItem($iid, $getFull = false, $pdo = null)
	{
		$insBI = new InsuranceBillableItem();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM insurance_billable_items WHERE id=" . $iid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$insBI->setId($row["id"]);
				$insBI->setItem(getItem($row['item_code'], $pdo));
				$insBI->setItemDescription($row['item_description']);
				$insBI->setItemGroupCategory((new BillSourceDAO())->getBillSource($row['item_group_category_id'], $pdo));
				if ($getFull) {
					$clinic = (new ClinicDAO())->getClinic($row['hospid'], false, $pdo);
				} else {
					$clinic = new Clinic();
					$clinic->setId($row['hospid']);
				}
				$insBI->setClinic($clinic);
			} else {
				$insBI = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$insBI = null;
		}
		return $insBI;
	}
	
	
	function getInsuranceBillableItemByCode($iCode, $getFull = false, $pdo = null)
	{
		$insBI = new InsuranceBillableItem();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM insurance_billable_items WHERE item_code='$iCode'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$insBI->setId($row["id"]);
				
				$insBI->setItem(getItem($row['item_code'], $pdo));
				$insBI->setItemDescription($row['item_description']);
				$insBI->setItemGroupCategory((new BillSourceDAO())->getBillSource($row['item_group_category_id'], $pdo));
				if ($getFull) {
					$clinic = (new ClinicDAO())->getClinic($row['hospid'], false, $pdo);
				} else {
					$clinic = new Clinic($row['hospid']);
				}
				$insBI->setClinic($clinic);
			} else {
				$insBI = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$insBI = null;
		}
		return $insBI;
	}
	
	function getInsuranceBillableItems($getFull = false, $pdo = null)
	{
		$insBIs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM insurance_billable_items";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$insBI = new InsuranceBillableItem();
				$insBI->setId($row["id"]);
				$insBI->setItem(getItem($row['item_code'], $pdo));
				$insBI->setItemDescription($row['item_description']);
				$insBI->setItemGroupCategory((new BillSourceDAO())->getBillSource($row['item_group_category_id'], $pdo));
				if ($getFull) {
					$clinic = (new ClinicDAO())->getClinic($row['hospid'], false, $pdo);
				} else {
					$clinic = new Clinic();
					$clinic->setId($row['hospid']);
				}
				$insBI->setClinic($clinic);
				$insBIs[] = $insBI;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$insBIs = array();
		}
		return $insBIs;
	}
	
	function updateBillableItem($item, $pdo = null)
	{
		//        $item = new InsuranceBillableItem();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//            $sql = "UPDATE insurance_billable_items SET item_code ='" . $item->getItem()->getCode() . "', item_description='" . escape($item->getItemDescription()) . "', item_group_category='" . $item->getItemGroupCategory() . "', hospid=" . $item->getClinic()->getId() . " WHERE id=" . $item->getId();
			$sql = "UPDATE insurance_billable_items SET item_description='" . escape($item->getItemDescription()) . "', item_group_category_id='" . $item->getItemGroupCategory()->getId() . "', hospid=" . $item->getClinic()->getId() . " WHERE id=" . $item->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			return $item;
		} catch (PDOException $e) {
			errorLog($e);
			$item = null;
		}
		return $item;
	}
	
}
