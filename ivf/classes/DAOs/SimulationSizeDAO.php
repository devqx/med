<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/22/16
 * Time: 1:36 PM
 */
class SimulationSizeDAO
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
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/SimulationSize.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $pdo=null){
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM simulation_size WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new SimulationSize($row['id']))->setName($row['name']);
			}
			return null;
		} catch (PDOException $exception){
			errorLog($exception);
			return null;
		}
	}

	function all($sortAsc=false, $pdo=null){
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM simulation_size";
			$sql .= $sortAsc ? ' ORDER BY id ASC': ' ORDER BY id DESC';
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = (new SimulationSize($row['id']))->setName($row['name']);
			}
			return $data;
		} catch (PDOException $exception){
			errorLog($exception);
			return [];
		}
	}
}