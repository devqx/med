<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/21/16
 * Time: 9:51 AM
 */
class AptClinicDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AptClinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $pdo=null){
		if(is_null($id))
			return null;
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM appointment_clinic WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				return (new AptClinic($row['id']))->setName($row['name'])->setALimit($row['a_limit'])->setQueueType($row['queue_type']);
			}
			return NULL;
		}catch (PDOException $e){
			errorLog($e);
			return NULL;
		}
	}
	function findByName($name, $pdo=null){
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM appointment_clinic WHERE `name` LIKE '%$name%' LIMIT 1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				return (new AptClinic($row['id']))->setName($row['name'])->setALimit($row['a_limit'])->setQueueType($row['queue_type']);
			}
			return NULL;
		}catch (PDOException $e){
			errorLog($e);
			return NULL;
		}
	}

	function all($pdo=NULL){
		$data = [];
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM appointment_clinic";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$data[] = $this->get($row['id'], $pdo);
			}
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
		return $data;
	}
	function names($pdo=NULL){
		$data = [];
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT `name` FROM appointment_clinic";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$data[] = $row['name'];
			}
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
		return $data;
	}
}