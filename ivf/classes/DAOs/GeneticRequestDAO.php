<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/16
 * Time: 10:48 AM
 */
class GeneticRequestDAO
{
	private $conn = null;

	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/GeneticRequest.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticLabDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticSpecimenDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticLabResultDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/QualityControlDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/ReagentUsedDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM genetic_lab_request WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new GeneticRequest($row['id']))
					->setRequestCode($row['request_code'])
					->setFemalePatient((new PatientDemographDAO())->getPatient($row['female_patient_id'], FALSE, $pdo))
					->setMalePatient((new PatientDemographDAO())->getPatient($row['male_patient_id'], FALSE, $pdo))
					->setReferral( (new ReferralDAO())->get($row['referral_id'], $pdo) )
					->setRequestDate($row['request_date'])
					->setUser( (new StaffDirectoryDAO())->getStaff($row['user_id'], FALSE, $pdo))
					->setReason($row['reason'])
					->setLab( (new GeneticLabDAO())->get($row['genetic_lab_id'], $pdo) )
					->setSpecimenType( (new GeneticSpecimenDAO())->get($row['genetic_specimen_id'], $pdo) )
					->setSpecimenReceiveDate($row['specimen_received_on'])
					->setResult((new GeneticLabResultDAO())->getForRequest($row['id'], $pdo))
					->setQualityControls( (new QualityControlDAO())->forRequest($row['id'], $pdo) )
					->setReagents( (new ReagentUsedDAO())->getForRequest($row['id'], $pdo) )
					->setStatus($row['status']);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function all($status=null, $pdo = null)
	{
		$str = !is_blank($status) ? "WHERE `status` = '$status'": "";
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM genetic_lab_request {$str}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return $data;
		}
	}
	
	function search($search, $pdo = null)
	{
		$str = !is_blank($search) ? "WHERE `request_code` LIKE '%$search%'": "";
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM genetic_lab_request {$str}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return $data;
		}
	}
}