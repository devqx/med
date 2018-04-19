<?php

/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 12/10/15
 * Time: 4:55 PM
 */
class AntenatalPackagesDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AntenatalPackages.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function getPackages($pdo = null)
	{
		$packs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM antenatal_packages";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pack = (new AntenatalPackages($row['id']))->setName($row['package'])->setAmount($row['amount'])->setCode($row['billing_code']);
				$packs[] = $pack;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$packs = [];
		}
		return $packs;
	}

	function add($package, $pdo = null)
	{
		//$package = new AntenatalPackages();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$code = "AP" . generateBillableItemCode('antenatal_packages', $pdo);
			$package->setCode($code);
			$sql = "INSERT INTO antenatal_packages SET billing_code = '".$package->getCode()."', package='" . escape($package->getName()) . "', amount='" . $package->getAmount() . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$package->setId($pdo->lastInsertId());
				$insureBI = new InsuranceBillableItem();
				$insureBI->setItem($package);
				$insureBI->setItemDescription(escape($package->getName()));
				$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(15, $pdo));
				$insureBI->setClinic(new Clinic(1));

				$insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
				if ($insBI == NULL) {
					$pdo->rollBack();
					$stmt = null;
					return NULL;
				}
				$insureIC = new InsuranceItemsCost();
				$insureIC->setItem($package);
				$insureIC->setSellingPrice ($package->getAmount());
				$insureIC->setInsuranceScheme(new InsuranceScheme(1));
				$insureIC->setClinic(new Clinic(1));
				$insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($insureIC, $pdo);
				if ($insIC == NULL) {
					$pdo->rollBack();
					$stmt = null;
					return NULL;
				}
				$pdo->commit();
				return $package;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function get($iid, $pdo = null)
	{
		$pack = new AntenatalPackages();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM antenatal_packages WHERE id=" . $iid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$items = (new AntenatalPackageItemsDAO())->getPackageItems($row['id'], $pdo);
				$pack->setId($row['id'])->setName($row['package'])->setAmount($row['amount'])->setCode($row['billing_code'])->setItems($items);
			} else {
				$pack = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$pack = null;
		}
		return $pack;
	}
function getByCode($code, $pdo = null)
	{
		$pack = new AntenatalPackages();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM antenatal_packages WHERE billing_code='$code'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pack->setId($row['id'])->setName($row['package'])->setAmount($row['amount'])->setCode($row['billing_code']);
			} else {
				$pack = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$pack = null;
		}
		return $pack;
	}

	function update($package, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE antenatal_packages SET package='" . $package->getName() . "', amount='" . $package->getAmount() . "' WHERE id=" . $package->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return $package;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}