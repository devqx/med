<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/25/17
 * Time: 3:32 PM
 */
class ClinicalTaskComboDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTaskCombo.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskComboDataDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo = null)
	{
		if (is_null($id)) {
			return null;
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM clinical_task_combo WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data = (new ClinicalTaskComboDataDAO())->_for($row['id'], $pdo);
				return (new ClinicalTaskCombo($row['id']))->setName($row['name'])->setData($data);
			}
			return null;
		} catch (PDOException $exception) {
			errorLog($exception);
			return null;
		}
	}
	
	function all($pdo=null){
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM clinical_task_combo";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $exception) {
			errorLog($exception);
			return [];
		}
	}
}