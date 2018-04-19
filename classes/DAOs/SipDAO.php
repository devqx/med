<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/17/16
 * Time: 9:29 AM
 */
class SipDAO
{

private $conn;
	/**
	 * SipDAO constructor.
	 */
	public function __construct()
	{

		try{
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.sip.php';
			$this->conn = new MyDBConnector();
		}catch (PDOException $e){
			exit("ERROR: ". $e->getMessage());
		}
	}


	function addSipDomain($domain , $pdo=null){
		try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO sip_domain SET `name` = '". escape($domain->getName()) ."' ";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() > 0){
				$domain->setId($pdo->lastInsertId());
			}else{
				$domain = NULL;
			}
			$stmt = null;
		}catch (PDOException $e){
			errorLog($e);
			$domain = NULL;

		}
		return $domain;
	}

	function getOne($pdo=null){
		try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT name FROM sip_domain WHERE id=1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				return (new class_sip($row['id']))->setName($row['name']);
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}



}