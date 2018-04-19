<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/13/14
 * Time: 2:34 PM
 */
class ScanCategoryDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ScanCategory.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function getCategory($id, $pdo = null)
	{
		$cat = new ScanCategory();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM scan_category WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$cat->setId($row['id']);
				$cat->setName($row['name']);
			} else {
				$cat = null;
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$cat = null;
		}
		return $cat;
	}
	
	function getCategories($pdo = null)
	{
		$cats = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM scan_category";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$cat = new ScanCategory();
				$cat->setId($row['id']);
				$cat->setName($row['name']);
				
				$cats[] = $cat;
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$cats = [];
		}
		return $cats;
	}
	
	function getByName($name, $pdo = null)
	{
		$cat = new ScanCategory();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM scan_category WHERE `name` LIKE '%$name%'"; //.quote_esc_str($name);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$cat->setId($row["id"]);
				$cat->setName($row["name"]);
			} else {
				$cat = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$cat = null;
		}
		return $cat;
	}
	
	function getOrCreate($name, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$return = $this->getByName($name, $pdo);
			if ($return != null) {
				return $return;
			} else {
				$category = new ScanCategory();
				$category->setName($name);
				return $this->addCategory($category, $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function addCategory($cat, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO scan_category (name) VALUES ('" . $cat->getName() . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$cat->setId($pdo->lastInsertId());
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$cat = null;
		}
		return $cat;
	}
} 