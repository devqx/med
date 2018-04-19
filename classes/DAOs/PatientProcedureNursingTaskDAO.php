<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/18/16
 * Time: 1:57 PM
 */
class PatientProcedureNursingTaskDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientProcedureNursingTask.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ResourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			if (!isset($_SESSION)) session_start();
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function addTask($service, $pdo = null)
	{
		// $service = new PatientProcedureNursingTask();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO patient_procedure_nursing_task (patient_procedure_id, service_id, create_uid, service_centre_id) VALUES (" . $service->getPatientProcedure()->getId() . ", " . $service->getTask()->getId() . ", " . $_SESSION['staffID'] . ", ". $service->getServiceCentre()->getId() .")";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() > 0) {
				$service->setId($pdo->lastInsertId());
			} else {
				$service = null;
			}
			return $service;
		} catch (PDOException $e) {
			return null;
		}
	}


	function getProcedureTasks($procedure, $pdo = null)
	{
		$procedures = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_procedure_nursing_task WHERE patient_procedure_id = " . $procedure->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$procedures[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$procedures = [];
		}
		return $procedures;
	}

	function get($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_procedure_nursing_task WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$procedure = (new PatientProcedureNursingTask($row['id']))->setTask((new NursingServiceDAO())->get($row['service_id'], $pdo))->setCreator((new StaffDirectoryDAO())->getStaff($row['create_uid'], FALSE, $pdo))->setWhen($row['date_']);
				return $procedure;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}