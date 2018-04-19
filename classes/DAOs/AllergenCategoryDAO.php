<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/8/16
 * Time: 5:28 PM
 */
class AllergenCategoryDAO
{

	private $conn = null;

	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AllergenCategory.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}


	function add($cat, $pdo = null)
	{

	}

	function getAll($pdo = null)
	{
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM allergen_category";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO:: CURSOR_SCROLL));
			$stmt->execute();
			$cats = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$cat = new AllergenCategory($row['id']);
				$cat->setName($row['name']);
				$cat->setCreatedBy($row['created_by']);
				$cats[] = $cat;
			}
			return $cats;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function getOne($category_id, $pdo = null)
	{
		if(is_null($category_id)) {return null;}
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM allergen_category WHERE id=$category_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO:: CURSOR_SCROLL));
			$stmt->execute();
			if ($row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new AllergenCategory($row_data['id']))->setName($row_data['name']);
			}
			return null;

		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

}