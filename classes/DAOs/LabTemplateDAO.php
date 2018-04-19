<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LabTemplateDAO
 *
 * @author pauldic
 */
class LabTemplateDAO
{
	
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabTemplate.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabTemplateData.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabTemplateDataDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function getLabTemplate($tid, $pdo = null)
	{
		$temp = new LabTemplate();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM lab_template WHERE id=" . $tid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$temp->setId($row['id']);
				$temp->setLabel($row['label']);
				$temp->setData((new LabTemplateDataDAO())->getLabTemplateData($row['id'], false, $pdo));
			} else {
				$temp = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$temp = null;
		}
		return $temp;
	}
	
	function getLabTemplates($getFull = false, $pdo = null)
	{
		$temps = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM lab_template";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$temp = new LabTemplate();
				$temp->setId($row['id']);
				$temp->setLabel($row['label']);
				$temp->setData((new LabTemplateDataDAO())->getLabTemplateData($row['id'], FALSE, $pdo));
				$temps[] = $temp;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$temps = array();
		}
		return $temps;
	}
		
	function findLabTemplates($search, $pdo = null)
	{
		$temps = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$query = quote_esc_str('%'.$search.'%');
			$sql = "SELECT * FROM lab_template WHERE label LIKE $query";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$temp = new LabTemplate();
				$temp->setId($row['id']);
				$temp->setLabel($row['label']);
				$temp->setData((new LabTemplateDataDAO())->getLabTemplateData($row['id'], FALSE, $pdo));
				$temps[] = $temp;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$temps = array();
		}
		return $temps;
	}
	
}
