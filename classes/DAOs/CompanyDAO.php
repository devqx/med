<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/27/16
 * Time: 1:07 PM
 */
class CompanyDAO
{
	private $conn = null;

	function __construct() {
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/Company.php';
			$this->conn=new MyDBConnector();
		}catch(PDOException $e) {
			exit( 'ERROR: ' . $e->getMessage() );
		}
	}


	function all($pdo=NULL){
		$data = [];
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM company";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$company = (new Company($row['id']))->setName($row['name']);

				$data[] = $company;
			}
			return $data;
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}

	function get($id, $pdo=NULL){
		if( is_null($id))return NULL;
		try {
			$pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM company WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new Company($row['id']))->setName($row['name']);
			}
			return NULL;
		}catch (PDOException $e){
			errorLog($e);
			return NULL;
		}
	}

	function add($company, $pdo=null){
		try {
			$pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
			$name = escape($company->getName());
			$sql = "INSERT INTO company (`name`) VALUES ('$name')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()==1){
				$company->setId($pdo->lastInsertId());
				return $company;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

	function update($company, $pdo=null){
		try {
			$pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
			$name = escape($company->getName());
			$sql = "UPDATE company SET `name`='$name' WHERE id = {$company->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()>=1){
				return $company;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}


}