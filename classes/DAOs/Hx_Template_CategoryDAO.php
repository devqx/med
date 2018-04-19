<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/6/18
 * Time: 10:03 PM
 */

class Hx_Template_CategoryDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Hx_Template_Category.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function all($pdo=NULL){
		$cats = [];
		try {
			$pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM hx_template_category";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$cat = new Hx_Template_Category($row['id']);
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
		$cat = new Hx_Template_Category();
		try {
			$pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM hx_template_category WHERE id=$id";
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
}