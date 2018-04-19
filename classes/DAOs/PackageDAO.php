<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/13/16
 * Time: 11:36 AM
 */
class PackageDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Package.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PackageItem.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PackageCategoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PackageItemDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $full=FALSE, $pdo = null)
	{
		if (is_null($id)) {
			return null;
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM package WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$items = $full ? (new PackageItemDAO())->forPackage($row['id'], $pdo):[];
				return (new Package($row['id']))->setCode($row['billing_code'])->setPrice($row['price'])->setName($row['name'])->setActive((bool)$row['active'])->setExpiration($row['expiration'])->setCategory( (new PackageCategoryDAO())->get($row['category_id'], $pdo) )->setCreateDate($row['create_date'])->setItems($items)
					//->setCreateUser(null); forget about this for now
				;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	
	function all($type=null, $pdo = null)
	{
		$data = new ArrayObject();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$filter = !is_null($type) ? " pc.name =". quote_esc_str($type) : 'TRUE';
			$sql = "SELECT p.* FROM package p LEFT JOIN package_category pc ON pc.id=p.category_id WHERE $filter AND p.active IS TRUE";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], FALSE, $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	function getByCode($code, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM package WHERE billing_code='$code'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return $this->get($row['id'], FALSE, $pdo);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}