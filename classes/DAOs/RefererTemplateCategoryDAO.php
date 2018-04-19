<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 12/21/16
 * Time: 9:29 AM
 */
class RefererTemplateCategoryDAO
{

	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/RefererTemplateCategory.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function getCategories($pdo=NULL){
		$cats = [];
		try {
			$pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM referer_template_category";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$cat = new RefererTemplateCategory($row['id']);
				$cat->setName($row['name']);

				$cats[] = $cat;
			}
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}

		return $cats;
	}
	function getCategory($id, $pdo=NULL){
		$cat = new RefererTemplateCategory();
		try {
			$pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM referer_template_category WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$cat->setName($row['name']);
				$cat->setId($row['id']);
			} else {
				$cat = NULL;
			}
		}catch (PDOException $e){
			errorLog($e);
			return NULL;
		}

		return $cat;
	}

	function add($category, $pdo=NULL){
		try {
			$pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
			$sql = "INSERT INTO referer_template_category (`name`) VALUES ('".escape($category->getName())."')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$category->setId($pdo->lastInsertId());
			} else {
				$category = NULL;
			}
		}catch (PDOException $e){
			errorLog($e);
			return NULL;
		}

		return $category;
	}
}