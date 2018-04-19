<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DrugManufacturerDAO
 *
 * @author pauldic
 */
class DrugManufacturerDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DrugManufacturer.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function getManufacturers($pdo = null)
	{
		$mans = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_manufacturers ORDER BY `name` ASC";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$man = new DrugManufacturer();
				$man->setId($row["id"]);
				$man->setName($row["name"]);
				$mans[] = $man;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$mans = null;
		}
		return $mans;
	}
	
	function getManufacturer($id, $pdo = null)
	{
		$man = new DrugManufacturer();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_manufacturers WHERE id=" . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$man->setId($row["id"]);
				$man->setName($row["name"]);
			} else {
				$man = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$man = null;
		}
		return $man;
	}
	
	function findManufacturer($name, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_manufacturers WHERE `name`=" . quote_esc_str($name);
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return $this->getManufacturer($row['id'], $pdo);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	
	function addManufacturer($d_man, $pdo = null)
	{
		$manufacturer = new DrugManufacturer();
		$name = quote_esc_str($d_man->getName());
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//$pdo->beginTransaction();
			$sql = "INSERT INTO drug_manufacturers SET `name` = $name";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				//$pdo->commit();
				$manufacturer->setId($pdo->lastInsertId());
			} else {
				//$pdo->rollBack();
				$manufacturer = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$cat = null;
		}
		return $manufacturer;
	}
	
	function getOrCreate($name, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$return = $this->findManufacturer($name, $pdo);
			
			if ($return != null) {
				return $return;
			} else {
				$manufacturer = new DrugManufacturer();
				$manufacturer->setName($name);
				
				return $this->addManufacturer($manufacturer, $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function updateManufacturer($new_data, $id, $pdo = null)
	{
		//TODO: not yet implemented
	}
	
	function deleteManufacturer($id, $pdo = null)
	{
		//TODO: not yet implemented
	}
}
