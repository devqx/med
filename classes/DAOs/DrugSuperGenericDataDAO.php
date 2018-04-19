<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/18/16
 * Time: 9:51 AM
 */
class DrugSuperGenericDataDAO
{
	private $conn = null;

	function __construct() {
		if(!isset($_SESSION)){@session_start();}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DrugSuperGenericData.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/SuperGenericDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
			$this->conn=new MyDBConnector();
		}catch(PDOException $e) {
			exit( 'ERROR: ' . $e->getMessage() );
		}
	}


	function get($id, $pdo=null){
		if(is_null($id)){return null;}
		try{
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_super_generic_data WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO:: CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                return (new DrugSuperGenericData($row['id']))
                    ->setDrugGeneric((new DrugGenericDAO())->getGeneric($row['drug_generic_id'], fALSE, $pdo));
                //->setSuperGeneric((new SuperGenericDAO())->get($row['super_generic_id'], $pdo));
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function findWithGeneric($genericId, $superGenericId, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_super_generic_data WHERE drug_generic_id=$genericId AND super_generic_id=$superGenericId";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO:: CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new DrugSuperGenericData($row['id']))->setDrugGeneric((new DrugGenericDAO())->getGeneric($row['drug_generic_id'], FALSE, $pdo));//->setSuperGeneric((new SuperGenericDAO())->get($row['super_generic_id'], $pdo));
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function forSuperGeneric($id, $pdo=null){
		$data = [];
		try{
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_super_generic_data WHERE super_generic_id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO:: CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo)->getDrugGeneric();
			}
			return $data;
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}

	
	function addSuperGenericData($sup_data, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO drug_super_generic_data SET super_generic_id = '" . $sup_data->getDrugGeneric() . "', drug_generic_id= '". $sup_data->setSuperGeneric() ."' ";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$sup_data->setId($pdo->lastInsertId());
			} else {
				$sup_data = null;
			}
			$stmt = null;
			return $sup_data;
		} catch (PDOException $e) {
			 errorLog($e);
			return null;
		}
	}

}