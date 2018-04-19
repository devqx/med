<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/20/16
 * Time: 1:03 PM
 */
class ContactDAO
{
	private $conn = null;
	
	function __construct() {
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/Contact.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/Country.php';
			$this->conn=new MyDBConnector();
		}catch(PDOException $e) {
			exit( 'ERROR: ' . $e->getMessage() );
		}
	}
	
	function forPatient($pid, $relation=null, $pdo=NULL){
		$data = [];
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM contact WHERE patient_id=$pid";
			$sql .= $relation != null ? " AND relation='{$relation}'" : "";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}
	
	function clearPatient($pid, $pdo=NULL){
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "DELETE FROM contact WHERE patient_id=$pid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() >= 0){
				return true;
			}
			return false;
		}catch (PDOException $e){
			errorLog($e);
			return false;
		}
	}
	
	function get($id, $pdo=NULL){
		$data = [];
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT id, patient_id, country_id, MASK(phone) AS phone_num, type, `primary`, relation FROM contact WHERE id=$id";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new Contact($row['id']))->setPatient(null)->setPhone($row['phone_num'])->setCountry( (new Country())->get($row['country_id'], $pdo) )->setType($row['type'])->setPrimary((bool)$row['primary']);
			}
			return $data;
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}
}