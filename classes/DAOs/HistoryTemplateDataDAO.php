<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/16/15
 * Time: 10:56 AM
 */
class HistoryTemplateDataDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/HistoryTemplateData.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/HistoryTemplateDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function add($tData, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO history_template_data (history_template_id, label, datatype) VALUES (" . $tData->getHistoryTemplate()->getId() . ", '" . $tData->getLabel() . "', '" . $tData->getDataType() . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$tData->setId($pdo->lastInsertId());
				return $tData;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function get($id, $pdo = null)
	{

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM history_template_data WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$tData = new HistoryTemplateData($row['id']);
				$tData->setHistoryTemplate((new HistoryTemplateDAO())->get($row['history_template_id'], $pdo));
				$tData->setLabel($row['label']);
				$tData->setDataType($row['datatype']);

				return $tData;
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
			$sql = "SELECT * FROM history_template_data";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$tDatas = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$tDatas[] = $this->get($row['id'], $pdo);
			}
			return $tDatas;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}

	function byTemplate($tpl_id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM history_template_data WHERE history_template_id = $tpl_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$tDatas = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$tDatas[] = $this->get($row['id'], $pdo);
			}
			return $tDatas;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}


}