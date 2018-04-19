<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/7/15
 * Time: 11:52 AM
 */
class LabComboDataDAO
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
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabCombo.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabComboData.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
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
			$sql = "SELECT * FROM lab_combo_data WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new LabComboData())->setId($row['id'])->setLab((new LabDAO())->getLab($row['lab_id'], false, $pdo));
			}
			return null;
			
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function getComboData($comboId, $pdo = null)
	{
		if (is_null($comboId))
			return [];
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM lab_combo_data WHERE lab_combo_id=$comboId";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = (new LabComboData())->setId($row['id'])->setLab((new LabDAO())->getLab($row['lab_id'], true, $pdo));
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	function add($comboData, $pdo = null)
	{
		$lab_combo_id = $comboData->getLabCombo()->getId();
		$lab_id = $comboData->getLab()->getId();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO lab_combo_data (lab_combo_id, lab_id) VALUES ($lab_combo_id, $lab_id)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				$comboData->setId($pdo->lastInsertId());
				return $comboData;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}