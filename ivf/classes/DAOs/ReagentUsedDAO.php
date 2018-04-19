<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/17/16
 * Time: 12:52 PM
 */
class ReagentUsedDAO
{
	private $conn = null;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/GeneticRequest.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticLabDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticSpecimenDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticLabResultDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/QualityControlDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/ReagentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/ReagentUsed.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo=null){
		$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
		try {
			$sql = "SELECT * FROM genetic_request_reagent WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$request = null;
				return (new ReagentUsed($row['id']))->setRequest($request)->setReagent( (new ReagentDAO())->get($row['reagent_id'], $pdo) )->setLotNumber($row['lot_number'])->setDate($row['date_used'])->setUser( (new StaffDirectoryDAO())->getStaff($row['user_id'], FALSE, $pdo) );
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function getForRequest($rid, $pdo=null){
		$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
		try {
			$sql = "SELECT * FROM genetic_request_reagent WHERE request_id=$rid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$data = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}
}