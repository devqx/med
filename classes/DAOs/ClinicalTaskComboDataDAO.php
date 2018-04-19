<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/25/17
 * Time: 5:01 PM
 */
class ClinicalTaskComboDataDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTaskCombo.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTaskComboData.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskComboDAO.php';
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
			$sql = "SELECT * FROM clinical_task_combo_data WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new ClinicalTaskComboData($row['id']))->setType($row['type'])->setDescription($row['description'])->setFrequency($row['frequency'])->setInterval($row['interval'])->setTaskCount($row['task_count']);
			}
			return null;
		} catch (PDOException $exception) {
			errorLog($exception);
			return null;
		}
	}
	
	function _for($id, $pdo=null){
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM clinical_task_combo_data WHERE clinical_task_combo_id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$data = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = (new ClinicalTaskComboData($row['id']))->setType($row['type'])->setDescription($row['description'])->setFrequency($row['frequency'])->setInterval($row['interval'])->setTaskCount($row['task_count']);
			}
			return $data;
		} catch (PDOException $exception) {
			errorLog($exception);
			return [];
		}
	}
}