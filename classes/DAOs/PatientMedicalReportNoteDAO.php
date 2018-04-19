<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/5/16
 * Time: 7:20 PM
 */
class PatientMedicalReportNoteDAO
{
	private $conn = null;

	function __construct() {
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PatientMedicalReport.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PatientMedicalReportNote.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/ReferralDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/MedicalExamDAO.php';
			$this->conn=new MyDBConnector();
		}catch(PDOException $e) {
			exit( 'ERROR: ' . $e->getMessage() );
		}
	}

	function get($id, $pdo=null){
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_medical_report_note WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$creator = (new StaffDirectoryDAO())->getStaff($row['create_uid'], FALSE, $pdo);
				$patientReport = (new PatientMedicalReportDAO())->get($row['patient_medical_report_id'], $pdo);
				return (new PatientMedicalReportNote($row['id']))
					->setCreateUser($creator)
					->setNote($row['note'])
					->setPatientMedicalReport($patientReport)
					->setCreateDate($row['create_date']);
			}
			return NULL;
		}
		catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

	function forReport($report_id, $pdo=null){
		$data = [];
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_medical_report_note WHERE patient_medical_report_id=$report_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$creator = (new StaffDirectoryDAO())->getStaff($row['create_uid'], FALSE, $pdo);
				$patientReport = null; //saves the memory and cyclic redundancy error
				//(new PatientMedicalReportDAO())->get($row['patient_medical_report_id'], $pdo);
				$data[] = (new PatientMedicalReportNote($row['id']))
					->setCreateUser($creator)
					->setNote($row['note'])
					->setPatientMedicalReport($patientReport)
					->setCreateDate($row['create_date']);
			}
			return $data;
		}
		catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}
}