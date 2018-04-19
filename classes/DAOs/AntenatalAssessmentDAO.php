<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/6/15
 * Time: 9:56 AM
 */
class AntenatalAssessmentDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AntenatalAssessment.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientHistoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/FetalBrainRelationshipDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/FetalPresentationDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function add($assessment, $pdo = null)
	{
		//$assessment = new AntenatalAssessment();
		$user = $assessment->getUser()->getId();
		$patient = $assessment->getAntenatalInstance()->getPatient()->getId();
		$createDate = $assessment->getDate();
		$antenatalInstanceId = $assessment->getAntenatalInstance()->getId();
		$fundusHeight = !is_blank($assessment->getFundusHeight()) ? "'" . $assessment->getFundusHeight() . "'" : "NULL";
		$fhr = !is_blank($assessment->getFhr()) ? "'" . $assessment->getFhr() . "'" : "NULL";
		$fetal_presentation = ($assessment->getFetalPresentation()) ? $assessment->getFetalPresentation()->getId() : "NULL";
		$fetal_brain_relationship_id = ($assessment->getFetalBrainRelationship()) ? $assessment->getFetalBrainRelationship()->getId() : "NULL";
		$fetal_lie = $assessment->getFetalLie() ? quote_esc_str($assessment->getFetalLie()) : 'NULL';
		$comments = !is_blank($assessment->getComments()) ? "'" . escape($assessment->getComments()) . "'" : "NULL";
		$labs = !is_blank($assessment->getLab()) ? "'" . $assessment->getLab() . "'" : "NULL";
		$scans = !is_blank($assessment->getScan()) ? "'" . $assessment->getScan() . "'" : "NULL";
		$nextAppointmentDate = !is_blank($assessment->getNextAppointmentDate()) ? "'" . $assessment->getNextAppointmentDate() . "'" : "NULL";
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$canCommit = !$pdo->inTransaction();
			try {
				$pdo->beginTransaction();
			} catch (PDOException $ef) {
				//
			}
			
			$sql = "INSERT INTO antenatal_assessment (create_date, create_user_id, patient_id, enrollment_id, fundusHeight, fhr, fetal_presentation_id, fetal_brain_relationship_id, fetal_lie, comments, lab_request_code, scan_request_code, nextAppointmentDate) VALUES ('$createDate', $user, $patient, $antenatalInstanceId, $fundusHeight, $fhr, $fetal_presentation, $fetal_brain_relationship_id, $fetal_lie, $comments, $labs, $scans, $nextAppointmentDate)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				$assessment->setId($pdo->lastInsertId());
			}
			$return = [];
			/*foreach ($assessment->getData() as $data) {
					//todo: this is no more a patient history thingy
					//so it's empty for now or wasn't set actually
					$return[] = (new PatientHistoryDAO())->add($data, $assessment, 'antenatal', $pdo);
			}*/
			if (!in_array(null, $return)) {
				if ($canCommit) {
					$pdo->commit();
				}
				return $assessment;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function get($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * from antenatal_assessment WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$as = (new AntenatalAssessment($row['id']))->setAntenatalInstance((new AntenatalEnrollmentDAO())->get($row['enrollment_id'], true, $pdo))->setComments($row['comments'])->setDate($row['create_date'])->setUser((new StaffDirectoryDAO())->getStaff($row['create_user_id'], false, $pdo))->setFundusHeight($row['fundusHeight'])->setFetalBrainRelationship((new FetalBrainRelationshipDAO())->get($row['fetal_brain_relationship_id'], $pdo))->setFetalPresentation((new FetalPresentationDAO())->get($row['fetal_presentation_id'], $pdo))->setFetalLie($row['fetal_lie'])->setFhr($row['fhr'])->setLab($row['lab_request_code'])->setScan($row['scan_request_code'])->setNextAppointmentDate($row['nextAppointmentDate']);
				
				return $as;
			} else {
				return null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	
	function all($pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM antenatal_assessment";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			$data = [];
			errorLog($e);
		}
		
		return $data;
	}
	
	public function getAntenatalInstanceAssessments($instanceId, $pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT a.* from antenatal_assessment a WHERE enrollment_id=$instanceId # LEFT JOIN patient_systems_review r ON a.id=r.assessment_id WHERE r.antenatal_instance_id=$instanceId AND r.type='antenatal'";
			//$sql = "SELECT a.* from antenatal_assessment a LEFT JOIN patient_history h ON a.id=h.assessment_id WHERE h.instance_id=$instanceId AND h.type='antenatal'";error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			$data = [];
			errorLog($e);
		}
		
		return $data;
	}
}