<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DrugCategoryDAO
 *
 * @author pauldic
 */
class DrugCategoryDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DrugCategory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function getCategories($lastItemId = null, $pdo = null)
	{
		$pageSize = 50;
		$cats = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if ($lastItemId === null) {
				$sql = "SELECT * FROM drug_category ORDER BY `name`";
			} else {
				$sql = "SELECT * FROM drug_category WHERE id BETWEEN " . ($lastItemId + 1) . " AND " . ($lastItemId + $pageSize) . " ORDER BY `name` LIMIT $pageSize";
			}
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$cat = $this->getCategory($row["id"], $pdo);
				$cats[] = $cat;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$cats = null;
		}
		return $cats;
	}
	
	function find($filter, $pdo = null)
	{
		$pageSize = 50;
		$cats = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_category WHERE `name` LIKE '%$filter%' ORDER BY `name`";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$cats[] = $this->getCategory($row["id"], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$cats = null;
		}
		return $cats;
	}
	
	function getCategory($id, $pdo = null)
	{
		$cat = new DrugCategory();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_category WHERE id=" . $id;
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
			$cat = null;
		}
		return $cat;
	}
	
	function getByName($name, $pdo = null)
	{
		$cat = new DrugCategory();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_category WHERE `name`=".quote_esc_str($name);
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
	
	function addCategory($category, $pdo = null)
	{
		$cat = new DrugCategory();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//$pdo->beginTransaction();
			$sql = "INSERT INTO drug_category SET `name` = '" . $category->getName() . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				//$pdo->commit();
				$cat->setId($pdo->lastInsertId());
			} else {
				//$pdo->rollBack();
				$cat = null;
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$cat = null;
		}
		return $cat;
	}
	
	function updateCategory($category, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$sql = "UPDATE drug_category SET name = '" . $category->getName() . "' WHERE id='" . $category->getId() . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$pdo->commit();
			} else {
				$pdo->rollBack();
				$category = null;
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			$cat = null;
		}
		return $category;
	}
	
	function getOrCreate($name, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$return = $this->getByName($name, $pdo);
			
			if ($return != null) {
				return $return;
			} else {
				$category = new DrugCategory();
				$category->setName($name);
				return $this->addCategory($category, $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function deleteCategory($id, $pdo)
	{
		//TODO: not yet implemented
	}
	
}
