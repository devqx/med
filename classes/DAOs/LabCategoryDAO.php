<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LabCategoryDAO
 *
 * @author pauldic
 */
class LabCategoryDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabCategory.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function addLabCategory($cat, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO labtests_config_category (`name`) VALUES ('" . $cat->getName() . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$cat->setId($pdo->lastInsertId());
			} else {
				$cat = null;
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$cat = null;
		}
		return $cat;
	}
	
	function getLab($cid, $getFull = false, $pdo = null)
	{
		return $this->getLabCategory($cid, $pdo);
	}
	
	function getLabCategories($getFull = false, $pdo = null)
	{
		$cats = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM labtests_config_category";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$cat = new LabCategory();
				$cat->setId($row['id']);
				$cat->setName($row['name']);
				$cats[] = $cat;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$cats = array();
		}
		return $cats;
	}
	
	function getLabCategory($id, $pdo = null)
	{
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM labtests_config_category WHERE id='$id'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$cat = new LabCategory();
				$cat->setId($row['id']);
				$cat->setName($row['name']);
			} else {
				$cat = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$cat = null;
			$stmt = null;
		}
		return $cat;
	}
	
}
