<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/3/15
 * Time: 4:28 PM
 */
class ServiceCenterDAO
{
	private $conn = null;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/CostCenter.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Department.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/CostCenterDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function add($serviceCenter, $pdo = null)
	{
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$department_id = $serviceCenter->getDepartment()->getId();
			$cost_centre_id = $serviceCenter->getCostCentre()->getId();
			$type = $serviceCenter->getType();
			$name = escape($serviceCenter->getName());
			$erp_location_id = $serviceCenter->getErpLocation() ? $serviceCenter->getErpLocation() : 'NULL';
			$sql = "INSERT INTO service_centre (erp_location_id, department_id, cost_centre_id, `type`, `name`) VALUES ($erp_location_id,$department_id, $cost_centre_id, '$type', '$name')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() > 0) {
				$serviceCenter->setId($pdo->lastInsertId());
				return $serviceCenter;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function find($filter, $type, $pdo = null)
	{
		$centers = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM service_centre WHERE `name` LIKE '%$filter%' AND type='$type' ORDER BY `name`";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$center = $this->get($row["id"], $pdo);
				$centers[] = $center;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$centers = null;
		}
		return $centers;
	}
	
	function getOrCreate($center, $pdo=null){
		try{
			$pdo = $pdo == null ? (new MyDBConnector)->getPDO() : $pdo;
			$scent = $this->find($center->getName(), $center->getType(), $pdo)[0];
			if(!$scent == null){
				return $scent;
			}else{
				return $this->add($center, $pdo);
			}
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function get($id, $pdo = null)
	{
		if ($id === null || is_blank($id)) {
			return null;
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM service_centre WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$serviceCenter = new ServiceCenter($row['id']);
				$serviceCenter->setCostCentre((new CostCenterDAO())->get($row['cost_centre_id'], $pdo));
				$serviceCenter->setDepartment((new DepartmentDAO())->get($row['department_id'], $pdo));
				$serviceCenter->setName($row['name']);
				$serviceCenter->setType($row['type']);
				return $serviceCenter;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function all($type = null, $lastItemId = null, $pdo = null)
	{
		$pageSize = 50;
		$centres = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if ($type !== null && $lastItemId == null) {
				$sql = "SELECT * FROM service_centre WHERE type ='$type' ORDER BY id DESC";
			} else if ($lastItemId !== null && $type !== null) {
				$sql = "SELECT * FROM service_centre WHERE type ='$type' AND id BETWEEN " . ($lastItemId + 1) . " AND " . ($lastItemId + $pageSize) . " ORDER BY `name` LIMIT $pageSize";
			} else {
				$sql = "SELECT * FROM service_centre ORDER BY `name` ";
			}
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$centres[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
		return $centres;
	}
	
	//TODO: update method
}