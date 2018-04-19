<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LabTemplateDataDAO
 *
 * @author pauldic
 */
class LabTemplateDataDAO
{
	
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabTemplate.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabTemplateData.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabTemplateDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabTemplateDataDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabMethodDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function updateLabTemplate($data, $pdo = null)
	{
		try {
			$counter = 0;
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			foreach ($data as $k => $temp) {
				$sql = "UPDATE lab_template_data SET label='" . $temp->getLabel() . "', reference='" . $temp->getReference() . "' WHERE id=" . $temp->getId();
				$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt->execute();
				
				$counter++;
			}
			if (count($data) !== $counter) {
				$pdo->rollBack();
				$data = [null];
			} else {
				$pdo->commit();
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			$pdo->rollBack();
			errorLog($e);
			$stmt = null;
			$data = [null];
		}
		return $data;
	}
	
	function getLabTemplateDatum($did, $getFull = false, $pdo = null)
	{
		$datum = new LabTemplateData();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM lab_template_data WHERE id=" . $did;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				if ($getFull) {
					$temp = (new LabTemplateDAO())->getLabTemplate($row['lab_template_id'], $pdo);
				} else {
					$temp = new LabTemplate($row['lab_template_id']);
				}
				$datum->setId($row['id'])
					->setMethod((new LabMethodDAO())->get($row['lab_method_id'], $pdo))
					->setLabTemplate($temp)
					->setReference($row['reference']);
			} else {
				$datum = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$datum = null;
		}
		return $datum;
	}
	
	function getLabTemplateData($did, $getFull = false, $pdo = null)
	{
		$data = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM lab_template_data WHERE lab_template_id=$did ORDER BY id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$datum = new LabTemplateData();
				if ($getFull) {
					$temp = (new LabTemplateDAO())->getLabTemplate($row['lab_template_id'], $pdo);
				} else {
					$temp = new LabTemplate($row['lab_template_id']);
				}
				$datum
					->setId($row['id'])
					->setMethod((new LabMethodDAO())->get($row['lab_method_id'], $pdo))
					->setLabTemplate($temp)
					->setReference($row['reference']);
				$data[] = $datum;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$data = array();
		}
		return $data;
	}
	
	function getAllLabTemplateData($getFull = false, $pdo = null)
	{
		$data = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM lab_template_data";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				
				$datum = new LabTemplateData();
				if ($getFull) {
					$temp = (new LabTemplateDAO())->getLabTemplate($row['lab_template_id'], $pdo);
				} else {
					$temp = new LabTemplate($row['lab_template_id']);
				}
				$datum->setId($row['id'])
					->setMethod((new LabMethodDAO())->get($row['lab_method_id'], $pdo))
					->setLabTemplate($temp)
					->setReference($row['reference']);
				$data[] = $datum;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$data = array();
		}
		return $data;
	}
	
}
