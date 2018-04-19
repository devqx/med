<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ResourceDAO
 *
 * @author pauldic
 */
class ResourceDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Resource.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function addResource($res, $pdo)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO resource (name, type) VALUES ('" . $res->getName() . "', '" . $res->getType() . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() > 0) {
				$res->setId($pdo->lastInsertId());
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$res = null;
		}
		return $res;
	}
	
	function getResource($rid, $pdo = null)
	{
		if (is_null($rid) || is_blank($rid)) {
			return null;
		}
		$res = new Resource();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM resource WHERE id=" . $rid;
			//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new Resource($row['id']))->setName($row['name'])->setType($row['type'])->setAeTitle($row['ae_title'])->setModality($row['modality'])->setStationName($row['station_name']);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$res = null;
			errorLog($e);
		}
		return $res;
	}
	
	
	function getResources($pdo = null)
	{
		$ress = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM resource";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ress[] = $this->getResource($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$ress = array();
		}
		return $ress;
	}
	
	function getResourcesByType($typeArray, $pdo = null)
	{
		$ress = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM resource WHERE type IN ('" . implode("','", escape($typeArray)) . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ress[] = $this->getResource($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ress = array();
		}
		return $ress;
	}
	
}
