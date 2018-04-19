<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/15/16
 * Time: 2:38 PM
 */
class IVFPackageDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/IVFPackage.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function all($pdo = null)
	{
		$packs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM ivf_package";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$packs[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$packs = [];
		}
		return $packs;
	}

	function get($iid, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT v.*, i.selling_price FROM ivf_package v LEFT JOIN insurance_items_cost i ON i.item_code=v.billing_code WHERE v.id=$iid AND insurance_scheme_id=1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new IVFPackage($row['id']))->setName($row['name'])->setAmount($row['selling_price'])->setCode($row['billing_code']);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	function getByCode($code, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM ivf_package WHERE billing_code='$code'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return $this->get($row['id'], $pdo);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

}