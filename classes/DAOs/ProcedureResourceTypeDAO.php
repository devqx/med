<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/8/17
 * Time: 1:05 PM
 */
class ProcedureResourceTypeDAO
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
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ProcedureResourceType.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo=null){
		if(is_null($id)){
			return null;
		}
		try {
			$sql = "SELECT * FROM procedure_resource_type WHERE id=$id";
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				// return (object)$row;
				return (new ProcedureResourceType($row['id']))->setName($row['name']);
			}
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function all($pdo=null){
		try {
			$sql = "SELECT * FROM procedure_resource_type";
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$data = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}
}