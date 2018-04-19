<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BlockDAO
 *
 * @author pauldic
 */
class BlockDAO
{
	private $conn = null;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Block.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function addBlock($b, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO block SET name = '" . $b->getName() . "', description='" . $b->getDescription() . "', hospital_id = '" . $b->getHospital()->getId() . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() > 0) {
				$b->setId($pdo->lastInsertId());
			} else {
				$b = null;
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			$b = $stmt = null;
		} catch (Exception $e) {
			$b = $stmt = null;
		}
		
		return $b;
	}
	
	function getBlock($id, $getFull = false, $pdo = null)
	{
		$block = new Block();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM block WHERE id = " . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$block->setId($row["id"]);
				$block->setName($row["name"]);
				$block->setDescription($row["description"]);
				if ($getFull) {
					$hosp = (new ClinicDAO())->getClinic($row['hospital_id'], false, $pdo);
				} else {
					$hosp = new Clinic();
					$hosp->setId($row['hospital_id']);
				}
				$block->setHospital($hosp);
			} else {
				$block = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$block = $stmt = null;
		}
		return $block;
	}
	
	function getBlocks($getFull = false, $pdo = null)
	{
		$blocks = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM block ORDER BY name";
			//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$block = new Block();
				$block->setId($row["id"]);
				$block->setName($row["name"]);
				$block->setDescription($row["description"]);
				if ($getFull) {
					$hosp = (new ClinicDAO())->getClinic($row['hospital_id'], false, $pdo);
				} else {
					$hosp = new Clinic();
					$hosp->setId($row['hospital_id']);
				}
				$block->setHospital($hosp);
				$blocks[] = $block;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$blocks = [];
		}
		return $blocks;
	}
	
	function getBlocksByHospital($hip, $getFull = false, $pdo = null)
	{
		$block = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM block  WHERE hospital_id = $hip ORDER BY name";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$block = new Block();
				$block->setId($row["id"]);
				$block->setName($row["name"]);
				$block->setDescription($row["description"]);
				if ($getFull) {
					$hosp = (new ClinicDAO())->getClinic($row['hospital_id'], false, $pdo);
				} else {
					$hosp = new Clinic();
					$hosp->setId($row['hospital_id']);
				}
				$block->setHospital($hosp);
				$blocks[] = $block;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$block = [];
		}
		return $block;
	}
	
	function updateBlock($block, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE `block` SET `name` = '" . $block->getName() . "', description='" . $block->getDescription() . "', hospital_id = '" . $block->getHospital()->getId() . "' WHERE id = " . $block->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$status = true;
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$status = false;
		}
		return $status;
	}
	
}
