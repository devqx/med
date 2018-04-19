<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/17/16
 * Time: 4:47 PM
 */
class GeneticLabResultDAO
{
	private $conn = null;

	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/GeneticLab.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/GeneticRequest.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/GeneticLabResult.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo=null){
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM genetic_lab_result WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new GeneticLabResult($row['id']))
					->setRequest( (new GeneticRequest($row['genetic_lab_request_id'])) )
					->setNote($row['note'])
					->setUser( (new StaffDirectoryDAO())->getStaff($row['user_id'], FALSE, $pdo) )
					->setTimeEntered($row['time_entered']);
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function getForRequest($requestId, $pdo=null){
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM genetic_lab_result WHERE genetic_lab_request_id=$requestId";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new GeneticLabResult($row['id']))
					//->setRequest( (new GeneticRequest($row['genetic_lab_request_id'])) )
					->setNote($row['note'])
					->setUser( (new StaffDirectoryDAO())->getStaff($row['user_id'], FALSE, $pdo) )
					->setTimeEntered($row['time_entered']);
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
}