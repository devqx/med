<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/27/17
 * Time: 9:24 AM
 */
class PatientVitalPreferenceDAO
{
	private $conn = null;
	
	function __construct() {
		if(!isset($_SESSION)){@session_start();}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PatientVitalPreference.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';
			$this->conn=new MyDBConnector();
		}catch(PDOException $e) {
			exit( 'ERROR: ' . $e->getMessage() );
		}
	}
	
	function get($id, $pdo=null)
	{
		if (is_null($id)){return null;}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_vital_preference WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
				return (new PatientVitalPreference($row['id']))->setPatient($patient)->setType($row['type']);
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function forPatient($patientId, $pdo=null)
	{
		if (is_null($patientId)){return null;}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_vital_preference WHERE patient_id=$patientId";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$data = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = (new PatientVitalPreference($row['id'], $row['type']))->setPatient(null);
			}
			return $data;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function forPatientType($patientId, $type, $pdo=null)
	{
		if (is_null($patientId)){return null;}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$type = quote_esc_str($type);
			$sql = "SELECT * FROM patient_vital_preference WHERE patient_id=$patientId AND type=$type";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new PatientVitalPreference($row['id'], $row['type']))->setPatient(new PatientDemograph($patientId));
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	
}