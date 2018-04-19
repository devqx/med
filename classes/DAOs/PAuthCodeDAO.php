<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/17/16
 * Time: 1:18 PM
 */
class PAuthCodeDAO
{
	private $conn = null;
	
	function __construct() {
		if(!isset($_SESSION)){@session_start();}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PAuthCode.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PAuthCodeNote.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/NotificationOptions.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PAuthCodeNoteDAO.php';
			$this->conn=new MyDBConnector();
		}catch(PDOException $e) {
			exit( 'ERROR: ' . $e->getMessage() );
		}
	}
	
	function get($id, $pdo=null)
	{
		if (is_null($id)){return null;}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM authorization_code WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$notes = (new PAuthCodeNoteDAO())->forAuth($row['id'], $pdo);
				$patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
				$creator = (new StaffDirectoryDAO())->getStaff($row['creator_id'], FALSE, $pdo);
				$requestMethod = (new NotificationOptions())->get($row['channel_id'], $pdo);
				return (new PAuthCode($row['id']))->setPatient($patient)->setStatus($row['status'])->setCreator($creator)->setCreateDate($row['create_date'])->setReceiveDate($row['receive_date'])->setCode($row['code'])->setChannel( $requestMethod )->setNotes($notes);
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function all($page=0, $pageSize=10, $patientId=null, $pdo=null)
	{
		$data = new ArrayObject();
		$total = 0;
		$filter = !is_null($patientId) ? " AND patient_id=$patientId":'';
		$sql = "SELECT * FROM authorization_code WHERE `status` <> 'expired'{$filter} ORDER BY id DESC";
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e){
			error_log("ERROR: Failed to return total number of records");
		}
		
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
		}catch (PDOException $e){
			errorLog($e);
			$data = new ArrayObject();
		}
		$results = (object)null;
		$results->data = $data;
		$results->total = $total;
		$results->page = $page;
		return $results;
	}
	
	function forPatient($patientId, $pdo=null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM authorization_code WHERE patient_id=$patientId";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
}