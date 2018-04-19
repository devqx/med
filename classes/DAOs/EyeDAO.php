<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 7/25/16
 * Time: 9:06 PM
 */
class EyeDAO
{


	private $conn;

	function __construct()
	{

		try{

			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER['DOCUMENT_ROOT'] .  '/classes/EyePart.php';

			$this->conn = new MyDBConnector();

		}catch (PDOException $e){
			errorLog($e);
		}
	}

	function getOne($id, $pdo=NULL){

		if($id === null){
			return NULL;
		}
		try{
			$pdo = $pdo == NULL ? $this->conn->getPDO(): $pdo;
			$sql = "SELECT * FROM eye WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				return (new EyePart($row['id']))->setName($row['name'])->setShape($row['shape'])->setCoords($row['coords']);
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

	function getAll($pdo=null){
		try{
			$pdo = $pdo == NULL ? $this->conn->getPDO(): $pdo;
			$sql = "SELECT * FROM eye";
			$stmt= $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$data = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$data[] = $this->getOne($row['id'], $pdo);
			}
			return $data;
			
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}

}