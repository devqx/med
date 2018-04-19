<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/1/16
 * Time: 2:41 PM
 */
class FormularyDataDAO
{
	private $conn = null;
	
	function __construct() {
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Formulary.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/FormularyData.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo=null)
	{
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_formulary_data WHERE id=" . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new FormularyData($row['id']))->setGeneric( (new DrugGenericDAO())->getGeneric($row['generic_id'], FALSE, $pdo) );
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return NULL;
		}
	}
	function getForFormulary($formulary_id, $pdo=null)
	{
		$data = [];
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_formulary_data WHERE drug_formulary_id=" . $formulary_id;
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