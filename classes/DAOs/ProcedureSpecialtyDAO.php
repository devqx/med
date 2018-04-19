<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/18/17
 * Time: 11:05 AM
 */
class ProcedureSpecialtyDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ProcedureSpecialty.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo = null)
	{
		if(is_null($id) || is_blank($id)){
			return null;
		}
		try {
			$pdo = $pdo === null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM procedure_specialty WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new ProcedureSpecialty($row['id']))->setName($row['name']);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function all($pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo === null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM procedure_specialty";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
}