<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/2/16
 * Time: 9:10 AM
 */
class NursingTemplateDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/NursingTemplate.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function all($pdo=NULL){
		$templates = [];
		try {
			$pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM nursing_template";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$templates[] = $this->get($row['id'], $pdo);
			}
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
		return $templates;
	}
	function get($id, $pdo=NULL){
		try {
			$pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM nursing_template WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				return (new NursingTemplate($row['id']))->setTitle($row['title'])->setContent(htmlentities($row['content']));
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return NULL;
		}
	}
}