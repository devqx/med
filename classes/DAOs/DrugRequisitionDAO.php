<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/26/16
 * Time: 4:52 PM
 */
class DrugRequisitionDAO
{
	private $conn = NULL;

	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DrugRequisition.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugRequisitionLineDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $pdo=null){
		if(is_blank($id)) return null;
		try {
			$sql = "SELECT *, (SELECT COUNT(*) FROM drug_requisition_line WHERE requisition_id=$id) AS num_items FROM drug_requisition WHERE id=$id";
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$createUser = (new StaffDirectoryDAO())->getStaff($row['create_user_id'], FALSE, $pdo);
				$lastEditUser = (new StaffDirectoryDAO())->getStaff($row['last_action_user'], FALSE, $pdo);
				$items = (new DrugRequisitionLineDAO())->getForRequisition($row['id'], $pdo);
				return (new DrugRequisition($row['id']))->setCreateDate($row['create_date'])->setCreateUser($createUser)->setStatus($row['status'])->setLastActionUser($lastEditUser)->setItemsCount($row['num_items'])->setItems($items);
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function all($pdo=null){
		try {
			$data = [];
			$sql = "SELECT * FROM drug_requisition";
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}
	function find($search, $pdo=null){
		try {
			$data = [];
			$sql = "SELECT * FROM drug_requisition WHERE DATE(create_date) BETWEEN DATE('$search[0]') AND DATE('$search[1]')";
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
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