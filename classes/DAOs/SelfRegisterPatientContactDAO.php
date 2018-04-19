<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/1/16
 * Time: 5:15 PM
 */
class SelfRegisterPatientContactDAO
{
	private $conn = null;

	function __construct()
	{
		try{
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/SelfRegisteredPatientContact.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Nation.php';
	    $this->conn=new MyDBConnector();
		}catch (PDOException $e){
			exit('ERROR: '. $e->getMessage());
		}
	}

	function getSelfRegPatientContact($fid, $pdo=null){
		$data = [];
		try{
			$pdo=$pdo==NULL ? $this->conn->getPDO() : $pdo;
			$sql =  "SELECT * FROM fake_contact WHERE fake_patient_id= $fid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$data[] = $this->get($row['id'], $pdo);
			}
	return $data;
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}


	function get($id, $pdo=null){
		$data = [];
		try{
			$pdo=$pdo==null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM fake_contact WHERE id=".$id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				return (new SelfRegisteredPatientContact($row['id']))->setPatient($row['fake_patient_id'])->setPhone($row['phone'])->setNation((new CountryDAO())->getCountry($row['nation_id'], $pdo))->setType($row['type'])->setPrimary((bool)$row['primary']);
			}
		}	catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}

//
//	function get_($id, $pdo=NULL){
//		$data = [];
//		try {
//			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
//			$sql = "SELECT * FROM contact WHERE id=$id";
//			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
//			$stmt->execute();
//			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
//				return (new Contact($row['id']))->setPatient(null)->setPhone($row['phone'])->setCountry( (new Country())->get($row['country_id'], $pdo) )->setType($row['type'])->setPrimary((bool)$row['primary']);
//			}
//			return $data;
//		}catch (PDOException $e){
//			errorLog($e);
//			return [];
//		}
//
//}
}