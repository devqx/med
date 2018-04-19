<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/3/16
 * Time: 3:32 PM
 */
class MedicalExamDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/MedicalExam.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/LabDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/ScanDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/ProcedureDAO.php';
			$this->conn=new MyDBConnector();
		}catch(PDOException $e) {
			exit( 'ERROR: ' . $e->getMessage() );
		}
	}

	function get($id, $pdo=NULL){
		try {
			$pdo = $pdo==NULL?$this->conn->getPDO(): $pdo;
			$sql = "SELECT s.*, c.selling_price FROM medical_exam s LEFT JOIN insurance_items_cost c ON c.item_code=s.billing_code WHERE s.id = $id #AND c.insurance_scheme_id = 1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$labs = [];
				foreach (array_filter(explode(",", $row['labs'])) as $item){
					$labs[] = (new LabDAO())->getLab($item, FALSE, $pdo);
				}

				$imagings = [];
				foreach (array_filter(explode(",", $row['imagings'])) as $item){
					$imagings[] = (new ScanDAO())->getScan($item, $pdo);
				}

				$procedures = [];
				foreach (array_filter(explode(",", $row['procedures'])) as $item) {
					$procedures[] = (new ProcedureDAO())->getProcedure($item, $pdo);
				}
				return (new MedicalExam($row['id']))->setCode($row['billing_code'])->setName($row['name'])->setBasePrice($row['selling_price'])->setProcedures($procedures)->setImagings($imagings)->setLabs($labs);
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

	function getByCode($code, $pdo=NULL){
		try {
			$pdo = $pdo==NULL?$this->conn->getPDO(): $pdo;
			$sql = "SELECT * FROM medical_exam WHERE billing_code = '$code'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				return $this->get($row['id'], $pdo);
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

	function find($search, $pdo=NULL){
		$data = array();
		try {
			$pdo = $pdo==NULL?$this->conn->getPDO(): $pdo;
			$sql = "SELECT * FROM medical_exam WHERE `name` LIKE '%$search%'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$data[] = $this->get($row['id'], $pdo);
			}

		}catch (PDOException $e){
			errorLog($e);
			$data = [];
		}
		return $data;
	}
	function all($pdo=NULL){
		$data = array();
		try {
			$pdo = $pdo==NULL?$this->conn->getPDO(): $pdo;
			$sql = "SELECT * FROM medical_exam";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$data[] = $this->get($row['id'], $pdo);
			}

		}catch (PDOException $e){
			errorLog($e);
			$data = [];
		}
		return $data;
	}
}