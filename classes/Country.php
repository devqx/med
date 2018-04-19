<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/15/16
 * Time: 9:44 AM
 */
class Country
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
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo = null)
	{
		if (is_null($id))
			return null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM countries WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$row['dialing_code'] = !is_null($row['dialing_code']) ? '+' . $row['dialing_code'] : null;
				return (object)$row;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function all($pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM countries";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$data = [];
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$row['dialing_code'] = !is_null($row['dialing_code']) ? '+' . $row['dialing_code'] : null;
				$data[] = (object)$row;
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
}