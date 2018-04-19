<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/6/18
 * Time: 10:02 PM
 */

class Hx_TemplateDAO
{
	
	private $conn = NULL;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/HxTemplate.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/Hx_Template_CategoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
			
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	function get($id, $pdo=NULL){
		try {
			$pdo = $pdo==NULL?$this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM hx_template WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				return (new HxTemplate($row['id']))->setName($row['name'])->setCategory((new Hx_Template_CategoryDAO())->getCategory($row['category_id']), $pdo)->setContent(htmlentities($row['note']));
			 }
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function all($pdo=NULL){
		$data = [];
		try {
			$pdo = $pdo==NULL?$this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM hx_template";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}
	
}