<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/26/16
 * Time: 7:02 PM
 */
class EncounterDAO
{
	private $conn = NULL;

	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Encounter.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAllergensDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterAddendumDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAttachmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function add($encounter, $pdo = NULL)
	{
		 //$encounter=new Encounter();
		$initiatorId = $encounter->getInitiator()->getId();
		$departmentId = $encounter->getDepartment()->getId();
		$patientId = $encounter->getPatient()->getId();
		$specialization = $encounter->getSpecialization() ? $encounter->getSpecialization()->getId() : "NULL";
		$schemeId = $encounter->getScheme()? $encounter->getScheme()->getId():'NULL';
		$followUp = var_export($encounter->getFollowUp(), TRUE);
		$startTime = $encounter->getStartDate() ? quote_esc_str($encounter->getStartDate()) : 'NOW()';
		$billId = $encounter->getBill() ?( is_array( $encounter->getBill()->getId()) ? "'". implode(",",  $encounter->getBill()->getId()) ."'" : $encounter->getBill()->getId()) : "NULL";
		$refId = $encounter->getReferrer() ? $encounter->getReferrer()->getId() : "NULL";
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO encounter (start_date, initiator_id, department_id, patient_id, specialization_id, follow_up, scheme_id, bill_line_id, referral_id ) VALUES ( $startTime, $initiatorId, $departmentId, $patientId, $specialization, $followUp, $schemeId, $billId, $refId )";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$encounter->setId($pdo->lastInsertId());
				return $encounter;
			}
			return NULL;
		} catch (PDOException $e) {
			errorLog($e);
			return NULL;
		}
	}

	function update($id, $pdo = NULL){
		try{
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE encounter SET triaged_on=NOW(), triaged_by={$_SESSION['staffID']} WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				return $this;
			}
			return null;

		}catch(PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function  getClaimed($id, $pdo=null){
		if (is_null($id) || is_blank($id)) {
			return NULL;
		}
		try{
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT signed_by FROM encounter WHERE id=$id AND claimed IS TRUE";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$result = new Encounter();
				$result->setSignedBy((new StaffDirectoryDAO())->getStaff($row['signed_by'], true));
				return $result;
			}
			
		}catch (PDOException $e){
			errorLog($e);
		}
	}

	function get($id, $getFull = FALSE, $pdo = NULL)
	{
		if (is_null($id) || is_blank($id)) {
			return NULL;
		}
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM encounter WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$id = $row['id'];
				if ($getFull) {
					$labs = (new PatientLabDAO())->getEncounterLabs($id, $pdo);
					$notes = (new VisitNotesDAO())->getEncounterNotes($id, NULL, $pdo);
					$presentingComplaints = (new VisitNotesDAO())->getEncounterNotes($id, ['s', 'd'], $pdo);
					// $diagnoses = (new PatientDiagnosisDAO())->getEncounterDiagnoses($id, $pdo);
					$diagnoses = (new VisitNotesDAO())->getEncounterNotes($id, 'a', $pdo);
					$diagnosesNotes = (new VisitNotesDAO())->getEncounterNotes($id, 'g', $pdo);
					$plan = (new VisitNotesDAO())->getEncounterNotes($id, 'p', $pdo);
					$medications = (new VisitNotesDAO())->getEncounterNotes($id, 'm', $pdo);
					
					$scans = (new PatientScanDAO())->getEncounterScans($id, $pdo);
					$prescriptions = (new PrescriptionDAO())->getEncounterPrescriptions($id, $pdo);
					$procedures = (new PatientProcedureDAO())->getEncounterProcedures($id, $pdo);
					$systems_reviews = (new VisitNotesDAO())->getEncounterNotes($id, 'v', $pdo);
					$investigations = (new VisitNotesDAO())->getEncounterNotes($id, 'i', $pdo);
					$examinations =  (new VisitNotesDAO())->getEncounterNotes($id, 'x', $pdo);
					$examinationNotes =  (new VisitNotesDAO())->getEncounterNotes($id, 'e', $pdo);
					$drugHistory =  (new PrescriptionDAO())->getEncounterPrescriptions($id, $pdo);
					//(new PatientSystemsReviewDAO())->getEncounterReviews($id, $pdo);
					$bills = [];
					$medicalHistory =  (new VisitNotesDAO())->getEncounterNotes($id, 't', $pdo);
					$addenda = (new EncounterAddendumDAO())->getForEncounter($row['id'], $pdo);
					$socialHistory =  (new VisitNotesDAO())->getEncounterNotes($id, 'hx', $pdo);
					$allergies = (new PatientAllergensDAO())->forEncounter($id, $pdo);
					$documents = (new PatientAttachmentDAO())->encounter($id, 0, 100, $pdo);
					$insurance = (new InsuranceSchemeDAO())->get($row['scheme_id'], FALSE, $pdo);

				} else {
					$labs = [];//(new PatientLabDAO())->getEncounterLabs($id, $pdo);
					$notes = [];//(new VisitNotesDAO())->getEncounterNotes($id, NULL, $pdo);
					$presentingComplaints = (new VisitNotesDAO())->getEncounterNotes($id, ['s','d'], $pdo);
					$diagnoses = (new VisitNotesDAO())->getEncounterNotes($id, 'a', $pdo);
					$diagnosesNotes = [];//(new VisitNotesDAO())->getEncounterNotes($id, 'g', $pdo);
					$plan = (new VisitNotesDAO())->getEncounterNotes($id, 'p', $pdo);
					$medications = (new VisitNotesDAO())->getEncounterNotes($id, 'm', $pdo);

					$scans = [];//(new PatientScanDAO())->getEncounterScans($id, $pdo);
					$prescriptions = [];//(new PrescriptionDAO())->getEncounterPrescriptions($id, $pdo);
					$procedures = [];//(new PatientProcedureDAO())->getEncounterProcedures($id, $pdo);
					$systems_reviews = [];//(new VisitNotesDAO())->getEncounterNotes($id, 'v', $pdo);
					$investigations = [];//(new VisitNotesDAO())->getEncounterNotes($id, 'i', $pdo);
					$examinations =  [];//(new VisitNotesDAO())->getEncounterNotes($id, 'x', $pdo);
					$examinationNotes =  [];//(new VisitNotesDAO())->getEncounterNotes($id, 'e', $pdo);
					$drugHistory =  [];//(new PrescriptionDAO())->getEncounterPrescriptions($id, $pdo);
					//(new PatientSystemsReviewDAO())->getEncounterReviews($id, $pdo);
					$bills = [];
					$medicalHistory = [];// (new VisitNotesDAO())->getEncounterNotes($id, 't', $pdo);
					$addenda = [];//(new EncounterAddendumDAO())->getForEncounter($row['id'], $pdo);
					$socialHistory = [];
					$allergies = [];
					$documents = [];
					$insurance = [];
				}
				
				//if(count($presentingComplaints) == 0 && count($diagnoses) == 0 && count($plan) == 0){
				//	$cancelable = true;
				//}
				
				$bills = [];
				foreach ( array_filter(explode(",",$row['bill_line_id'])) as $bId){
					$bills[] = (new BillDAO())->getBill($bId, TRUE, $pdo);
				}

				return (new Encounter())->setId($row['id'])->setCanceled((bool)$row['canceled'])->setTriagedBy($row['triaged_by'])->setTriagedOn($row['triaged_on'])->setStartDate($row['start_date'])->setOpen($row['open'])->setInitiator((new StaffDirectoryDAO())->getStaff($row['initiator_id'], FALSE, $pdo))->setDepartment((new DepartmentDAO())->get($row['department_id'], $pdo))->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo))->setClaimed($row['claimed'])->setSpecialization((new StaffSpecializationDAO())->get($row['specialization_id'], $pdo))->setLabs($labs)->setNotes($notes)->setScans($scans)->setPrescriptions($medications)->setProcedures($procedures)->setDiagnoses(array_merge($diagnoses, []))->setAddenda($addenda)->setSystemsReviews($systems_reviews)->setPlan($plan)->setPresentingComplaints($presentingComplaints)->setInvestigations($investigations)->setExaminations($examinations)->setMedicalHistory($medicalHistory)->setExamNotes($examinationNotes)->setSignedBy((new StaffDirectoryDAO())->getStaff($row['signed_by'], FALSE, $pdo))->setSignedOn($row['signed_on'])->setBills($bills)->setScheme($insurance)
					->setBill($row['bill_line_id']!=null ? $bills : NULL)
					->setDrugHistory($drugHistory)->setAllergies($allergies)->setSocialHistory($socialHistory)->setDocuments($documents);
			}
			return NULL;
		} catch (PDOException $e) {
			errorLog($e);
			return NULL;
		}
	}

	function getOpenEncounters($patientId, $pdo = NULL)
	{
		$data = [];
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM encounter WHERE patient_id=$patientId AND `open` IS TRUE ORDER BY start_date"; // ???
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], FALSE, $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}

	function getUnClaimedEncounters($patientId, $pdo = NULL)
	{
		$data = [];
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM encounter WHERE patient_id=$patientId AND `claimed` IS FALSE AND canceled IS FALSE ORDER BY start_date DESC"; // ???
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], FALSE, $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	

	function forPatient($patientId, $full=false, $page=0, $pageSize=10, $pdo = NULL)
	{
		$data = [];
		if(is_blank($patientId)){
			$results = (object)null;
			$results->data = [];
			$results->total = 0;
			$results->page = $page;

			return $results;
		}

		$sql = "SELECT e.*, CONCAT_WS(' ', sd.firstname, sd.lastname) AS signed_by_name, ENCOUNTER_NOTES_COUNT(e.id, 'complaints') AS count_complaints, ENCOUNTER_NOTES_COUNT(e.id, 'plans') AS count_plans, ENCOUNTER_NOTES_COUNT(e.id, 'diagnoses') AS count_diagnoses FROM encounter e LEFT JOIN staff_directory sd ON sd.staffId=e.signed_by WHERE patient_id=$patientId AND e.canceled IS FALSE ORDER BY start_date DESC";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			errorLog($e);
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;

		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				//$pc = "SELECT pvn.*, sd.username, CONCAT_WS(' ', sd.firstname, sd.lastname) AS doctorName FROM patient_visit_notes pvn LEFT JOIN staff_directory sd ON sd.staffId = pvn.noted_by WHERE encounter_id = ".$row['id']."  AND pvn.note_type='s' GROUP BY pvn.description";
				//$dg = "SELECT pvn.*, sd.username, CONCAT_WS(' ', sd.firstname, sd.lastname) AS doctorName FROM patient_visit_notes pvn LEFT JOIN staff_directory sd ON sd.staffId = pvn.noted_by WHERE encounter_id = ".$row['id']."  AND pvn.note_type='a' GROUP BY pvn.description";
				//$pl = "SELECT pvn.*, sd.username, CONCAT_WS(' ', sd.firstname, sd.lastname) AS doctorName FROM patient_visit_notes pvn LEFT JOIN staff_directory sd ON sd.staffId = pvn.noted_by WHERE encounter_id = ".$row['id']."  AND pvn.note_type='' GROUP BY pvn.description";
				//$row['count_plans'] = 0;
				//$row['count_complaints'] = 0;
				//$row['count_diagnoses'] = 0;
				$row['department'] = (new DepartmentDAO())->get($row['department_id'], $pdo);
				$row['specialization_'] = (new StaffSpecializationDAO())->get($row['specialization_id'], $pdo);
				$data[] = (object)$row;
				//$data[] = $this->get($row['id'], $full, $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$data = [];
		}

		$results = (object)null;
		$results->data = $data;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function unclaimedForPatient($patientId, $pdo = NULL)
	{
		$data = [];
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM encounter WHERE patient_id=$patientId AND claimed in (false) AND canceled IS FALSE ORDER BY start_date DESC";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], FALSE, $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}


	/////// get the unclaimed Encounters for HMO

    function unclaimedEncountersHMO($schemeId=null, $insurer=null, $full=false, $page=0, $pageSize=10, $startdate=null, $enddate=null, $pdo = NULL)
    {
	   
    	
	    $filter = "";
	    $extraquery = "";
	    if($insurer &&  !$schemeId){
		    $filter = "io.id=$insurer";
		    $extraquery = "LEFT JOIN insurance_schemes isc ON e.scheme_id=isc.id LEFT JOIN insurance_owners io ON isc.scheme_owner_id=io.id";
	    }else if($schemeId != null && $insurer == null){
		    $filter = "e.scheme_id=$schemeId";
		    $extraquery = "";
	    }else if ($schemeId != null && $insurer != null){
		    $filter = "e.scheme_id=$schemeId AND io.id=$insurer";
		    $extraquery = "LEFT JOIN insurance_schemes isc ON e.scheme_id=isc.id LEFT JOIN insurance_owners io ON isc.scheme_owner_id=io.id";
		
	    }
	    
	    $date_filter = "";
	    if($startdate && $enddate){
	    	$date_filter = " AND DATE(e.start_date) BETWEEN DATE('$startdate') and DATE('$enddate')";
	    }
	    
        $data = [];
        $sql = "SELECT e.* FROM encounter e $extraquery WHERE $filter $date_filter AND e.canceled IS FALSE AND e.claimed IS FALSE ORDER BY e.start_date DESC";
        $total = 0;
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	
	        $stmt->execute();
            $total = $stmt->rowCount();
        } catch (PDOException $e) {
            errorLog($e);
        }

        $page = ($page > 0) ? $page : 0;
        $offset = ($page > 0) ? $pageSize * $page : 0;

        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql .= " LIMIT $offset, $pageSize";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	        $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                //$pc = "SELECT pvn.*, sd.username, CONCAT_WS(' ', sd.firstname, sd.lastname) AS doctorName FROM patient_visit_notes pvn LEFT JOIN staff_directory sd ON sd.staffId = pvn.noted_by WHERE encounter_id = ".$row['id']."  AND pvn.note_type='s' GROUP BY pvn.description";
                //$dg = "SELECT pvn.*, sd.username, CONCAT_WS(' ', sd.firstname, sd.lastname) AS doctorName FROM patient_visit_notes pvn LEFT JOIN staff_directory sd ON sd.staffId = pvn.noted_by WHERE encounter_id = ".$row['id']."  AND pvn.note_type='a' GROUP BY pvn.description";
                //$pl = "SELECT pvn.*, sd.username, CONCAT_WS(' ', sd.firstname, sd.lastname) AS doctorName FROM patient_visit_notes pvn LEFT JOIN staff_directory sd ON sd.staffId = pvn.noted_by WHERE encounter_id = ".$row['id']."  AND pvn.note_type='' GROUP BY pvn.description";

                $data[] = $this->get($row['id'], $full, $pdo);
            }
        } catch (PDOException $e) {
            errorLog($e);
            $data = [];
        }

        $results = (object)null;
        $results->data = $data;
        $results->total = $total;
        $results->page = $page;

        return $results;
    }


}
