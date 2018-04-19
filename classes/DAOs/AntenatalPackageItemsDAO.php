<?php

/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 12/10/15
 * Time: 5:46 PM
 */
class AntenatalPackageItemsDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AntenatalPackageItem.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AntenatalPackages.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackagesDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function getItemsByPackage($package_id, $pdo = null)
	{
		$items = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM antenatal_package_item WHERE package_id=" . $package_id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$items[] = $this->get($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = $pdo = null;
			$items = [];
		}
		return $items;
	}
	
	function addItems($items)
	{
		try {
			$pdo = $this->conn->getPDO();
			$pdo->beginTransaction();
			$sql = "INSERT INTO antenatal_package_item (package_id, item_id, item_code, `type`, item_usage) VALUES ";
			$ss = [];
			foreach ($items as $it) {
				$packageId = $it->getPackage()->getId();
				$itemName = $it->getName();
				$type = quote_esc_str($it->getType());
				$itemCode = quote_esc_str($it->getItemCode());
				$usage = $it->getUsage();
				$ss[] = "($packageId, $itemName, $itemCode, $type, $usage)";
			}
			$sql .= implode(", ", $ss);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$pdo->commit();
			$status = true;
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$status = false;
		}
		return $status;
	}
	
	function get($iicid, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM antenatal_package_item WHERE id=$iicid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new AntenatalPackageItem($row['id']))->setName($row['item_id'])
					//->setPackage((new AntenatalPackagesDAO())->getPackage($row['package_id'], $pdo))
					->setType($row['type'])
					->setItemCode($row['item_code'])
					->setUsage($row['item_usage']);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function getItemByPackage($pid, $iicid, $pdo = null)
	{
		$item = new AntenatalPackageItem();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM antenatal_package_item WHERE package_id='" . $pid . "' AND item_id='" . $iicid . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$item->setId($row['id']);
				$item->setName($row['item_id']);
				$item->setPackage((new AntenatalPackagesDAO())->get($row['package_id'], $pdo));
				$item->setType($row['type']);
				$item->setUsage($row['item_usage']);
				
			} else {
				$item = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$item = null;
		}
		return $item;
	}
	
	function getPackageItems($packageId, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM antenatal_package_item WHERE package_id=$packageId";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$items = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$items[] = $this->get($row['id'], $pdo);
			}
			return $items;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	function removeItem($item, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "DELETE FROM antenatal_package_item WHERE id = " . $item->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return true;
			}
			return false;
		} catch (PDOException $e) {
			return null;
		}
	}
	
	function updateItemUsages($item, $pdo = null)
	{
		$status = false;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE antenatal_package_item SET item_usage =" . $item->getUsage() . " WHERE id = " . $item->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$status = true;
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
		} catch (Exception $e) {
			$stmt = null;
		}
		return $status;
	}
}