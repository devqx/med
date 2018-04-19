<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/3/16
 * Time: 1:53 PM
 */
class PatientMedicalReportDAO
{
	private $conn = null;

	function __construct() {
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PatientMedicalReport.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/ReferralDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/MedicalExamDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PatientMedicalReportNoteDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PatientLabDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PatientScanDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PatientProcedureDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
			$this->conn=new MyDBConnector();
		}catch(PDOException $e) {
			exit( 'ERROR: ' . $e->getMessage() );
		}
	}

	function get($id, $pdo=NULL){
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT r.* FROM patient_medical_report r WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
				$exam = (new MedicalExamDAO())->get($row['exam_id'], $pdo);
				$approved_by = (new StaffDirectoryDAO())->getStaff($row['approved_by_id'], FALSE, $pdo);
				$canceled_by = (new StaffDirectoryDAO())->getStaff($row['canceled_by_id'], FALSE, $pdo);
				$requested_by = (new StaffDirectoryDAO())->getStaff($row['requested_by_id'], FALSE, $pdo);
				$referral = (new ReferralDAO())->get($row['referral_id'], $pdo);
				$notes = (new PatientMedicalReportNoteDAO())->forReport($row['id'], $pdo);
				$bills = [];
				foreach (array_filter(explode(",", $row['bill_line_id'])) as $b) {
					$bills[] = (new BillDAO())->getBill($b, false, $pdo);
				}
				$labs = [];
				foreach (array_filter(explode(",", $row['labs'])) as $requestId) {
					$labs[] = (new PatientLabDAO())->getLab($requestId, $pdo);
				}
				unset($requestId);

				$imagings = [];
				foreach (array_filter(explode(",", $row['imagings'])) as $requestId) {
					$imagings[] = (new PatientScanDAO())->getScan($requestId, $pdo);
				}
				unset($requestId);
				$procedures = [];
				foreach (array_filter(explode(",", $row['procedures'])) as $requestId) {
					$procedures[] = (new PatientProcedureDAO())->get($requestId, $pdo);
				}
				unset($requestId);
				return (new PatientMedicalReport($row['id']))->setRequestCode($row['requestCode'])->setPatient($patient)->setExam($exam)->setRequestNote($row['request_note'])->setRequestBy($requested_by)->setRequestDate($row['request_date'])->setApproved($row['approved'])->setApprovedBy($approved_by)->setApprovedDate($row['approved_date'])->setDateLastModified($row['date_last_modified'])->setCancelled($row['cancelled'])->setCancelledDate($row['cancel_date'])->setCancelledBy($canceled_by)->setReferral($referral)->setNotes($notes)
					->setLabs($labs)->setImagings($imagings)->setProcedures($procedures)->setBill($row['bill_line_id'] != null ? $bills : NULL);
			}
			return NULL;
		}catch (PDOException $e){
			errorLog($e);
			return NULL;
		}
	}

	function forPatient($pid, $page=0, $pageSize=10, $pdo=NULL){
		$total = 0;
		$sql = "SELECT r.*, (SELECT COUNT(*) FROM patient_medical_report_note WHERE patient_medical_report_id=r.id) AS notes FROM patient_medical_report r WHERE patient_id=$pid";
		//error_log($sql);
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e){
			error_log("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$data = [];
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$patient = null;
				$exam = (new MedicalExamDAO())->get($row['exam_id'], $pdo);
				$approved_by = (new StaffDirectoryDAO())->getStaff($row['approved_by_id'], FALSE, $pdo);
				$canceled_by = (new StaffDirectoryDAO())->getStaff($row['canceled_by_id'], FALSE, $pdo);
				$requested_by = (new StaffDirectoryDAO())->getStaff($row['requested_by_id'], FALSE, $pdo);
				$labs = [];
				foreach (array_filter(explode(",", $row['labs'])) as $requestId) {
					$labs[] = (new PatientLabDAO())->getLab($requestId, $pdo);
				}
				unset($requestId);

				$imagings = [];
				foreach (array_filter(explode(",", $row['imagings'])) as $requestId) {
					$imagings[] = (new PatientScanDAO())->getScan($requestId, $pdo);
				}
				unset($requestId);

				$procedures = [];
				foreach (array_filter(explode(",", $row['procedures'])) as $requestId) {
					$procedures[] = (new PatientProcedureDAO())->get($requestId, $pdo);
				}
				unset($requestId);
				$referral = (new ReferralDAO())->get($row['referral_id'], $pdo);
				$notes = (new PatientMedicalReportNoteDAO())->forReport($row['id'], $pdo);
				$data[] = (new PatientMedicalReport($row['id']))->setRequestCode($row['requestCode'])->setPatient($patient)->setExam($exam)->setRequestNote($row['request_note'])->setRequestBy($requested_by)->setRequestDate($row['request_date'])->setApproved($row['approved'])->setApprovedBy($approved_by)->setApprovedDate($row['approved_date'])->setDateLastModified($row['date_last_modified'])->setCancelled($row['cancelled'])->setCancelledDate($row['cancel_date'])->setCancelledBy($canceled_by)->setReferral($referral)->setNotes($notes)->setLabs($labs)->setImagings($imagings)->setProcedures($procedures)->setNotesCount($row['notes']);
			}
		}catch (PDOException $e){
			errorLog($e);
			$data = [];
		}

		$results = (object)null;
		$results->data = $data;
		$results->total = $total;
		$results->page = $page;
		return $results;
	}

	function all($page=0, $pageSize=10, $status, $pdo=NULL){
		$sql = "SELECT r.*, (SELECT COUNT(*) FROM patient_medical_report_note WHERE patient_medical_report_id=r.id) AS notes FROM patient_medical_report r LEFT JOIN patient_demograph pd ON pd.patient_ID=r.patient_id WHERE pd.active IS TRUE";

		if($status == "open"){
			$sql .= " AND cancelled IS FALSE HAVING notes = 0 ";
		} else if($status == "to_approve"){
			$sql .= " AND approved IS FALSE HAVING notes > 0";
		}
		//... others
		$total = 0;

		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e){
			error_log("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$data = [];
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
				$exam = (new MedicalExamDAO())->get($row['exam_id'], $pdo);
				$approved_by = (new StaffDirectoryDAO())->getStaff($row['approved_by_id'], FALSE, $pdo);
				$canceled_by = (new StaffDirectoryDAO())->getStaff($row['canceled_by_id'], FALSE, $pdo);
				$requested_by = (new StaffDirectoryDAO())->getStaff($row['requested_by_id'], FALSE, $pdo);
				$referral = (new ReferralDAO())->get($row['referral_id'], $pdo);
				$notes = (new PatientMedicalReportNoteDAO())->forReport($row['id'], $pdo);
				$labs = [];
				foreach (array_filter(explode(",", $row['labs'])) as $requestId) {
					$labs[] = (new PatientLabDAO())->getLab($requestId, $pdo);
				}
				unset($requestId);

				$imagings = [];
				foreach (array_filter(explode(",", $row['imagings'])) as $requestId) {
					$imagings[] = (new PatientScanDAO())->getScan($requestId, $pdo);
				}
				unset($requestId);

				$procedures = [];
				foreach (array_filter(explode(",", $row['procedures'])) as $requestId) {
					$procedures[] = (new PatientProcedureDAO())->get($requestId, $pdo);
				}
				unset($requestId);

				$data[] = (new PatientMedicalReport($row['id']))->setRequestCode($row['requestCode'])->setPatient($patient)->setExam($exam)->setRequestNote($row['request_note'])->setRequestBy($requested_by)->setRequestDate($row['request_date'])->setApproved($row['approved'])->setApprovedBy($approved_by)->setApprovedDate($row['approved_date'])->setDateLastModified($row['date_last_modified'])->setCancelled($row['cancelled'])->setCancelledDate($row['cancel_date'])->setCancelledBy($canceled_by)->setReferral($referral)->setNotes($notes)->setLabs($labs)->setImagings($imagings)->setProcedures($procedures)->setNotesCount($row['notes']);
			}

		}catch (PDOException $e){
			errorLog($e);
			$data = [];
		}
		$results = (object)null;
		$results->data = $data;
		$results->total = $total;
		$results->page = $page;
		return $results;
	}

	function find($search, $page=0, $pageSize=10, $pdo=NULL){
		$sql = "SELECT r.*, (SELECT COUNT(*) FROM patient_medical_report_note WHERE patient_medical_report_id=r.id) AS notes FROM patient_medical_report r LEFT JOIN patient_demograph pd ON pd.patient_ID=r.patient_id WHERE pd.active IS TRUE AND (r.requestCode LIKE '%$search%' OR pd.fname LIKE '%$search' OR pd.patient_ID='$search' OR pd.lname LIKE '%$search' OR pd.mname LIKE '%$search')";
		$total = 0;

		if(!is_blank($search)){
			try {
				$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
				$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt->execute();
				$total = $stmt->rowCount();
			} catch (PDOException $e){
				error_log("ERROR: Failed to return total number of records");
			}

			$page = ($page > 0) ? $page : 0;
			$offset = ($page > 0) ? $pageSize * $page : 0;
			$data = [];
			try {
				$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
				$sql .= " LIMIT $offset, $pageSize";
				$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt->execute();
				while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
					$patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$exam = (new MedicalExamDAO())->get($row['exam_id'], $pdo);
					$approved_by = (new StaffDirectoryDAO())->getStaff($row['approved_by_id'], FALSE, $pdo);
					$canceled_by = (new StaffDirectoryDAO())->getStaff($row['canceled_by_id'], FALSE, $pdo);
					$requested_by = (new StaffDirectoryDAO())->getStaff($row['requested_by_id'], FALSE, $pdo);
					$referral = (new ReferralDAO())->get($row['referral_id'], $pdo);
					$notes = (new PatientMedicalReportNoteDAO())->forReport($row['id'], $pdo);
					$labs = [];
					foreach (array_filter(explode(",", $row['labs'])) as $requestId) {
						$labs[] = (new PatientLabDAO())->getLab($requestId, $pdo);
					}
					unset($requestId);

					$imagings = [];
					foreach (array_filter(explode(",", $row['imagings'])) as $requestId) {
						$imagings[] = (new PatientScanDAO())->getScan($requestId, $pdo);
					}
					unset($requestId);

					$procedures = [];
					foreach (array_filter(explode(",", $row['procedures'])) as $requestId) {
						$procedures[] = (new PatientProcedureDAO())->get($requestId, $pdo);
					}
					unset($requestId);

					$data[] = (new PatientMedicalReport($row['id']))->setRequestCode($row['requestCode'])->setPatient($patient)->setExam($exam)->setRequestNote($row['request_note'])->setRequestBy($requested_by)->setRequestDate($row['request_date'])->setApproved($row['approved'])->setApprovedBy($approved_by)->setApprovedDate($row['approved_date'])->setDateLastModified($row['date_last_modified'])->setCancelled($row['cancelled'])->setCancelledDate($row['cancel_date'])->setCancelledBy($canceled_by)->setReferral($referral)->setNotes($notes)->setLabs($labs)->setImagings($imagings)->setProcedures($procedures)->setNotesCount($row['notes']);
				}

			}catch (PDOException $e){
				errorLog($e);
				$data = [];
			}
		} else {
			$data = [];
		}


		$results = (object)null;
		$results->data = $data;
		$results->total = $total;
		$results->page = $page;
		return $results;
	}
}