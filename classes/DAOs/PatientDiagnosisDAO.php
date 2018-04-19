<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/16/14
 * Time: 5:08 PM
 */

class PatientDiagnosisDAO {
	private $conn = null;

	function __construct() {
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PatientDiagnosis.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/VisitNotes.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/DiagnosisDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/VisitNotesDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
			
			$this->conn=new MyDBConnector();
		}catch(PDOException $e) {
			if(!isset($_SESSION)){session_start();}
			exit( 'ERROR: ' . $e->getMessage() );
		}
	}

	function get($id, $pdo=NULL){
		try {
			$pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_diagnoses WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$diagnosis = (new PatientDiagnosis($row['id']))
					->setPatient( (new PatientDemographDAO())->getPatient($row['patient_ID'], FALSE, $pdo, NULL) )
					->setDate($row['date_of_entry'])
					->setDiagnosedBy( (new StaffDirectoryDAO())->getStaff($row['diagnosed_by'], FALSE, $pdo) )
					->setNote($row['diagnosisNote'])
					->setType($row['_status'])
					->setDiagnosis( (new DiagnosisDAO())->getDiagnosis($row['diagnosis'], $pdo) )
					->setStatus($row['active'])
					->setSeverity($row['severity'])
					->setClinic( (new ClinicDAO())->getClinic($row['hospital_diagnosed'], FALSE, $pdo) );

				return $diagnosis;
			}
			return NULL;
		}catch (PDOException $E){
			return NULL;
		}
	}

	function all($pdo=NULL){
		$sql = "SELECT * FROM patient_diagnoses";
		$diagnoses = [];
		try {
			$pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$diagnoses[] = $this->get($row['id'], $pdo);
			}
			$stmt = NULL;
		}catch (PDOException $E){
			$diagnoses= [];
		}
		return $diagnoses;
	}

	function reportAll($page=0, $pageSize=10, $from=null, $to=null, $diagnosis=null, $pdo=NULL){
		$filter = "";
		if($from != NULL && $to != NULL){
			$filter = " WHERE DATE(d.date_of_entry) BETWEEN '".$from."' AND '".$to."'";
		}
		
		if($diagnosis != null){
			$filter .= " AND d.diagnosis=$diagnosis";
		}
		
		if((is_null($from) || is_null($to)) && is_null($diagnosis)){
			$results = (object)null;
			$results->data = [];
			$results->total = 0;
			$results->page = $page;
			return $results;
		}
		$sql = "SELECT d.id, d._status AS `Status`, d.patient_ID, d.diagnosis, d.date_of_entry AS `Date`, demo.sex, demo.date_of_birth, CONCAT_WS(' ', demo.fname, demo.mname, demo.lname) AS Patient, CONCAT_WS(' ', s.firstname, s.lastname) AS DiagnosedBy, d.diagnosisNote AS Note, d.`diag-type` AS Type, dg.code AS `DCode`, dg.type AS DType, d.severity, dg.case AS Diagnosis, PATIENT_SCHEME(d.patient_ID) AS coverage, bp.name as body_part FROM patient_diagnoses d LEFT JOIN patient_demograph demo  ON demo.patient_ID=d.patient_ID LEFT JOIN staff_directory s ON d.diagnosed_by=s.staffId LEFT JOIN diagnoses dg ON dg.id=d.diagnosis LEFT JOIN body_part bp ON bp.id=d.body_part_id $filter";
		$total = 0;
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e){
			errorLog($e);
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$diagnoses = [];
		try {
			$pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$diagnosis = (object)$row;
//                $diagnosis->Patient = [$row['patient'], $row['patient_ID']];
//                $diagnosis->Date = $row['date_of_entry'];
//                $diagnosis->DiagnosedBy = $row['staff'];
//                $diagnosis->Note = $row['diagnosisNote'];
//                $diagnosis->Type = $row['_status'];
//                $diagnosis->Diagnosis = $row['diagName'];
//                $diagnosis->Status = $row['_status'];
				$diagnoses[] = $diagnosis;
			}
			$stmt = NULL;
		}catch (PDOException $E){
			$diagnoses= [];
		}
		$results = (object)null;
		$results->data = $diagnoses;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function one($pid, $type=NULL, $active=NULL, $severity='chronic', $page = 0, $pageSize = 10, $pdo=NULL, $encounter=NULL){
		$extras = "";
		if($type){$extras.=" AND _status = '$type'";}
		if($active){$extras.= " AND active=$active";}
		if($severity){$extras.= " AND severity='$severity'";}
		if($encounter){$extras.= " AND encounter_id='$encounter'";}

		$sql = "SELECT pd.*, pd.`diag-type` AS diagType, sd.username, d.code, d.type AS diagnosisType, d.`case`, bp.name AS body_part FROM patient_diagnoses pd LEFT JOIN diagnoses d ON d.id=pd.diagnosis LEFT JOIN staff_directory sd ON sd.staffId=pd.diagnosed_by LEFT JOIN body_part bp ON pd.body_part_id=bp.id WHERE patient_ID = $pid $extras ORDER BY date_of_entry DESC";
		$total = 0;
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e){
			errorLog($e);
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;

		$diagnoses = [];
		try {
			$pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
//                $diagnosis = $this->get($row['id'], $pdo);
//                $diagnoses[] = $diagnosis;
				$diagnoses[] = (object)$row;
			}
			$stmt = NULL;
		}catch (PDOException $E){
			return (object)null;
		}
		$results = (object)null;
		$results->data = $diagnoses;
		$results->total = $total;
		$results->page = $page;
		return $results;
	}


	function oneByDate($pid, $start, $end, $pdo=NULL){
		if($start == NULL){
			$dateStart = '1970-01-01';
		} else {
			$dateStart = date("Y-m-d", strtotime($start));
		}
		if($end == NULL){
			$dateStop = date("Y-m-d");
		}else {
			$dateStop = date("Y-m-d", strtotime($end));
		}
		$sql = "SELECT * FROM patient_diagnoses WHERE patient_ID = $pid AND DATE(date_of_entry) BETWEEN '$dateStart' AND '$dateStop' ORDER BY date_of_entry";
		$diagnoses = [];
		try {
			$pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$diagnosis = $this->get($row['id'], $pdo);
				$diagnoses[] = $diagnosis;
			}
			$stmt = NULL;
			return $diagnoses;

		}catch (PDOException $E){
			return [];
		}
	}

	function getPatientDiagnoses($pid, $pdo=NULL){ // For Mobile
		$sql = "SELECT * FROM patient_diagnoses WHERE patient_ID = $pid ORDER BY date_of_entry";
		$diagnoses = [];
		try {
			$pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$diagnosis = $this->get($row['id'], $pdo);
				$diagnoses[] = $diagnosis;
			}
			$stmt = NULL;
			return $diagnoses;

		}catch (PDOException $E){
			return [];
		}
	}

	function add($d, $pdo=NULL){
		//$d = new PatientDiagnosis();
		$patient = $d->getPatient()->getId();
		$dateOfEntry = $d->getDate() ? $d->getDate() : date("Y-m-d H:i:s", time());
		$diagnosedBy = $d->getDiagnosedBy() ? $d->getDiagnosedBy()->getId() : $_SESSION['staffID'];
		$diagnosisNote = escape($d->getNote());
		$diagnosisType = $d->getType();
		$diagnosis = (new DiagnosisDAO())->getDiagnosis($d->getDiagnosis()->getId(), $pdo);
		$status = var_export($d->getStatus(), true);
		$severity = $d->getSeverity() ? $d->getSeverity() : "chronic";
		$clinicId = 1;
		$encounter = $d->getEncounter()? $d->getEncounter()->getId() : "NULL";
		$inPatient = $d->getInPatient()? $d->getInPatient()->getId() : "NULL";
		$bodypart = $d->getBodyPart() ? $d->getBodyPart() : "NULL";

		$sql = "INSERT INTO patient_diagnoses (patient_ID, date_of_entry, diagnosed_by, diagnosisNote, diagnosis, `_status`, severity, `active`, hospital_diagnosed, encounter_id, in_patient_id, body_part_id) VALUES ($patient, '$dateOfEntry', $diagnosedBy, '$diagnosisNote', ".$diagnosis->getId().", '$diagnosisType',  '$severity', $status,$clinicId, $encounter, $inPatient, $bodypart)";
		try {
			$pdo = $pdo===null ? $this->conn->getPDO(): $pdo;
			$canCommit = !$pdo->inTransaction();
			try {
				$pdo->beginTransaction();
			}catch(PDOException $e){
				//not bad
			}
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() > 0){
				$d->setId($pdo->lastInsertId());

				if(!is_blank($diagnosisNote)) {
					$type = ($diagnosisType == "history") ? "diag_hist" : "diag_note";
					$vNote = (new VisitNotes())->setPatient($d->getPatient())->setEncounter($d->getEncounter())->setNoteType($type)->setDescription($diagnosisNote)->setDateOfEntry(date('Y-m-d H:i:s'))->setNotedBy($d->getDiagnosedBy() ? $d->getDiagnosedBy() : new StaffDirectory($_SESSION['staffID']));

					if (!(new VisitNotesDAO())->addNote($vNote, $pdo)) {
						if ($canCommit) {
							$pdo->rollBack();
						}
						return null;
					}
				}
				if($canCommit){
					$pdo->commit();
				}
				return $d;
			}
			if($canCommit){
				$pdo->rollBack();
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

	public function getEncounterDiagnoses($id, $pdo)
	{
		$diagnoses = [];
		try {
			$pdo = $pdo===null ? $this->conn->getPDO(): $pdo;
			$sql = "SELECT * FROM patient_diagnoses WHERE encounter_id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$diagnosis = $this->get($row['id'], $pdo);
				$diagnoses[] = $diagnosis;
			}
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
		return $diagnoses;
	}
	public function getInPatientDiagnoses($admissionId, $pdo=null)
	{
		$diagnoses = [];
		try {
			$pdo = $pdo===null ? $this->conn->getPDO(): $pdo;
			$sql = "SELECT * FROM patient_diagnoses WHERE in_patient_id=$admissionId AND in_patient_id IS NOT NULL";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$diagnosis = $this->get($row['id'], $pdo);
				$diagnoses[] = $diagnosis;
			}
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
		return $diagnoses;
	}
}