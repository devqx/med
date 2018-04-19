<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/7/16
 * Time: 1:22 PM
 */
class ProcedureActionListDAO
{
	private $conn = null;

	function __construct() {
		if(!isset($_SESSION)){@session_start();}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/BillSourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/ProcedureActionList.php';
			$this->conn=new MyDBConnector();
		}catch(PDOException $e) {
			exit( 'ERROR: ' . $e->getMessage() );
		}
	}

	function get($id, $pdo=NULL){
		try {
			$pdo = $pdo == NULL?$this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM procedure_action_list WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$enteredBy = (new StaffDirectoryDAO())->getStaff($row['entered_by'], FALSE, $pdo);
				return (new ProcedureActionList($row['id']))->setPatientProcedure( new PatientProcedure($row['patient_procedure_id']) )->setDescription($row['note'])->setTimeEntered($row['time_entered'])->setEnteredBy( $enteredBy )->setDone((bool)$row['done']);
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

	function forPatProcedure($id, $pdo=NULL){
		$procedures = [];
		try {
			$pdo = $pdo == NULL?$this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM procedure_action_list WHERE patient_procedure_id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$procedures[] = $this->get($row['id'], $pdo);
			}
		}catch (PDOException $e){
			errorLog($e);
			$procedures = [];
		}
		return $procedures;
	}
}