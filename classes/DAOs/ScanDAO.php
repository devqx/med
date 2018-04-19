<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/13/14
 * Time: 2:21 PM
 */
class ScanDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Scan.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ScanCategory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ScanCategoryDAO.php';
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

	function getScan($id, $pdo = null)
	{
		$scan = new Scan();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT s.*, c.selling_price FROM scan s LEFT JOIN insurance_items_cost c ON c.item_code=s.billing_code WHERE s.id = $id AND c.insurance_scheme_id = 1";
//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$scan->setId($row['id']);
				$scan->setName($row['name']);
				$scan->setCode($row['billing_code']);
				$scan->setBasePrice($row['selling_price']);
				$scan->setCategory((new ScanCategoryDAO())->getCategory($row['category_id'], $pdo));
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

	function getScanByCode($code, $pdo = null)
	{
		$scan = new Scan();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT s.*, c.selling_price FROM scan s LEFT JOIN insurance_items_cost c ON c.item_code=s.billing_code WHERE s.billing_code = '$code' #AND c.insurance_scheme_id=1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				// return (object)$row;
				$scan->setId($row['id']);
				$scan->setName($row['name']);
				$scan->setCode($row['billing_code']);
				$scan->setBasePrice($row['selling_price']);
				$scan->setCategory((new ScanCategoryDAO())->getCategory($row['category_id'], $pdo));
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

	function getScans($pdo = null)
	{
		$scans = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT s.*, c.selling_price FROM scan s LEFT JOIN insurance_items_cost c ON s.billing_code=c.item_code WHERE c.insurance_scheme_id=1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$scan = new Scan();
				$scan->setId($row['id']);
				$scan->setName($row['name']);
				$scan->setCode($row['billing_code']);
				$scan->setBasePrice($row['selling_price']);
				$scan->setCategory((new ScanCategoryDAO())->getCategory($row['category_id'], $pdo));

				$scans[] = $scan;
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$scans = [];
		}
		return $scans;
	}

	function getScansByIds($ids, $pdo = null)
	{
		$scans = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM scan WHERE id IN ($ids)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$scans[] = $this->getScan($row['id'], $pdo);
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$scans = [];
		}
		return $scans;
	}

	function findScans($search, $pdo = null)
	{
		$scans = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT s.* FROM scan s LEFT JOIN scan_category c ON s.category_id=c.id WHERE s.name LIKE '%$search%' OR s.billing_code LIKE '%$search%' OR c.name LIKE '%$search%'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$scans[] = $this->getScan($row['id'], $pdo);
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$scans = [];
		}
		return $scans;
	}

	function addScan($scan, $price_,  $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$code = "SC" . generateBillableItemCode('scan', $pdo);
			$scan->setCode($code);
			$name = quote_esc_str($scan->getName());
			$category_id = $scan->getCategory()->getId();
			$price = $scan->getBasePrice() ? $scan->getBasePrice() : $price_;
			$sql = "INSERT INTO scan (`name`, billing_code, category_id) VALUES ($name, '$code','$category_id')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$insureBI = new InsuranceBillableItem();
			$insureBI->setItem($scan);
			$insureBI->setItemDescription($scan->getName());
			$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(7, $pdo));

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
			$insureIC->setItem($scan);
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
				$scan->setId($pdo->lastInsertId());
				//this is wrong. the $pdo::lastInsertId call here, would return the InsuranceItemsCost id that was just inserted
				$pdo->commit();
			} else {
				$scan = null;
				$pdo->rollBack();
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$scan = null;
		}
		return $scan;
	}

	function updateScan($scan, $price, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$sql = "UPDATE scan SET `name` = '" . escape($scan->getName()) . "', category_id = '" . $scan->getCategory()->getId() . "' WHERE id = " . $scan->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$clinic = new Clinic(1);

			$insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($scan->getCode(), TRUE, $pdo);
			$insureBI->setItemDescription($scan->getName());
			$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(7, $pdo));
			$insureBI->setClinic($clinic);
			$insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);

			if ($insBI == null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}
			$insureIC = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($scan->getCode(), 1, FALSE, TRUE, $pdo);

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
			$scan = null;

			return null;//$e->getMessage();

		}
		return $scan;
	}
}