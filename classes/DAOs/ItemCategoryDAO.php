<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/3/17
 * Time: 11:07 AM
 */
class ItemCategoryDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ItemCategory.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR ' . $e->getMessage());
		}
	}

	function add($item, $pdo = null)
	{
		$cat = new ItemCategory();
		try {
			$pdo = $pdo === null ? $this->conn->getPDO() : $pdo;
			
			$sql = "INSERT INTO item_category SET `name` ='" . $item->getName() . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()==1){
				$cat->setId($pdo->lastInsertId());
				return $cat;
			} else {
				return NULL;
			}
			//$stmt = null;

		} catch (PDOException $e) {
		    errorLog($e);
			$cat = null;
		}
		return $cat;
	}


	function update($item, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$sql = "UPDATE item_category SET 	`name` = '" . $item->getName() . "' WHERE id='" . $item->getId() . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$pdo->commit();
			} else {
				$pdo->rollBack();
				$item = null;
			}
			$stmt = null;

		} catch (PDOException $e) {
		    errorLog($e);
			return null;
		}
		return $item;
	}

	function getCategories($lastItemId = null, $pdo = NULL) {
		$pageSize = 50;
		$categories = array();
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			if ($lastItemId === null) {
				$sql = "SELECT * FROM item_category ORDER BY id DESC";
			} else {
				$sql = "SELECT * FROM item_category WHERE id BETWEEN " . ($lastItemId + 1) . " AND " . ($lastItemId + $pageSize) . " ORDER BY `name` LIMIT $pageSize";
			}
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$categories[] = $this->get($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
		    errorLog($e);
			$categories = [];
		}
		return $categories;
	}

	function find($filter, $pdo = null)
	{
		$cats = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_category WHERE `name` LIKE '%$filter%' ORDER BY `name`";

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$cat = $this->get($row["id"], $pdo);
				$cats[] = $cat;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$cats = null;
		}
		return $cats;
	}

	
	
	
	function get($id, $pdo = null)
	{
		$cat = new ItemCategory();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_category WHERE id=$id";
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
	
	function getOrCreate($name, $pdo){
		try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$return = $this->find($name, $pdo)[0];
			if(!$return == null){
			return $return;
			}else{
				$category = new ItemCategory();
				$category->setName($name);
				return $this->add($category, $pdo);
			}
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
}