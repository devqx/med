<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/2/15
 * Time: 1:21 PM
 */
class KinRelationDAO
{
	private $conn = null;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/KinRelation.php';
			
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function all($pdo = null)
	{
		$data = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM kin_relation ORDER BY `name`";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$kin = new KinRelation($row["id"]);
				$kin->setName($row['name']);
				
				$data[] = $kin;
			}
		} catch (PDOException $e) {
			$data = [];
		}
		return $data;
	}
	
	function get($id, $pdo = null)
	{
		if (is_blank($id))
			return null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM kin_relation WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$kin = new KinRelation($row["id"]);
				$kin->setName($row["name"]);
				
				return $kin;
			}
			return null;
		} catch (PDOException $e) {
			return null;
		}
	}
	
	//todo: adding and updating
}