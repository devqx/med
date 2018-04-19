<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/8/16
 * Time: 6:18 PM
 */
class SuperGenericDAO
{

	private $conn = null;

	function __construct() {
		if(!isset($_SESSION)){@session_start();}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/SuperGeneric.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/DrugSuperGenericDataDAO.php';
			$this->conn=new MyDBConnector();
		}catch(PDOException $e) {
			exit( 'ERROR: ' . $e->getMessage() );
		}
	}


	function addSuperGeneric($sup_gen, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO drug_super_generic SET name = '" . $sup_gen->getName() . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$sup_gen->setId($pdo->lastInsertId());
			} else {
				$sup_gen = null;
			}
			$stmt = null;
			return $sup_gen;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}



	function getAll($pdo=null){
		try{
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_super_generic";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO:: CURSOR_SCROLL));
			$stmt->execute();
			$cats = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$cats[] = $this->get($row['id'], $pdo);
			}
			return $cats;
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}

	function get($id, $pdo=null){
		if(is_null($id)){return null;}
		try{
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_super_generic WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO:: CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				return (new SuperGeneric($row['id']))->setName($row['name'])
                    ->setData((new DrugSuperGenericDataDAO())->forSuperGeneric($row['id'], $pdo));
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
}