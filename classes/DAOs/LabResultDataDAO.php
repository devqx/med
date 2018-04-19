<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LabResultDataDAO
 *
 * @author pauldic
 */
class LabResultDataDAO
{
	
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabResultData.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabResult.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabTemplateData.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabResultDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabTemplateDataDAO.php';
			if (!isset($_SESSION))
				session_start();
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function addLabResultData($data, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$canCommit = true;
			try {
				$pdo->beginTransaction();
			} catch (PDOException $e) {
				$canCommit = false;
			}
			$counter = 0;
			foreach ($data as $datum) {
				$sql = "INSERT INTO lab_result_data (lab_result_id, lab_template_data_id, `value`)  VALUES (" . $data[0]->getLabResult()->getId() . ", " . $datum->getLabTemplateData()->getId() . ", '" . $datum->getValue() . "')";
				//error_log($sql);
				$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt->execute();
				$counter++;
			}
			
			if (count($data) === $counter) {
				if ($canCommit) {
					$pdo->commit();
				}
			} else {
				$pdo->rollBack();
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			if ($pdo != null) {
				$pdo->rollBack();
			}
			error_log("PDO Exception");
			$stmt = null;
			$datum = null;
		}
		return $data;
	}
	
	function getLabResultDatum($lrId, $ltDataId, $getFull = false, $pdo = null)
	{
		$data = new LabResultData();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM lab_result_data WHERE lab_result_id = $lrId AND lab_template_data_id=$ltDataId";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				if ($getFull) {
					$res = (new LabResultDAO())->getLabResult($row['lab_result_id'], false, $pdo);
				} else {
					$res = new LabResult($row['lab_result_id']);
				}
				$data->setLabResult($res);    //Obj
				$data->setLabTemplateData((new LabTemplateDataDAO())->getLabTemplateDatum($row['lab_template_data_id'], $pdo));    //Obj
				$data->setValue($row['value']);
			} else {
				$data = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$data = null;
		}
		return $data;
	}
	
	function getLabResultData($rid, $getFull = false, $pdo = null)
	{
		$datas = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM lab_result_data WHERE lab_result_id=" . $rid;
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data = new LabResultData();
				if ($getFull) {
					$res = (new LabResultDAO())->getLabResult($row['lab_result_id'], false, $pdo);
				} else {
					$res = new LabResult($row['lab_result_id']);
				}
				$data->setLabResult($res);    //Obj
				$data->setLabTemplateData((new LabTemplateDataDAO())->getLabTemplateDatum($row['lab_template_data_id'], false, $pdo));    //Obj
				$data->setValue($row['value']);
				$datas[] = $data;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$datas = array();
		}
		return $datas;
	}
	
}
