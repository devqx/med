<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/16/15
 * Time: 10:45 AM
 */
class HistoryDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/HistoryTemplate.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/History.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/HistoryTemplateDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function add($history, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO history (template_id) VALUES (" . $history->getTemplate()->getId() . ")";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$history->setId($pdo->lastInsertId());
				return $history;
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
			$sql = "SELECT * FROM history WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$temp = new History($row['id']);
				$temp->setTemplate((new HistoryTemplateDAO())->get($row['template_id'], $pdo));
				return $temp;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function all($pdo = null)
	{
		$temps = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM history";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$temp = new History($row['id']);
				$temp->setTemplate((new HistoryTemplateDAO())->get($row['template_id'], $pdo));

				$temps[] = $temp;
			}
			return $temps;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}

	function byTemplate($templateId, $pdo = null)
	{
		$temps = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM history WHERE template_id = $templateId";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$temp = new History($row['id']);
				$temp->setTemplate((new HistoryTemplateDAO())->get($row['template_id'], $pdo));

				$temps[] = $temp;
			}
			return $temps;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
}