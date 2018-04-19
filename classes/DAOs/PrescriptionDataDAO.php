<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PrescriptionDataDAO
 *
 * @author pauldic
 */
class PrescriptionDataDAO
{
	private $conn = null;
	
	/**
	 * PrescriptionDataDAO constructor.
	 */
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Drug.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PrescriptionData.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugBatchDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAntenatalUsagesDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BodyPartDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SubstitutionCodeDAO.php';
			
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			errorLog($e);
		}
	}

	function addPrescriptionData($pds,  $pdo = null)
	{
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO patient_regimens_data (group_code, drug_id, drug_generic_id, dose, duration, `comment`, batch_id, frequency, refillable, `status`, requested_by, hospid, bodypart_id, external_source, refill_date,refill_number, related_id, diagnoses_id) VALUES ";
			$sqlPart = [];
			foreach ($pds as $pd) {
				$batchId = ($pd->getBatch() !== null) ? $pd->getBatch()->getId() : "NULL";
				$comment = !is_blank($pd->getComment()) ? quote_esc_str($pd->getComment()) : "NULL";
				$refill_date = !is_blank($pd->getRefillDate()) ? quote_esc_str($pd->getRefillDate()) : "NULL";
				$refill_number = !is_blank($pd->getRefillNumber()) && (int)$pd->getRefillNumber() != 0 ? quote_esc_str($pd->getRefillNumber()) : "NULL";
				$bodyPart = $pd->getBodyPart() ? $pd->getBodyPart()->getId() : "NULL";
				$externalSource = $pd->getExternalSource() == 'yes' ? var_export(true, true) : var_export(false, true);
				$duration = !is_blank(parseNumber($pd->getDuration())) ? parseNumber($pd->getDuration()): 'NULL';
				$relatedId = $pd->getRelated() ? $pd->getRelated()->getId() : "NULL";
				$diagnosis = $pd->getDiagnosis() ? $pd->getDiagnosis()->getId() : "NULL";
				$sqlPart[] = "('" . $pds[0]->getCode() . "', " . (($pd->getDrug() === null) ? "NULL" . ", '" . $pd->getGeneric()->getId() . "', " : "'" . $pd->getDrug()->getId() . "', '" . $pd->getGeneric()->getId() . "', ") . "'" . $pd->getDose() . "', $duration, $comment, $batchId, '" . $pd->getFrequency() . "', " . var_export((bool)$pd->isRefillable(), true) . ", '" . (($pd->getStatus() != null) ? $pd->getStatus() : "open") . "', '" . $pd->getRequestedBy()->getId() . "', '" . $pd->getHospital()->getId() . "', $bodyPart, $externalSource, $refill_date, $refill_number, $relatedId,$diagnosis)";
				
			}
			$sql .= implode(",", $sqlPart);
			
			error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$stmt = null;
			$pds = [];
		}
		return $pds;
	}
	
	function addInPatientPrescriptionData($pds, $pdo = null)
	{
		
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO patient_regimens_data (group_code, drug_id, drug_generic_id, dose, duration, `comment`, frequency, refillable, `status`, requested_by, filled_by, filled_on, completed_by, completed_on, hospid, bodypart_id, refill_date,refill_number,related_id,diagnoses_id) VALUES ";
			$sqlPart = [];
			foreach ($pds as $pd) {
				$comment = !is_blank($pd->getComment()) ? quote_esc_str($pd->getComment()) : "NULL";
				$refill_date = !is_blank($pd->getRefillDate()) ?  quote_esc_str($pd->getRefillDate()) : "NULL";
				$refill_number = !is_blank($pd->getRefillNumber()) ?  quote_esc_str($pd->getRefillNUmber()) : "NULL";
				$duration = !is_blank(parseNumber($pd->getDuration())) ? parseNumber($pd->getDuration()): 'NULL';
				$bodyPart = $pd->getBodyPart() ? $pd->getBodyPart()->getId() : "NULL";
				$relatedId = $pd->getRelated() ? $pd->getRelated()->getId() : "NULL";
				
				$diagnosis = $pd->getDiagnosis() ? $pd->getDiagnosis()->getId() : "NULL";
				
				$sqlPart[] =  "('" . $pds[0]->getCode() . "', " . (($pd->getDrug() != null) ? $pd->getDrug()->getId() : "NULL") . ", " . $pd->getGeneric()->getId() . ", '" . $pd->getDose() . "', $duration, $comment, '" . $pd->getFrequency() . "', " . var_export((bool)$pd->isRefillable(), true) . ", '" . (($pd->getStatus() != null) ? $pd->getStatus() : "open") . "', '" . $pd->getRequestedBy()->getId() . "', " . (is_null($pd->getFilledBy()) ? "NULL" : $pd->getFilledBy()->getId()) . ", " . (is_null($pd->getFilledBy()) ? "NULL" : NOW()) . ", " . (is_null($pd->getCompletedBy()) ? "NULL" : $pd->getCompletedBy()->getId()) . ", " . (is_null($pd->getCompletedBy()) ? "NULL" : NOW()) . ", '" . $pd->getHospital()->getId() . "', $bodyPart, $refill_date, $refill_number,$relatedId,$diagnosis)";
			}
			$sql .= implode(",", $sqlPart);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$pdo->rollBack();
			$stmt = null;
			$pds = [];
		}
		return $pds;
	}
	
	function updatePatientRegimenData($pd, $pdo=null){
		$today = date('y-m-d');
		try{
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			foreach ($pd as $p){

				$sql = "UPDATE patient_regimens_data SET refillable= FALSE WHERE id='". $p->ref_code ."'";
				$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt->execute();

				$sql_2 = "UPDATE patient_regimens_data SET refill_number= '". ($p->refill_number - 1) ."', refill_date=null  WHERE id='". $p->ref_code ."' ";
				$stmt_2 = $pdo->prepare($sql_2, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt_2->execute();

		}

		}catch (PDOException $e){
			errorLog($e);
		}

	}
	
	/*ISAAC'S */
	function getPatientPrescriptionData_($pid, $getFull = FALSE, $pdo = null)
	{
		$pd = new PrescriptionData();
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT p.group_code, p.patient_id, pr.drug_id, pr.dose, pr.quantity, pr.duration, pr.frequency, pr.comment FROM patient_regimens p LEFT JOIN patient_regimens_data pr ON p.group_code = pr.group_code WHERE p.patient_id=" . $pid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pd->setCode($row['group_code']);
				if ($getFull) {
					$drug = $row['drug_id'] === null ? null : (new DrugDAO())->getDrug(['drug_id'], TRUE);
					if ($drug === null) {
						$gen = (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], $getFull, $pdo);
					} else {
						$gen = $drug->getGeneric();
					}
				} else {
					$drug = ($row['drug_id'] === null) ? null : new Drug($row['drug_id']);
					$gen = new DrugGeneric($row['drug_generic_id']);
				}
				$pd->setDrug($drug);
				$pd->setGeneric($gen);
				$pd->setQuantity($row['quantity']);
				$pd->setDose($row['dose']);
				$pd->setDuration($row['duration']);
				$pd->setComment($row['comment']);
				$pd->setFrequency($row['frequency']);

			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$pd = null;
		}
		return $pd;
	}

	function getPrescriptionDatum($pid, $getFull = FALSE, $pdo = null)
	{
		$pd = new PrescriptionData();
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_regimens_data WHERE id=" . $pid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pd->setId($row['id']);
				$pd->setCode($row['group_code']);
				$pd->setBatch(trim($row['batch_id']) != "" ? (new DrugBatchDAO())->getBatch($row['batch_id'], $pdo) : null);

				if ($getFull) {
					$drug = $row['drug_id'] === null ? null : (new DrugDAO())->getDrug($row['drug_id'], TRUE, $pdo);
					if ($drug === null) {
						$gen = (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], $getFull, $pdo);
					} else {
						$gen = $drug->getGeneric();
					}
					$dao = new StaffDirectoryDAO();
					$req = $dao->getStaff($row['requested_by'], FALSE, $pdo);
					$mod = $dao->getStaff($row['modified_by'], FALSE, $pdo);
					$fil = $dao->getStaff($row['filled_by'], FALSE, $pdo);
					$com = $dao->getStaff($row['completed_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$drug = ($row['drug_id'] === null) ? null : new Drug($row['drug_id']);
					$gen = new DrugGeneric($row['drug_generic_id']);
					$req = new StaffDirectory($row['requested_by']);
					$mod = new StaffDirectory($row['modified_by']);
					$fil = new StaffDirectory($row['filled_by']);
					$com = new StaffDirectory($row['completed_by']);
					$hosp = new Clinic($row['hospid']);
				}
				$pd->setDrug($drug);
				$pd->setGeneric($gen);
				$pd->setQuantity($row['quantity']);
				$pd->setDose($row['dose']);
				$pd->setDuration($row['duration']);
				$pd->setRefillDate($row['refill_date']);
				$pd->setComment($row['comment']);
				$pd->setBodyPart((new BodyPartDAO())->get($row['bodypart_id'], $pdo));
				$pd->setFrequency($row['frequency']);
				$pd->setRefillable($row['refillable']);
				$pd->setRefillNumber($row['refill_number']);
				$pd->setStatus($row['status']);
				$pd->setRequestedBy($req);
				$pd->setModifiedBy($mod);
				$pd->setFilledBy($fil);
				$pd->setFilledOn($row['filled_on']);
				$pd->setCompletedBy($com);
				$pd->setCompletedOn($row['completed_on']);
				$pd->setCancelledBy((new StaffDirectoryDAO())->getStaff($row['cancelled_by'], TRUE, $pdo));
				$pd->setCancelledOn($row['cancelled_on']);
				$pd->setCancelNote($row['cancel_note']);
				$pd->setSubstitutionReason( (new SubstitutionCodeDAO())->get($row['substitution_reason'], $pdo) ? (new SubstitutionCodeDAO())->get($row['substitution_reason'], $pdo)->getName() : NULL );
				$pd->setSubstitutedOn($row['substituted_on']);
				$pd->setSubstitutedBy((new StaffDirectoryDAO())->getStaff($row['substituted_by'], TRUE, $pdo));
				$pd->setHospital($hosp);
				
				$bills = [];
				foreach( array_filter(explode(",", $row['bill_line_id']))  as $bId){
					$bills[] = (new BillDAO())->getBill($bId, TRUE, $pdo);
				}
				
				$pd->setBill($row['bill_line_id']!= null ? $bills : NULL );
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$pd = null;
		}
		return $pd;
	}


	function getPrescriptionDataByCode($iCode, $getFull = FALSE, $pdo = null)
	{
		$pds = array();
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_regimens_data WHERE group_code='" . $iCode . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pds[] = $this->getPrescriptionDatum($row['id'], $getFull, $pdo);
			}
		} catch (PDOException $e) {
			$pds = [];
		}
		return $pds;
	}


	function getRefillPrescriptionDataByCode($iCode, $getFull = FALSE, $pdo = null)
	{
		$pds = array();
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_regimens_data WHERE group_code='" . $iCode . "' AND refillable=TRUE AND `status` != 'cancelled'";

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pd = new PrescriptionData();
				$pd->setId($row['id']);
				$pd->setCode($row['group_code']);
				$pd->setBatch(trim($row['batch_id']) != "" ? (new DrugBatchDAO())->getBatch($row['batch_id'], $pdo) : null);

				if ($getFull) {
					$drug = $row['drug_id'] === null ? null : (new DrugDAO())->getDrug($row['drug_id'], TRUE, $pdo);
					if ($drug === null) {
						$gen = (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], $getFull, $pdo);
					} else {
						$gen = $drug->getGeneric();
					}
					$dao = new StaffDirectoryDAO();
					$req = $dao->getStaff($row['requested_by'], FALSE, $pdo);
					$mod = $dao->getStaff($row['modified_by'], FALSE, $pdo);
					$fil = $dao->getStaff($row['filled_by'], FALSE, $pdo);
					$com = $dao->getStaff($row['completed_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$drug = ($row['drug_id'] === null) ? null : new Drug($row['drug_id']);
					$gen = new DrugGeneric($row['drug_generic_id']);
					$req = new StaffDirectory($row['requested_by']);
					$mod = new StaffDirectory($row['modified_by']);
					$fil = new StaffDirectory($row['filled_by']);
					$com = new StaffDirectory($row['completed_by']);
					$hosp = new Clinic($row['hospid']);
				}
				$pd->setDrug($drug);
				$pd->setGeneric($gen);
				$pd->setQuantity($row['quantity']);
				$pd->setDose($row['dose']);
				$pd->setDuration($row['duration']);
				$pd->setRefillDate($row['refill_date']);
				$pd->setComment($row['comment']);
				$pd->setFrequency($row['frequency']);
				$pd->setRefillable($row['refillable']);
				$pd->setStatus('open');
				$pd->setRequestedBy($req);
				$pd->setModifiedBy($mod);
				$pd->setFilledBy($fil);
				$pd->setFilledOn($row['filled_on']);
				$pd->setCompletedBy($com);
				$pd->setCompletedOn($row['completed_on']);
				$pd->setCancelledBy((new StaffDirectoryDAO())->getStaff($row['cancelled_by'], TRUE, $pdo));
				$pd->setCancelledOn($row['cancelled_on']);
				$pd->setCancelNote($row['cancel_note']);
				$pd->setHospital($hosp);
				$pds[] = $pd;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pds = [];
		}
		return $pds;
	}

	function getUncomputedPrescriptionDataByCode($iCode, $getFull = FALSE, $pdo = null)
	{
		$pds = array();
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_regimens_data WHERE group_code='" . $iCode . "'";

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pds[] = $this->getPrescriptionDatum($row['id'], $getFull, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pds = [];
		}
		return $pds;
	}

	function getLastAdministrationTime($ipid, $pdo = null)
	{
		$time = null;
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT d.completed_on AS `time` FROM patient_regimens_data d  LEFT JOIN patient_regimens p ON p.group_code=d.group_code WHERE p.in_patient_id=" . $ipid . " ORDER BY completed_on DESC LIMIT 1";
//            errorLog($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$time = $row['time'];
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = $time = null;
		}
		return $time;
	}

	function getPrescriptionData($getFull = FALSE, $pdo = null)
	{
		$pds = array();
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_regimens_data";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pds[] = $this->getPrescriptionDatum($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pds = array();
		}
		return $pds;
	}

	function getFullfilledPrescriptionData($ipid, $did, $getFull = FALSE, $pdo = null)
	{
		$pds = array();
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT d.* FROM patient_regimens_data d LEFT JOIN patient_regimens r ON r.group_code=d.group_code WHERE r.in_patient_id=" . $ipid . " AND d.drug_id=" . $did;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pd = new PrescriptionData($row['id']);
				$pd->setBatch((new DrugBatchDAO())->getBatch($row['batch_id'], $pdo));

				if ($getFull) {
					$drug = $row['drug_id'] === null ? null : (new DrugDAO())->getDrug($row['drug_id'], TRUE, $pdo);
					if ($drug === null) {
						$gen = (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], TRUE, $pdo);
					} else {
						$gen = $drug->getGeneric();
					}

					$dao = new StaffDirectoryDAO();
					$req = $dao->getStaff($row['requested_by'], FALSE, $pdo);
					$mod = $dao->getStaff($row['modified_by'], FALSE, $pdo);
					$fil = $dao->getStaff($row['filled_by'], FALSE, $pdo);
					$com = $dao->getStaff($row['completed_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$drug = ($row['drug_id'] === null) ? null : new Drug($row['drug_id']);
					$gen = new DrugGeneric($row['drug_generic_id']);
					$req = new StaffDirectory($row['requested_by']);
					$mod = new StaffDirectory($row['modified_by']);
					$fil = new StaffDirectory($row['filled_by']);
					$com = new StaffDirectory($row['completed_by']);
					$hosp = new Clinic($row['hospid']);
				}
				$pd->setCode($row['group_code']);
				$pd->setDrug($drug);
				$pd->setGeneric($gen);
				$pd->setQuantity($row['quantity']);
				$pd->setDose($row['dose']);
				$pd->setDuration($row['duration']);
				$pd->setRefillDate($row['refill_date']);
				$pd->setComment($row['comment']);
				$pd->setFrequency($row['frequency']);
				$pd->setRefillable($row['refillable']);
				$pd->setStatus($row['status']);
				$pd->setRequestedBy($req);
				$pd->setModifiedBy($mod);
				$pd->setFilledBy($fil);
				$pd->setFilledOn($row['filled_on']);
				$pd->setCompletedBy($com);
				$pd->setCompletedOn($row['completed_on']);
				$pd->setCancelledBy((new StaffDirectoryDAO())->getStaff($row['cancelled_by'], TRUE, $pdo));
				$pd->setCancelledOn($row['cancelled_on']);
				$pd->setCancelNote($row['cancel_note']);
				$pd->setHospital($hosp);

				$pds[] = $pd;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pds = array();
		}
		return $pds;
	}

	function aggregateIPPrescriptionData($ipid, $getFull = FALSE, $pdo = null)
	{
		$pds = array();
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT d.*, sum(dose) AS total FROM patient_regimens_data d LEFT JOIN patient_regimens r ON r.group_code=d.group_code WHERE r.in_patient_id=" . $ipid . " GROUP BY drug_id";
//            errorLog($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pd = new PrescriptionData($row['id']);
				$pd->setBatch((new DrugBatchDAO())->getBatch($row['batch_id'], $pdo));
				if ($getFull) {
					$drug = $row['drug_id'] === null ? null : (new DrugDAO())->getDrug($row['drug_id'], TRUE, $pdo);
					if ($drug === null) {
						$gen = (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], $pdo);
					} else {
						$gen = $drug->getGeneric();
					}
					$dao = new StaffDirectoryDAO();
					$req = $dao->getStaff($row['requested_by'], FALSE, $pdo);
					$mod = $dao->getStaff($row['modified_by'], FALSE, $pdo);
					$fil = $dao->getStaff($row['filled_by'], FALSE, $pdo);
					$com = $dao->getStaff($row['completed_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$drug = ($row['drug_id'] === null) ? null : new Drug($row['drug_id']);
					$gen = new DrugGeneric($row['drug_generic_id']);
					$req = new StaffDirectory($row['requested_by']);
					$mod = new StaffDirectory($row['modified_by']);
					$fil = new StaffDirectory($row['filled_by']);
					$com = new StaffDirectory($row['completed_by']);
					$hosp = new Clinic($row['hospid']);
				}
				$pd->setCode($row['group_code']);
				$pd->setDrug($drug);
				$pd->setGeneric($gen);
				$pd->setQuantity($row['total']);
				$pd->setRefillDate($row['refill_date']);
				$pd->setDose($row['dose']);
				$pd->setDuration($row['duration']);
				$pd->setComment($row['comment']);
				$pd->setBodyPart((new BodyPartDAO())->get($row['bodypart_id'], $pdo));
				$pd->setFrequency($row['frequency']);
				$pd->setRefillable($row['refillable']);
				$pd->setStatus($row['status']);
				$pd->setRequestedBy($req);
				$pd->setModifiedBy($mod);
				$pd->setFilledBy($fil);
				$pd->setFilledOn($row['filled_on']);
				$pd->setCompletedBy($com);
				$pd->setCompletedOn($row['completed_on']);
				$pd->setCancelledBy((new StaffDirectoryDAO())->getStaff($row['cancelled_by'], TRUE, $pdo));
				$pd->setCancelledOn($row['cancelled_on']);
				$pd->setCancelNote($row['cancel_note']);
				$pd->setHospital($hosp);

				$pds[] = $pd;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pds = array();
		}
		return $pds;
	}

	function fillPrescription($pres, $pdo = null)
	{
		$refill_date = $pres->getRefillDate() ? quote_esc_str($pres->getRefillDate()) : 'NULL';
		$comment = $pres->getComment() ? quote_esc_str($pres->getComment()) : 'NULL';
		$billLine = $pres->getBill() != null && $pres->getBill()->getId() ? (is_array($pres->getBill()->getId()) ? "'".implode(",", $pres->getBill()->getId()). "'" : $pres->getBill()->getId() ): "NULL";
		
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE patient_regimens_data SET `comment`=$comment,  drug_id = " . $pres->getDrug()->getId() . ", batch_id=" . ($pres->getBatch() != null ? $pres->getBatch()->getId() : "NULL") . ", refill_date=$refill_date, duration='" . $pres->getDuration() . "', quantity=" . $pres->getQuantity() . ", `status`='filled', filled_on=NOW(), filled_by='" . $pres->getFilledBy()->getId() . "', bill_line_id=$billLine WHERE `status`<>'filled' AND id = " . $pres->getId();
			$pat = (new PrescriptionDAO())->getPrescriptionByCode($pres->getCode(), FALSE, $pdo)->getPatient();

			$deplete = null;
			$dispense = null;

			if ($pres->getBatch() != null) {
				$dispense = (new DrugDAO())->dispenseDrug($pres->getDrug(), $pres->getQuantity(), $pres->getBatch(), $pat, $pdo);
				$deplete = (new DrugBatchDAO())->depleteStock($pres->getBatch(), $pres->getQuantity(), $pdo);
			}

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			//if there was a batch to deplete from and dispense
			if ($pres->getBatch() != null && $deplete !== null && $dispense !== null && $stmt->rowCount() == 1) {
				$status = TRUE;
			} else if ($pres->getBatch() == null) {
				//what's happening here? that batch would be null ?
				$status = TRUE;
			} else {
				$status = FALSE;
			}
		} catch (PDOException $e) {
			errorLog($e);
			$status = FALSE;
		}
		return $status;
	}

	function completePrescription($pres, $pdo = null)
	{//complete the individual regimen lines
		$status = FALSE;
		$cancelled_Date = "AND `cancelled_on`='NULL'";
		$cancelledBy = "AND `cancelled_by`='NULL'";
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE patient_regimens_data SET `status`='completed', completed_on=NOW(), completed_by='" . $pres->getCompletedBy()->getId() . "' WHERE  `status`!='cancelled' AND  id = " . $pres->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$status = TRUE;
			}
		} catch (PDOException $e) {
			errorLog($e);
			$status = FALSE;
		}
		return $status;
	}

	function cancelPrescription($pres, $pdo = null)
	{
		// $pres = new PrescriptionData();
		if ($pres->getCancelledBy() == null) return FALSE;
		$staff_id = $pres->getCancelledBy()->getId();
		$staff = (new StaffDirectoryDAO())->getStaff($staff_id, false, $pdo);
		$parent_pres = (new PrescriptionDAO())->getPrescriptionByCode($pres->getCode(), FALSE, $pdo);
		$patient = $parent_pres->getPatient();
		$cancelReason = !is_blank($pres->getCancelNote()) ? quote_esc_str($pres->getCancelNote()) : "NULL";
		$status = FALSE;
		try {
			//start a transaction
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$sql = "UPDATE patient_regimens_data SET `status`='cancelled',cancelled_on=NOW(), cancelled_by='" . $pres->getCancelledBy()->getId() . "', cancel_note=$cancelReason WHERE id = " . $pres->getId();
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$bill = TRUE;
			if ($pres->getDrug() != null && ($pres->getStatus() == "filled" || $pres->getStatus() == "completed")) {
				$qty = $pres->getQuantity();
				$drug_price = (new InsuranceItemsCostDAO())->getItemPriceByCode($pres->getDrug()->getCode(), $patient->getId(), TRUE, $pdo);
				$amount = $drug_price * $qty;
				$overflow = $pres->getQuantity() - $qty;
				
				$BATCH_DAO = new DrugBatchDAO();
				$batch = $BATCH_DAO->getBatch($pres->getBatch()->getId(), $pdo);
				$batch->setQuantity($qty);
				$BATCH_DAO->stockUp($batch, $pdo);
				// for cancellation of prescription

				$patient = (new PatientDemographDAO())->getPatient($patient->getId(), FALSE, $pdo);
				// Restore the dispensed drug batch
				$disp = (new DispensedDrugs())->setDrug($pres->getDrug())->setPatient($patient)->setQuantity($qty)->setBatch($batch)->setQuantityOverflow($overflow)->setType("reversal")//make sure this batch is not expired during usage
				->setBilledTo($patient->getScheme())->setPharmacist((new StaffDirectoryDAO())->getStaff($_SESSION["staffID"], FALSE, $pdo));

				$item = (new DispensedDrugsDAO())->add($disp, $pdo);
				if ($item !== null) {
					$status = TRUE;
				}
				
				// check is patient has been transferred ..
				$checkBill = (new BillDAO())->getTransferCreditOnly($pres->getBill()[0]->getId(), true, $pdo);
				$billTransf = (new BillDAO())->checkBill($pres->getBill()[0]->getId(), true, $pdo);
				if ($pres->getBill() !== null) {
					//$pres = new PrescriptionData();
					//get the line charged for the item and undo the related bill lines
					
					(new BillDAO())->cancelRelatedItems($patient->getId(), $pres->getDrug()->getCode(), $pres->getFilledOn(), $pdo);
					
					if(is_array($pres->getBill()) && count($pres->getBill())==2) {
						$b1 = $pres->getBill()[0];
						$b2 = $pres->getBill()[1];
						$checkBill1 = (new BillDAO())->getTransferCreditOnly($b1->getBill()->getId(), true, $pdo);
						$billTransf1 = (new BillDAO())->checkBill($b1->getBill()->getId(), true, $pdo);
						if ($billTransf1 && $checkBill1 == null){
							$pdo->rollBack();
							return false;
						}
						$bil = new Bill();
						$bil->setPatient($patient);
						$bil->setDescription("Prescription Cancellation: " . (($pres->getDrug() != null) ? $pres->getDrug()->getName() : $pres->getGeneric()->getName()));
						$bil->setItem($pres->getDrug());
						$bil->setSource((new BillSourceDAO())->findSourceById(2, $pdo));
						$bil->setTransactionType("reversal");
						$bil->setTransactionDate(date("Y-m-d H:i:s"));
						$bil->setCancelledOn(date("Y-m-d H:i:s"));
						$bil->setCancelledBy((new StaffDirectory($_SESSION['staffID'])));
						$bil->setDueDate($b1->getTransactionDate());
						$bil->setAmount(0 - ($b1->getAmount()));
						$bil->setDiscounted(null);
						$bil->setDiscountedBy(null);
						$bil->setClinic($staff->getClinic());
						$bil->setBilledTo($b1->getBilledTo());
						$bil->setParent($b1);
						$bil->setActiveBill('not_active');
						$bil->setCostCentre((new PrescriptionDAO())->getPrescriptionByCode($pres->getCode(), false, $pdo)->getServiceCentre() !== null ? (new PrescriptionDAO())->getPrescriptionByCode($pres->getCode(), false, $pdo)->getServiceCentre()->getCostCentre() : null);
						$parent = (is_null($checkBill1)) ? $bil->setParent($b1->getBill()) : $bil->setParent($checkBill1);
						$bil->setParent($parent->getParent());
						
						$bill1 = (new BillDAO())->addBill($bil, $b1->getQuantity(), $pdo, null);
						
						$checkBill2 = (new BillDAO())->getTransferCreditOnly($b2->getBill()->getId(), true, $pdo);
						$billTransf2 = (new BillDAO())->checkBill($b2->getBill()->getId(), true, $pdo);
						if ($billTransf2 && $checkBill2 == null){
							$pdo->rollBack();
							return false;
						}
						$bil = new Bill();
						$bil->setPatient($patient);
						$bil->setDescription("Prescription Cancellation: " . (($pres->getDrug() != null) ? $pres->getDrug()->getName() : $pres->getGeneric()->getName()));
						$bil->setItem($pres->getDrug());
						$bil->setSource((new BillSourceDAO())->findSourceById(2, $pdo));
						$bil->setTransactionType("reversal");
						$bil->setTransactionDate(date("Y-m-d H:i:s"));
						$bil->setCancelledOn(date("Y-m-d H:i:s"));
						$bil->setCancelledBy((new StaffDirectory($_SESSION['staffID'])));
						$bil->setDueDate($b2->getTransactionDate());
						$bil->setAmount(0 - ($b2->getAmount()));
						$bil->setDiscounted(null);
						$bil->setDiscountedBy(null);
						$bil->setClinic($staff->getClinic());
						$bil->setBilledTo($b2->getBilledTo());
						$bil->setParent($b2);
						$bil->setActiveBill('not_active');
						$bil->setCostCentre((new PrescriptionDAO())->getPrescriptionByCode($pres->getCode(), false, $pdo)->getServiceCentre() !== null ? (new PrescriptionDAO())->getPrescriptionByCode($pres->getCode(), false, $pdo)->getServiceCentre()->getCostCentre() : null);
						$parent = (is_null($checkBill2)) ? $bil->setParent($b2->getBill()) : $bil->setParent($checkBill2);
						$bil->setParent($parent->getParent());
						
						$bill2 = (new BillDAO())->addBill($bil, $b2->getQuantity(), $pdo, null);
						
						if ($bill1 !== null && $bill2 !== null) {
							$pdo->commit();
							return true;
						} else {
							$pdo->rollBack();
							return false;
						}
					} else {
						
						if ($billTransf && $checkBill == null){
							$pdo->rollBack();
							return false;
						}
						$bil = new Bill();
						$bil->setPatient($patient);
						$bil->setDescription("Prescription Cancellation: " . (($pres->getDrug() != null) ? $pres->getDrug()->getName() : $pres->getGeneric()->getName()));
						$bil->setItem($pres->getDrug());
						$bil->setSource((new BillSourceDAO())->findSourceById(2, $pdo));
						$bil->setTransactionType("reversal");
						$bil->setTransactionDate(date("Y-m-d H:i:s"));
						$bil->setCancelledOn(date("Y-m-d H:i:s"));
						$bil->setCancelledBy((new StaffDirectory($_SESSION['staffID'])));
						$bil->setDueDate($pres->getBill()[0]->getTransactionDate());
						$bil->setAmount(0 - ($pres->getBill()[0]->getAmount()));
						$bil->setDiscounted(null);
						$bil->setDiscountedBy(null);
						$bil->setActiveBill('not_active');
						$bil->setClinic($staff->getClinic());
						$bil->setBilledTo($pres->getBill()[0]->getBilledTo());
						$bil->setCostCentre((new PrescriptionDAO())->getPrescriptionByCode($pres->getCode(), false, $pdo)->getServiceCentre() !== null ? (new PrescriptionDAO())->getPrescriptionByCode($pres->getCode(), false, $pdo)->getServiceCentre()->getCostCentre() : null);
						$parent = (is_null($checkBill)) ? $bil->setParent($pres->getBill()[0]) : $bil->setParent($checkBill);
						$bil->setParent($parent->getParent());
						
						$bill = (new BillDAO())->addBill($bil, $pres->getBill()[0]->getQuantity(), $pdo, null);
						if ($bill !== null) {
							$pdo->commit();
							return true;
						} else {
							$pdo->rollBack();
							return false;
						}
					}
					
					
				} else if($pres->getBill()==null){
					//we didn't charge: we either used a token or antenatal package
					
					//consider the antenatal scenario first
					require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/AntenatalEnrollmentDAO.php';
					$activeAntenatalInstance = (new AntenatalEnrollmentDAO())->getActiveInstance($patient->getId(), FALSE, $pdo);
					if($activeAntenatalInstance !== null){
						//error_log("HERE is an antenatal patient");
						//if the patient has is enrolled into antenatal and the package has the items covered
						//yay!!! we know the item
						
						//$pres = new PrescriptionData();
						
						require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AntenatalPackageItem.php';
						require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
						require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
						require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackagesDAO.php';
						$thisItemCode = $pres->getDrug()->getCode();
						$itemsCodes = [];
						
						$patientTokens = (new AntenatalPackagesDAO())->get($activeAntenatalInstance->getPackage()->getId(), $pdo)->getItems();
						foreach($patientTokens as $token){
							//$token = new AntenatalPackageItem();
							$itemsCodes[$token->getItemCode()] = $token->getUsage();
						}
						
						//if(in_array($thisItemCode, $itemsCodes)){
						if(isset($itemsCodes[$thisItemCode])){
							$billQuantity = (int)$pres->getQuantity();
							$item_type = getAntenatalItemType($thisItemCode);
							// it's a reversal
							(new PatientAntenatalUsages())->setPatient($patient)->setItemCode($thisItemCode)->setItem($pres->getDrug()->getId())->setType($item_type)->setAntenatal($activeAntenatalInstance)->setUsages(parseNumber(0 - $billQuantity))->setDateUsed(date(MainConfig::$mysqlDateTimeFormat))->add($pdo);
							$pdo->commit();
							return true;
						}
						$pdo->rollBack();
						return false;
					}
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PackageTokenUsage.php';
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PackageTokenDAO.php';
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PackageTokenUsageDAO.php';
					$thisItemCode = $pres->getDrug()->getCode();
					$itemsCodes = [];
					//$itemTokens = [];
					$patientTokens = (new PackageTokenDAO())->forPatient($patient->getId(), $pdo);
					foreach ($patientTokens as $token) {
						//$token = new PackageToken();
						//$itemTokens[] = array('code'=>$token->getItemCode(),'quantity_left'=>$token->getRemainingQuantity());
						$itemsCodes[] = $token->getItemCode();
					}
					if (in_array($thisItemCode, $itemsCodes)) {
						//we used a token:package
						$itemQuantity = (new PackageTokenDAO())->forPatientItem($thisItemCode, $patient->getId(), $pdo);
						$availableTokenItemQty = $itemQuantity->getRemainingQuantity();
						$billQuantity = $pres->getQuantity();
						
						$itemQuantity->setRemainingQuantity($availableTokenItemQty+$billQuantity)->setPatient($patient)->update($pdo);
						(new PackageTokenUsage())->setItemCode($thisItemCode)->setPatient($patient)->setQuantity(0-$billQuantity)->add($pdo);
						
						//we have reduced token, so we need not charge this patient, exit from this function
						$pdo->commit();
						return true;
					} else if (!in_array($thisItemCode, $itemsCodes)){
						$pdo->commit();
						return true;
					}
				}
				$pdo->rollBack();
				return false;
			}
			if ($stmt->rowCount() <= 0 || $bill === null) {
				error_log("Couldn't cancel prescription");
				$pdo->rollBack();
			} else {
				$status = true;
				$pdo->commit();
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
		}
		return $status;
	}

	function getCompletedPrescriptionsByDateRange($page, $pageSize, $pharmacy, $getFull = FALSE, $startdate = null, $enddate = null, $pdo = null)
	{
		$filter = ($pharmacy != null ? " AND r.service_centre_id=$pharmacy" : "");
		$startdate = ($startdate === null || $startdate == '') ? date('Y-m-d H:i:s') : $startdate;
		$enddate = ($enddate === null || $enddate == '') ? date('Y-m-d H:i:s') : $enddate;
		$sql = "SELECT r.* FROM patient_regimens_data d LEFT JOIN patient_regimens r ON d.group_code=r.group_code WHERE d.status IN ('completed') AND d.filled_by IS NOT NULL AND DATE(r.when) BETWEEN DATE('$startdate') and DATE('$enddate') $filter";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$pds = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//todo: add drug generic and code
			$sql = "SELECT d.*, r.`when`, r.patient_id, CONCAT_WS(' ', pd.fname, pd.mname, pd.lname) AS patientName, s.scheme_name, g.name AS generic_name, drugs.name AS drug_name, drugs.billing_code AS drug_code FROM patient_regimens_data d LEFT JOIN patient_regimens r ON r.group_code=d.group_code LEFT JOIN patient_demograph pd ON pd.patient_ID=r.patient_id LEFT JOIN insurance i ON i.patient_id=r.patient_id LEFT JOIN insurance_schemes s ON s.id=i.insurance_scheme LEFT JOIN drugs ON drugs.id=d.drug_id LEFT JOIN drug_generics g ON d.drug_generic_id=g.id WHERE d.status IN ('completed') AND d.filled_by IS NOT NULL AND DATE(r.when) BETWEEN DATE('$startdate') and DATE('$enddate') $filter ORDER BY r.when ASC LIMIT $offset, $pageSize";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				/*$pd = new PrescriptionData();
				$pd->setId($row['id']);
				$pd->setCode((new PrescriptionDAO())->getPatientPrescriptionByCode($row['group_code'], TRUE, $pdo));
				$pd->setBatch(trim($row['batch_id']) != "" ? (new DrugBatchDAO())->getBatch($row['batch_id'], $pdo) : null);

				if ($getFull) {
					$drug = $row['drug_id'] === null ? null : (new DrugDAO())->getDrug($row['drug_id'], TRUE, $pdo);
					if ($drug === null) {
						$gen = (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], $getFull, $pdo);
					} else {
						$gen = $drug->getGeneric();
					}
					$dao = new StaffDirectoryDAO();
					$req = $dao->getStaff($row['requested_by'], FALSE, $pdo);
					$mod = $dao->getStaff($row['modified_by'], FALSE, $pdo);
					$fil = $dao->getStaff($row['filled_by'], FALSE, $pdo);
					$com = $dao->getStaff($row['completed_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$drug = ($row['drug_id'] === null) ? null : new Drug($row['drug_id']);
					$gen = new DrugGeneric($row['drug_generic_id']);
					$req = new StaffDirectory($row['requested_by']);
					$mod = new StaffDirectory($row['modified_by']);
					$fil = new StaffDirectory($row['filled_by']);
					$com = new StaffDirectory($row['completed_by']);
					$hosp = new Clinic($row['hospid']);
				}
				$pd->setDrug($drug);
				$pd->setGeneric($gen);
				$pd->setQuantity($row['quantity']);
				$pd->setDose($row['dose']);
				$pd->setDuration($row['duration']);
				$pd->setComment($row['comment']);
				$pd->setFrequency($row['frequency']);
				$pd->setRefillable($row['refillable']);
				$pd->setStatus('open');
				$pd->setRequestedBy($req);
				$pd->setModifiedBy($mod);
				$pd->setFilledBy($fil);
				$pd->setFilledOn($row['filled_on']);
				$pd->setCompletedBy($com);
				$pd->setCompletedOn($row['completed_on']);
				$pd->setCancelledBy((new StaffDirectoryDAO())->getStaff($row['cancelled_by'], TRUE, $pdo));
				$pd->setCancelledOn($row['cancelled_on']);
				$pd->setHospital($hosp);
				$pds[] = $pd;*/
				$pds[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$pds = [];
		}
		$results = (object)null;
		$results->data = $pds;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function getUnfulfilledPrescriptions($page, $pageSize, $start=null, $stop=null, $pdo = null)
	{
		$f = ($start == null) ? date("Y-m-d") : $start;
		$t = ($stop == null) ? date("Y-m-d") : $stop;
		$condition = " d.status IN ('filled') AND pd.active IS TRUE AND DATE(r.when) BETWEEN DATE('$f') AND DATE('$t')";
		$sql = "SELECT r.* FROM patient_regimens_data d LEFT JOIN patient_regimens r ON d.group_code=r.group_code LEFT JOIN patient_demograph pd ON pd.patient_ID=r.patient_id WHERE $condition";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			errorLog("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$pds = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//todo: add drug generic and code
			$sql = "SELECT d.*, r.`when`, r.patient_id, CONCAT_WS(' ', pd.fname, pd.mname, pd.lname) AS patientName, CONCAT_WS(' ', sd.firstname, sd.lastname) AS requestedByName, s.scheme_name, g.name AS generic_name, g.form, drugs.name AS drug_name, drugs.billing_code AS drug_code FROM patient_regimens_data d LEFT JOIN patient_regimens r ON r.group_code=d.group_code LEFT JOIN patient_demograph pd ON pd.patient_ID=r.patient_id LEFT JOIN staff_directory sd ON sd.staffId=r.requested_by LEFT JOIN insurance i ON i.patient_id=r.patient_id LEFT JOIN insurance_schemes s ON s.id=i.insurance_scheme LEFT JOIN drugs ON drugs.id=d.drug_id LEFT JOIN drug_generics g ON d.drug_generic_id=g.id WHERE $condition AND pd.active IS TRUE ORDER BY r.when ASC LIMIT $offset, $pageSize";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pds[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$pds = [];
		}
		$results = (object)null;
		$results->data = $pds;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function exportCompletedPrescriptionsByDateRange($page, $pageSize, $pharmacy, $getFull = FALSE, $startdate = null, $enddate = null, $pdo = null)
	{
		$filter = ($pharmacy != null ? " AND r.service_centre_id=$pharmacy" : "");
		$startdate = ($startdate === null || $startdate == '') ? date('Y-m-d H:i:s') : $startdate;
		$enddate = ($enddate === null || $enddate == '') ? date('Y-m-d H:i:s') : $enddate;
		$sql = "SELECT * FROM patient_regimens_data d LEFT JOIN patient_regimens r ON d.group_code=r.group_code WHERE d.status IN ('completed') AND d.filled_by IS NOT NULL AND DATE(r.when) BETWEEN DATE('$startdate') and DATE('$enddate') $filter";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			errorLog("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$pds = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT d.* FROM patient_regimens_data d LEFT JOIN patient_regimens r ON r.group_code=d.group_code WHERE d.status IN ('completed') AND d.filled_by IS NOT NULL AND DATE(r.when) BETWEEN DATE('$startdate') and DATE('$enddate') $filter ORDER BY r.when ASC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$report = (object)null;
				$patient = null;
				$patient = (new PrescriptionDAO())->getPatientPrescriptionByCode($row['group_code'], TRUE, $pdo)->getPatient();

				$report->Date = date('jS M, Y', strtotime((new PrescriptionDAO())->getPatientPrescriptionByCode($row['group_code'], FALSE, $pdo)->getWhen()));
				$report->Patient = $patient->getFullname();
				$report->PatientID = $patient->getId();
				$report->Drug = ($row['drug_id'] == null) ? (new DrugGenericDAO())->getGeneric($row['drug_generic_id'], TRUE, $pdo)->getName() : (new DrugDAO())->getDrug($row['drug_id'], TRUE, $pdo)->getName();
				$report->Quantity = $row['quantity'];
				$report->Scheme = $patient->getScheme()->getName();
				$report->Amount = (new InsuranceItemsCostDAO())->getItemPriceByCode((new DrugDAO())->getDrug($row['drug_id'], TRUE, $pdo)->getCode(), $patient->getId(), TRUE, $pdo);

				$pds[] = $report;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$pds = [];
		}
		$results = (object)null;
		$results->data = $pds;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}
}
