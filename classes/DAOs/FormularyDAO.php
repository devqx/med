<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/1/16
 * Time: 2:50 PM
 */
class FormularyDAO
{
	private $conn = null;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Formulary.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/FormularyData.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/FormularyDataDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo = null)
	{
		if (is_blank($id))
			return null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_formulary WHERE id=" . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data = (new FormularyDataDAO())->getForFormulary($row['id'], $pdo);
				return (new Formulary($row['id']))->setName($row['name'])->setData($data);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function all($pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_formulary";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
}