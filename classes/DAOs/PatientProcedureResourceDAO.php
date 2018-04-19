<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/11/14
 * Time: 4:13 PM
 */
class PatientProcedureResourceDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientProcedureResource.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ResourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureResourceTypeDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function addResource($resource, $pdo = null)
	{
		//$resource = new PatientProcedureResource();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$resourceType = $resource->getResourceType() ? $resource->getResourceType()->getId() : 'NULL';
			$sql = "INSERT INTO patient_procedure_resource (patient_procedure_id, staff_id, create_uid, resource_type_id) VALUES ('" . $resource->getPatientProcedure()->getId() . "', '" . $resource->getResource()->getId() . "', '" . $resource->getCreator()->getId() . "', $resourceType)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() > 0) {
				$resource->setId($pdo->lastInsertId());
				$resource->setCreateTime(date("Y-m-d H:i:s", time()));
			} else {
				$resource = null;
			}
			return $resource;
		} catch (PDOException $e) {
			return null;
		}
	}
	
	function getProcedureResources($procedure, $getFull = false, $pdo = null)
	{
		$procedures = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_procedure_resource WHERE patient_procedure_id = " . $procedure->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$procedure = new PatientProcedureResource($row['patient_procedure_id']);
				$procedure->setResource((new StaffDirectoryDAO())->getStaff($row['staff_id'], true, $pdo));
				$procedure->setCreator((new StaffDirectoryDAO())->getStaff($row['create_uid'], $getFull, $pdo));
				$procedure->setCreateTime($row['date_']);
				$procedure->setResourceType( (new ProcedureResourceTypeDAO())->get($row['resource_type_id'], $pdo) );
				
				$procedures[] = $procedure;
				//                $procedure->setPatientProcedure( (new PatientProcedureDAO())->getProcedure($pdo) );
			}
		} catch (PDOException $e) {
			$procedures = [];
		}
		return $procedures;
	}
	
	function getResources($getFull = false, $pdo = null)
	{
		$procedures = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_procedure_resource";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$procedure = new PatientProcedureResource($row['patient_procedure_id']);
				$procedure->setResource((new StaffDirectoryDAO())->getStaff($row['staff_id'], $getFull, $pdo));
				$procedure->setCreator((new StaffDirectoryDAO())->getStaff($row['create_uid'], $getFull, $pdo));
				$procedure->setPatientProcedure((new PatientProcedureDAO())->get($row['patient_procedure_id'], $pdo));
				$procedure->setCreateTime($row['date_']);
				$procedure->setResourceType( (new ProcedureResourceTypeDAO())->get($row['resource_type_id'], $pdo) );
				
				$procedures[] = $procedure;
			}
		} catch (PDOException $e) {
			$procedures = [];
		}
		return $procedures;
	}
} 