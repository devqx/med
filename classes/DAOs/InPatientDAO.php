<?php

/**
 * Description of InPatientDAO
 *
 * @author pauldic
 */
class InPatientDAO
{

	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Ward.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bed.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InPatient.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTask.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientCareMember.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabourEnrollment.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabourEnrollmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BedDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientCareMemberDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/WardDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.admissions.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function addInPatient($a, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			try {
				$pdo->beginTransaction();
			} catch (Exception $ex) {
				//Transaction Already Started
			}
			$iP = $this->getActiveInPatient($a->getPatient()->getId(), FALSE, $pdo);

			if ($iP != null) {
				$pdo->rollBack();
				return null;
			}
			$ward = ($a->getWard() != null) ? $a->getWard()->getId() : "NULL";

			//fixme: prevent this statement if the patient is not discharged yet,
			//let the app prevent that
			$sql = "INSERT INTO in_patient SET patient_id = " . $a->getPatient()->getId() . ", date_admitted=NOW(), admitted_by='" . $a->getAdmittedBy()->getId() . "', reason='" . escape($a->getReason()) . "', anticipated_discharge_date='" . $a->getAnticipatedDischargeDate() . "', hospital_id = '" . $a->getClinic()->getId() . "', ward_id=" . $ward;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() > 0) {
				$a->setId($pdo->lastInsertId());
				$pcms = $a->getPatientCareMembers();

				$adm = new InPatient($a->getId());
				$adm->setPatient($a->getPatient());

				$pcms[0]->setInPatient($adm);
				$pcs = (new PatientCareMemberDAO())->addPatientCareMember($pcms, $pdo);

				if (count($pcms) === count($pcs)) {
					$pdo->commit();
					$queue = new PatientQueue();
					$queue->setType("Bed");
					$queue->setPatient($a->getPatient());
					(new PatientQueueDAO())->addPatientQueue($queue, $pdo);
				} else {
					$pdo->rollBack();
					return null;
				}
			} else {
				$a = null;
				$pdo->rollBack();
				return null;
			}

			$stmt = null;
		} catch (PDOException $e) {
			$a = null;
			errorLog($e);
			$pdo->rollBack();
		} catch (Exception $e) {
			$a = null;
			$pdo->rollBack();
			errorLog($e);
		}
		return $a;
	}

	function getInPatient($aid, $getFull = FALSE, $pdo = null)
	{
		$adm = new InPatient();
		if (is_null($aid) || is_blank($aid)) {
			return null;
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM in_patient WHERE id=" . $aid;
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$bed = (new BedDAO())->getBed($row['bed_id'], TRUE, $pdo);
					$admittedBy = (new StaffDirectoryDAO())->getStaff($row['admitted_by'], FALSE, $pdo);
					$dischargedBy = (new StaffDirectoryDAO())->getStaff($row['discharged_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospital_id'], FALSE, $pdo);
					$ward = (new WardDAO())->getWard($row['ward_id'], FALSE, $pdo);
					$diagnoses = (new PatientDiagnosisDAO())->getInPatientDiagnoses($row['id'], $pdo);
					$medications = (new PrescriptionDataDAO())->getLastAdministrationTime($row['id'], $pdo);
					$appointments = (new AppointmentDAO())->getAppointmentByGroup($row['appointment_id'], TRUE, $pdo);
					$dischargedMedication = (new PrescriptionDAO())->getPrescriptionByCode($row['medication_code'], FALSE, $pdo);
					$bills = (new BillDAO())->getInPatientBill($row['id'], FALSE, $pdo);
				} else {
					$dischargedMedication = (new PrescriptionDAO())->getPrescriptionByCode($row['medication_code'], FALSE, $pdo);
					$appointments = (new AppointmentDAO())->getAppointmentByGroup($row['appointment_id'], TRUE, $pdo);
					$pat = new PatientDemograph($row['patient_id']);
					$bed = new Bed($row['bed_id']);
					$admittedBy = new StaffDirectory($row['admitted_by']);
					$dischargedBy = new StaffDirectory($row['discharged_by']);
					$hosp = new Clinic($row['hospital_id']);
					$ward = new Ward($row['ward_id']);
					$diagnoses = [];
					$bills = [];
				}
				$adm->setId($row["id"])->setPatient($pat)
					->setStatus($row['status'])
					->setBed($bed)//Obj
					->setDateAdmitted($row["date_admitted"])->setAdmittedBy($admittedBy)//Obj
					->setReason($row["reason"])->setAnticipatedDischargeDate($row["anticipated_discharge_date"])->setDateDischarged($row["date_discharged"])->setDischargeNote($row["discharge_note"])->setDischargedBy($dischargedBy)//Obj
					->setClinic($hosp)->setBillStatus($row["bill_status"])->setWard($ward)
					->setDiagnoses($diagnoses)
					->setBills($bills)
					->setNextAppointment($appointments)
					->setNextMedication($dischargedMedication)
					->setLabourInstance($row['labour_enrollment_id'] ? new LabourEnrollment($row['labour_enrollment_id']) : NULL );

				return $adm;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	
	function getInPatientFlat($aid, $getFull = FALSE, $pdo = null)
	{
		$adm = new InPatient();
		if (is_null($aid) || is_blank($aid)) {
			return null;
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM in_patient WHERE id=" . $aid;
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatientMin($row['patient_id'], FALSE, $pdo);
					$bed = (new BedDAO())->getBed($row['bed_id'], TRUE, $pdo);
					$admittedBy = (new StaffDirectoryDAO())->getStaff($row['admitted_by'], FALSE, $pdo);
					$dischargedBy = (new StaffDirectoryDAO())->getStaff($row['discharged_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospital_id'], FALSE, $pdo);
					$ward = (new WardDAO())->getWard($row['ward_id'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$bed = new Bed($row['bed_id']);
					$admittedBy = new StaffDirectory($row['admitted_by']);
					$dischargedBy = new StaffDirectory($row['discharged_by']);
					$hosp = new Clinic($row['hospital_id']);
					$ward = new Ward($row['ward_id']);
				}
				$adm->setId($row["id"])->setPatient($pat)
					->setStatus($row['status'])
					->setBed($bed)//Obj
					->setDateAdmitted($row["date_admitted"])->setAdmittedBy($admittedBy)//Obj
					->setReason($row["reason"])->setAnticipatedDischargeDate($row["anticipated_discharge_date"])->setDateDischarged($row["date_discharged"])->setDischargeNote($row["discharge_note"])->setDischargedBy($dischargedBy)//Obj
					->setClinic($hosp)->setBillStatus($row["bill_status"])->setWard($ward)
					->setLabourInstance($row['labour_enrollment_id'] ? new LabourEnrollment($row['labour_enrollment_id']) : NULL );
				
				return $adm;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function changeWard($ipInstance, $ward, $pdo)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE in_patient SET ward_id=" . $ward->getId() . " WHERE id=" . $ipInstance->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return true;
			}
			return false;
		} catch (PDOException $e) {
			errorLog($e);
			return false;
		}
	}

	function getActiveInPatient($pid, $getFull = FALSE, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM in_patient WHERE patient_id=$pid AND date_discharged IS NULL";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return $this->getInPatient($row['id'], $getFull, $pdo);
			}
			return null;
		} catch (PDOException $e) {
			$adm = $stmt = null;
			errorLog($e);
		}
		return $adm;
	}

	function getInActiveUnclaimedInPatient($pid, $getFull = FALSE, $pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT ip.date_admitted, ip.id, ip.reason FROM in_patient ip WHERE patient_id=$pid AND date_discharged IS NOT NULL AND claimed is false";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				//$data[] = $this->getInPatient($row['id'], $getFull, $pdo);
				$data[] = (object)$row;
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
		}
		return [];
	}
function getInPatientsForClaim($pid, $getFull = FALSE, $pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT ip.date_admitted, ip.id, ip.reason FROM in_patient ip WHERE patient_id=$pid AND date_discharged IS NOT NULL AND claimed in (true,false)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				//$data[] = $this->getInPatient($row['id'], $getFull, $pdo);
				$data[] = (object)$row;
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
		}
		return [];
	}

	function getMyInPatient($sid, $getFull = FALSE, $block=null, $ward = null, $page = 0, $pageSize = 10, $h_id=null)
	{
		$blockFilter = ($block != null) ? " AND bl.id = " . $block : "";
		$wardFilter = ($ward != null) ? " AND ip.ward_id = " . $ward : "";
        $healthStateFilter = ($h_id != null) ? " AND ip.health_state_id = $h_id ":"";

		$total = 0;
		//$sql = "SELECT ip.* FROM in_patient ip LEFT JOIN patient_care_member pcm ON pcm.in_patient_id=ip.id WHERE ip.date_discharged_full IS NULL AND date_discharged IS NULL AND pcm.care_member_id = '$sid'$wardFilter";
		$sql = "SELECT ip.*, CONCAT_WS(' ', pd.lname, pd.mname, pd.fname) as patientName, pd.sex, pd.active, sd.username, CONCAT_WS(' ', sd.lastname, sd.firstname) AS staffName, b.name AS bedName, w.name AS wardName, r.name AS roomName FROM in_patient ip LEFT JOIN patient_demograph pd ON ip.patient_id=pd.patient_ID LEFT JOIN staff_directory sd ON sd.staffId=ip.admitted_by LEFT JOIN bed b ON b.id=ip.bed_id LEFT JOIN ward w ON w.id=ip.ward_id LEFT JOIN room r ON r.id=b.room_id LEFT JOIN `block` bl ON bl.id=w.block_id LEFT JOIN patient_care_member pcm ON pcm.in_patient_id=ip.id WHERE pd.active IS TRUE AND ip.date_discharged_full IS NULL AND date_discharged IS NULL AND pcm.care_member_id = '$sid'{$blockFilter}{$wardFilter}{$healthStateFilter}";

		    //error_log($sql);
		try {
			//$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $pdo = $this->conn->getPDO();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$adms = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			//            $sql = "SELECT DISTINCT ip.* FROM in_patient ip LEFT JOIN patient_care_member pcm ON pcm.in_patient_id=ip.id LEFT JOIN staff_care_team sct ON sct.staff_id=pcm.care_member_id OR sct.team_id=pcm.care_team_id LEFT JOIN staff_directory sd ON sd.staffId ='" . $sid . "'  WHERE  ip.date_discharged IS NULL AND  pcm.status='Active'";
//            $sql = "SELECT ip.* FROM in_patient ip LEFT JOIN patient_care_member pct ON pct.in_patient_id=ip.id LEFT JOIN care_team ct ON ct.id=pct.care_team_id LEFT JOIN staff_care_team sct ON sct.team_id=ct.id LEFT JOIN staff_directory s ON (s.staffId=sct.staff_id OR s.staffId =pct.care_member_id) WHERE s.staffId='" . $sid . "' AND ip.date_discharged IS NULL";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$adms[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$adms = [];
			errorLog($e);
		}
		$results = (object)null;
		$results->data = $adms;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function getUncomputedBillInPatients($getFull = FALSE, $page, $pageSize, $pdo = null)
	{
		$adms = array();
		$sql = "SELECT ip.* FROM in_patient ip WHERE ip.bill_status = 'Uncomputed' AND ip.date_discharged_full IS NOT NULL";
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
		
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$adms[] = $this->getInPatientFlat($row['id'], TRUE, $pdo);
			}
			$stmt = null;
			
		} catch (PDOException $e) {
			$stmt = null;
			$adms = [];
			errorLog($e);
		}
		$results = (object)null;
		$results->data = $adms;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
		
	}

	function getInactiveInPatients($getFull = FALSE, $page = 0, $pageSize = 10, $patientId = null, $dateStart = null, $dateEnd = null, $pdo = null)
	{
		$total = 0;
		$patient = !is_null($patientId) ? " AND ip.patient_id=" . $patientId : "";
		$dates = !is_null($dateStart) && !is_null($dateEnd) ? " AND (DATE(date_admitted) BETWEEN '$dateStart' AND '$dateEnd' OR DATE(date_discharged) BETWEEN '$dateStart' AND '$dateEnd')" : "";
		if (!AdmissionSetting::$ipMedicationTaskRealTimeDeduct) {
			$sql = "SELECT CONCAT_WS(' ', pd.fname, pd.mname, pd.lname) AS patientName, ip.* FROM in_patient ip LEFT JOIN patient_demograph pd ON ip.patient_id=pd.patient_ID WHERE date_discharged_full IS NOT NULL AND date_discharged_full IS NOT NULL {$patient}{$dates} ORDER BY date_discharged DESC";
		} else {
			$sql = "SELECT CONCAT_WS(' ', pd.fname, pd.mname, pd.lname) AS patientName, ip.* FROM in_patient ip LEFT JOIN patient_demograph pd ON ip.patient_id=pd.patient_ID WHERE `status` = 'Discharged'{$patient}{$dates} ORDER BY date_discharged DESC";
		}
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

		$admissions = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$admissions[] = (object)$row;
//                $admissions[] = $this->getInPatient($row['id'], $getFull, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$admissions = [];
			$stmt = null;
			errorLog($e);
		}
		$results = (object)null;
		$results->data = $admissions;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}


	function getIncompletelyDischargedInPatients($getFull = FALSE, $page = 0, $pageSize = 10, $pdo = null)
	{
		$sql = "SELECT ip.*, pd.active, sc.scheme_name, PATIENT_SCHEME(pd.patient_ID) AS schemeName, CONCAT_WS(' ', pd.fname, pd.mname, pd.lname) AS patientName FROM in_patient ip LEFT JOIN patient_demograph pd ON pd.patient_ID=ip.patient_id LEFT JOIN insurance i ON i.patient_id=pd.patient_ID LEFT JOIN insurance_schemes sc ON i.insurance_scheme=sc.id WHERE pd.active IS TRUE AND date_discharged_full IS NULL AND date_discharged IS NOT NULL";
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
		$admissions = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				//$admissions[] = $this->getInPatient($row['id'], $getFull, $pdo);
				$admissions[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$admissions = [];
			$stmt = null;
		}
		$results = (object)null;
		$results->data = $admissions;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}


	function getActiveInPatients($getFull = FALSE, $block=null, $ward = null, $page = 0, $pageSize = 1000, $patientId=NULL, $h_id = null )
	{
		$blockFilter = ($block != NULL) ? " AND bl.id = ".$block : "";
		$wardFilter = ($ward != null) ? " AND ip.ward_id = " . $ward : "";
		$patientFilter = ($patientId != null) ? " AND ip.patient_id=$patientId":"";
        $healthStateFilter = ($h_id != null) ? " AND ip.health_state_id = $h_id ":"";

        error_log('Health State ID'. $healthStateFilter );

		$admissions = array();
		// $sql = "SELECT * FROM in_patient WHERE date_discharged_full IS NULL AND date_discharged IS NULL $wardFilter ORDER BY bed_id";
		$sql = "SELECT ip.*, CONCAT_WS(' ', pd.lname, pd.mname, pd.fname) as patientName, pd.sex, pd.active, PATIENT_SCHEME(pd.patient_ID) AS schemeName, sd.username, CONCAT_WS(' ', sd.lastname, sd.firstname) AS staffName, b.name AS bedName, w.name AS wardName, r.name AS roomName FROM in_patient ip LEFT JOIN patient_demograph pd ON ip.patient_id=pd.patient_ID LEFT JOIN staff_directory sd ON sd.staffId=ip.admitted_by LEFT JOIN bed b ON b.id=ip.bed_id LEFT JOIN ward w ON w.id=ip.ward_id LEFT JOIN room r ON r.id=b.room_id LEFT JOIN `block` bl ON bl.id=w.block_id WHERE pd.active IS TRUE AND ip.date_discharged_full IS NULL AND ip.date_discharged IS NULL {$blockFilter}{$wardFilter}{$patientFilter}{$healthStateFilter} ORDER BY ip.bed_id";
		//error_log($sql);
		$total = 0;
		try {
			//$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $pdo = $this->conn->getPDO();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				// $admissions[] = $this->getInPatient($row['id'], $getFull, $pdo);
				$admissions[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$admissions = [];
			$stmt = null;
		}
		$results = (object)null;
		$results->data = $admissions;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}


	function getInPatients($filterView = null, $getFull = FALSE, $ward = null, $dates = [], $page = 0, $pageSize = 10, $pdo = null)
	{
		$wardFilter = ($ward != null) ? "AND ward_id = " . $ward : "";
		$admissions = array();

		$date1 = $date2 = date("Y-m-d", time());
		if (count($dates) == 2) {
			$date1 = min($dates[0], $dates[1]);
			$date2 = max($dates[0], $dates[1]);
		}
		if ($filterView == "discharged") {
			$sql = "SELECT * FROM in_patient WHERE DATE(date_discharged) BETWEEN '$date1' AND '$date2' $wardFilter";
		} else if ($filterView == "admissions") {
			$sql = "SELECT * FROM in_patient WHERE DATE(date_admitted) BETWEEN '$date1' AND '$date2' $wardFilter";
		} else {
			//patients on current admissions
			$sql = "SELECT * FROM in_patient WHERE date_discharged_full IS NULL AND date_discharged IS NULL $wardFilter";
		}
		$sql .= " ORDER BY bed_id";
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

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= "  LIMIT $offset, $pageSize";
			//error_log($sql);

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$admissions[] = (object)$row;
				//$admissions[] = $this->getInPatient($row['id'], $getFull, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$admissions = [];
			$stmt = null;
		}
		$results = (object)null;
		$results->data = $admissions;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function getInPatientReport($filterView = null, $ward = null, $dates = [], $page = 0, $pageSize = 10, $pdo = null)
	{
		$wardFilter = ($ward != null) ? "AND ip.ward_id = " . $ward : "";
		$admissions = array();

		$date1 = $date2 = date("Y-m-d", time());
		if (count($dates) == 2) {
			$date1 = min($dates[0], $dates[1]);
			$date2 = max($dates[0], $dates[1]);
		}
		if ($filterView == "discharged") {
			$sql = "SELECT ip.*, CONCAT_WS(' ', pd.lname, pd.mname, pd.fname) as patientName, PATIENT_SCHEME(pd.patient_ID) AS schemeName, pd.sex, sd.username, CONCAT_WS(' ', sd.lastname, sd.firstname) AS staffName, b.name AS bedName, w.name AS wardName, r.name AS roomName FROM in_patient ip LEFT JOIN patient_demograph pd ON ip.patient_id=pd.patient_ID LEFT JOIN staff_directory sd ON sd.staffId=ip.admitted_by LEFT JOIN bed b ON b.id=ip.bed_id LEFT JOIN ward w ON w.id=ip.ward_id LEFT JOIN room r ON r.id=b.room_id WHERE pd.active IS TRUE AND DATE(date_discharged) BETWEEN '$date1' AND '$date2' $wardFilter";
			// $sql = "SELECT * FROM in_patient WHERE DATE(date_discharged) BETWEEN '$date1' AND '$date2' $wardFilter";
		} else if ($filterView == "admissions") {
			$sql = "SELECT ip.*, CONCAT_WS(' ', pd.lname, pd.mname, pd.fname) as patientName, PATIENT_SCHEME(pd.patient_ID) AS schemeName, pd.sex, sd.username, CONCAT_WS(' ', sd.lastname, sd.firstname) AS staffName, b.name AS bedName, w.name AS wardName, r.name AS roomName FROM in_patient ip LEFT JOIN patient_demograph pd ON ip.patient_id=pd.patient_ID LEFT JOIN staff_directory sd ON sd.staffId=ip.admitted_by LEFT JOIN bed b ON b.id=ip.bed_id LEFT JOIN ward w ON w.id=ip.ward_id LEFT JOIN room r ON r.id=b.room_id WHERE pd.active IS TRUE AND DATE(date_admitted) BETWEEN '$date1' AND '$date2' $wardFilter";
		} else {
			//patients on current admissions
			$sql = "SELECT ip.*, CONCAT_WS(' ', pd.lname, pd.mname, pd.fname) as patientName, PATIENT_SCHEME(pd.patient_ID) AS schemeName, pd.sex, sd.username, CONCAT_WS(' ', sd.lastname, sd.firstname) AS staffName, b.name AS bedName, w.name AS wardName, r.name AS roomName FROM in_patient ip LEFT JOIN patient_demograph pd ON ip.patient_id=pd.patient_ID LEFT JOIN staff_directory sd ON sd.staffId=ip.admitted_by LEFT JOIN bed b ON b.id=ip.bed_id LEFT JOIN ward w ON w.id=ip.ward_id LEFT JOIN room r ON r.id=b.room_id WHERE  pd.active IS TRUE AND date_discharged_full IS NULL AND date_discharged IS NULL $wardFilter";
		}
		$sql .= " ORDER BY ip.bed_id";
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

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= "  LIMIT $offset, $pageSize";

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$adm = (object)$row;
//                $adm->Date = $row["date_admitted"];
//                $adm->Patient = [(new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo)->getFullname(),$row['patient_id']] ;
//                $adm->Ward = (new WardDAO())->getWard($row['ward_id'], FALSE, $pdo)->getName();
//                $adm->Reason = $row["reason"];
//                $adm->By = (new StaffDirectoryDAO())->getStaff($row['admitted_by'], FALSE, $pdo)->getFullname();
//                $adm->Status = $row['status'];
				$admissions[] = $adm;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$admissions = [];
			$stmt = null;
		}
		$results = (object)null;
		$results->data = $admissions;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function getInboundInPatients($getFull = FALSE, $block=null, $ward = null, $page = 0, $pageSize = 10, $patientId=null, $h_id=null )
	{
		$blockFilter = ($ward != null) ? " AND bl.id = " . $block : "";
		$wardFilter = ($ward != null) ? " AND ip.ward_id = " . $ward : "";
		$patientFilter = ($patientId != null) ? " AND ip.patient_id=$patientId":"";
		$healthStateFilter = ($h_id != null) ? " AND ip.health_state_id = $h_id ":"";

		$sql = "SELECT ip.*, CONCAT_WS(' ', pd.lname, pd.mname, pd.fname) as patientName, pd.active, pd.sex, sd.username, CONCAT_WS(' ', sd.lastname, sd.firstname) AS staffName, b.name AS bedName, w.name AS wardName, r.name AS roomName FROM in_patient ip LEFT JOIN patient_demograph pd ON ip.patient_id=pd.patient_ID LEFT JOIN staff_directory sd ON sd.staffId=ip.admitted_by LEFT JOIN bed b ON b.id=ip.bed_id LEFT JOIN ward w ON w.id=ip.ward_id LEFT JOIN room r ON r.id=b.room_id LEFT JOIN `block` bl ON bl.id=w.block_id WHERE pd.active IS TRUE AND ip.date_discharged_full IS NULL AND ip.date_discharged IS NULL AND ip.bed_id IS NULL {$blockFilter}{$wardFilter}{$patientFilter}{$healthStateFilter}";

        error_log($sql);
		$total = 0;
		try {
			//$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo = $this->conn->getPDO();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;

		$admissions = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$admissions[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$admissions = [];
			$stmt = null;
			errorLog($e);
		}
		$results = (object)null;
		$results->data = $admissions;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	/**
	 *
	 * @param string $active will contain "*" to display both On admission and discharged other true for on admission and false for discharged
	 * @param bool $getFull
	 * @param null $pdo
	 * @return array
	 */
	function getInPatientsFiltered($active = "*", $getFull = FALSE, $pdo = null)
	{
		$admissions = array();
		$active = ($active === "*" ? "" : ($active === TRUE ? " WHERE date_discharged IS NULL " : " WHERE date_discharged IS NOT NULL "));
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT ip.*, w.name, r.name FROM in_patient ip LEFT JOIN bed b ON b.id=ip.bed_id LEFT JOIN room r ON r.id=b.room_id LEFT JOIN ward w ON w.id=r.ward_id $active ORDER BY -date_discharged DESC, -w.name DESC, -r.name DESC";

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$admissions[] = $this->getInPatient($row['id'], $getFull, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$admissions = [];
			$stmt = null;
			errorLog($e);
		}
		return $admissions;
	}

	function getInPatientInstances($pid, $getFull = FALSE, $pdo = null)
	{
		$instances = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM in_patient WHERE patient_id = $pid ORDER BY date_admitted";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$instances[] = $this->getInPatient($row['id'], $getFull, $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			return [];

		}
		return $instances;
	}


	function getInPatientInstancesSlim($pid, $getFull = FALSE, $pdo = null)
	{
		$instances = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM in_patient WHERE patient_id = $pid ORDER BY date_admitted";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$instances[] = (object)$row;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return [];

		}
		return $instances;
	}

	function getInPatientHistory($from = null, $to = null, $query = null, $getFull = FALSE, $pdo = null)
	{
		$admissions = array();
		if ($from !== null && $to !== null) {
			$q1 = " AND ip.date_discharged BETWEEN '" . $from . "' AND '" . $to . "' ";
			if ($query !== null) {
				$q1 = $q1 . " AND (pd.patient_ID LIKE '%" . $query . "%' OR pd.legacy_patient_id LIKE '%" . $query . "%' OR pd.fname LIKE '%" . $query . "%' OR pd.lname LIKE '%" . $query . "%' OR pd.mname LIKE '%" . $query . "%')";
			}
		} else {
			if ($query !== null) {
				$q1 = " AND (pd.patient_ID LIKE '%" . $query . "%' OR pd.legacy_patient_id LIKE '%" . $query . "%' OR pd.fname LIKE '%" . $query . "%' OR pd.lname LIKE '%" . $query . "%' OR pd.mname LIKE '%" . $query . "%')";
			}
		}

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT ip.*, w.name, r.name FROM in_patient ip LEFT JOIN bed b ON b.id=ip.bed_id LEFT JOIN room r ON r.id=b.room_id LEFT JOIN ward w ON w.id=r.ward_id LEFT JOIN patient_demograph pd on pd.patient_ID=ip.patient_id $q1 ORDER BY -date_discharged DESC, -w.name DESC, -r.name DESC";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$admissions[] = $this->getInPatient($row['id'], $getFull, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$admissions = [];
			$stmt = null;
			errorLog($e);
		}
		return $admissions;
	}

	function getInPatientsWithBed($reverse = FALSE, $getFull = FALSE, $pdo = null)
	{
		$admissions = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$reverse_ = $reverse ? '' : ' NOT';
			$sql = "SELECT * FROM in_patient WHERE bed_id IS $reverse_ NULL AND date_discharged IS NULL";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$admissions[] = $this->getInPatient($row['id'], $getFull, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$admissions = [];
			$stmt = null;
		}
//        error_log(count($admissions));
		return $admissions;
	}

	function getInPatientsInWard($wid, $getFull = FALSE, $pdo = null)
	{
		$admissions = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT ip.* FROM in_patient ip LEFT JOIN bed b ON b.id=ip.bed_id LEFT JOIN room r ON r.id=b.room_id LEFT JOIN ward w ON w.id=r.ward_id WHERE w.id=" . $wid . " AND date_discharged IS NULL";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$admissions[] = $this->getInPatient($row['id'], $getFull, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$admissions = [];
			$stmt = null;
		}
//        error_log(count($admissions));
		return $admissions;
	}

	//TODO: When you are ready to use
	function getInPatientSelectedParameter($sort = null, $ASC = null, $sqlPart = null, $getFull = FALSE, $pdo = null)
	{
		$admissions = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
//            $sql = "SELECT p.*, a.*, b.id, b.name, b.description, b.ward_id, w.name FROM in_patient a LEFT JOIN patient_demograph p ON a.patient_id = p.patient_id LEFT JOIN bedspaces b ON a.bed_id=b.id LEFT JOIN wards w ON w.id=b.ward_id WHERE a.discharged_by IS NULL AND a.date_discharged IS NULL GROUP BY a.patient_id ORDER BY w.id, a.bed_id DESC, a.date_admitted DESC";
//            **$sql = "SELECT p.*, a.*, b.id as bedSpaceId, b.name, b.bedtype_id, b.ward_id, b.available, b.description, b.hospid, w.name FROM in_patient a LEFT JOIN patient_demograph p ON a.patient_id = p.patient_id LEFT JOIN bedspaces b ON a.bed_id=b.id LEFT JOIN wards w ON w.id=b.ward_id WHERE a.discharged_by IS NULL AND a.date_discharged IS NULL GROUP BY a.patient_id ORDER BY w.id, a.bed_id DESC, a.date_admitted DESC";
			$sql = "SELECT p.*, a.*, getMedicamentCount(a.id) AS mCount, b.id AS bedSpaceId, b.name, b.bedtype_id, b.room_id, b.available, b.description, b.hospid, r.name, r.ward_id, w.name FROM in_patient a LEFT JOIN patient_demograph p ON a.patient_id = p.patient_id LEFT JOIN bedspaces b ON a.bed_id=b.id LEFT JOIN rooms r ON b.room_id=r.id LEFT JOIN wards w ON w.id=r.ward_id WHERE a.discharged_by IS NULL AND a.date_discharged IS NULL GROUP BY a.patient_id ORDER BY " . ($sort == null ? "w.id, r.id, a.bed_id DESC, a.date_admitted DESC" : $sort);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
//            error_log(print_r($sql, TRUE));
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$adm = new InPatient($row["id"]);
				$pat = new PatientDemograph();
				$pat->setActive($row["active"]);
				$pat->setId($row["patient_id"]);
				$pat->setLegacyId($row["legacy_patient_id"]);
				$pat->setLoginId($row["login_id"]);
				$pat->setFname($row["fname"]);
				$pat->setLname($row["lname"]);
				$pat->setMname($row["mname"]);
				$pat->setDateOfBirth($row["date_of_birth"]);
				$pat->setSex($row["sex"]);
				$pat->setEmail($row["email"]);
				$pat->setAddress($row["address"]);
				if ($getFull) {
					$lga = (new LGADAO())->getLGA($row["lga_id"], FALSE, $pdo);
					$resLga = (new LGADAO())->getLGA($row["lga_res_id"], FALSE, $pdo);
					$state = (new StateDAO())->getState($row["state_id"], $pdo);
					$resState = (new StateDAO())->getState($row["state_res_id"], $pdo);
					$clinic = (new ClinicDAO())->getClinic($row["basehospital"], FALSE, $pdo);
					$ses = (new SocioEconomicStatusDAO())->getSocioEconomicStatus($row["socio_economic"], $pdo);
					$ins = (new InsuranceDAO())->getInsurance($row["patient_id"], TRUE, $pdo);
				} else {
					$lga = $resLga = new LGA();
					$state = $resState = new State();
					$clinic = new Clinic();
					$ses = new SocioEconomicStatus();
					$ins = new Insurance();
					$lga->setId($row['lga_id']);
					$resLga->setId($row['lga_res_id']);
					$state->setId($row['state_id']);
					$resState->setId($row['state_res_id']);
					$clinic->setId($row['basehospital']);
					$ses->setId($row['socio_economic']);
					$ins->setPatient($pat);
				}
				$pat->setLga($lga);
				$pat->setResLga($resLga);
				$pat->setState($state);
				$pat->setResState($resState);

				$pat->setKinsFirstName($row["KinsFirstName"]);
				$pat->setKinsLastName($row["KinsLastName"]);
				$pat->setKinsPhone($row["KinsPhone"]);
				$pat->setKinsAddress($row["KinsAddress"]);
				$pat->setRegisteredBy($row["registered_By"]);
				$pat->setPhoneNumber($row["phonenumber"]);
				$pat->setBloodGroup($row["bloodgroup"]);
				$pat->setBloodType($row["bloodtype"]);
				$pat->setBaseClinic($clinic); //obj
				$pat->setTransferedTo($row["transferedto"]);
				$pat->setEnrollmentDate($row["enrollment_date"]);
				$pat->setSocioEconomic($ses);   //obj
				$pat->setLifeStyle($row["lifestyle"]);
				$pat->setPassportPath($this->getPatientImage($row['patient_id'], $row['sex']));
				$pat->setInsurance($ins);   //obj

				$adm->setPatient($pat);
				$bedSpace = new Bed();
				$bedSpace->setId($row["bedSpaceId"]);
				$bedSpace->setName($row["name"]);
				$bedSpace->setType($row["bedtype_id"]);
				$room = new Room();
				$room->setId($row["ward_id"]);
				$room->setRoomName($row["name"]);
				$ward = new Ward();
				$ward->setId($row["ward_id"]);
				$ward->setName($row["name"]);
				$ward->setHospital($row["hospid"]);
				$room->setWard($ward);
				$bedSpace->setRoom($room);
				$bedSpace->setAvailable($row["available"]);
				$bedSpace->setDescription($row["description"]);
				$bedSpace->setHospital($row["hospid"]);
				$adm->setBed($bedSpace);
				$adm->setDateAdmitted($row["date_admitted"]);
				$adm->setAdmittedBy($row["admitted_by"]);
				$adm->setReason($row["reason"]);
				$adm->setClinicalTask($row["ward_round"]);
				$adm->setDateDischarged($row["date_discharged"]);
				$adm->setDischargedBy($row["discharged_by"]);
				$adm->setClinic($row["hospital_id"]);
				$adm->setMCount($row["mCount"]);
				$adm->setSearch($row["patient_id"] . "|" . $pat->getLegacyId() . "|" . $pat->getPhoneNumber() . "|" . $pat->getFname() . " " . $pat->getLname() . " " . $pat->getMname());
				$admissions[] = $adm;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$admissions = null;
		}
		return $admissions;
	}

	function assignBed($aid, $bid, $pdo = null)
	{
		$status = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$canCommit = !$pdo->inTransaction();
			$sql = "UPDATE in_patient SET bed_id = $bid, bed_assign_date = IF(bed_assign_date IS NOT NULL, bed_assign_date, NOW()) WHERE id = " . $aid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ((new BedDAO())->occupyBed($bid, $pdo)) {
				$status = TRUE;
				if ($canCommit) {
					$pdo->commit();
				}
			} else {
				error_log("this is means error");
				$pdo->rollBack();
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$status = null;
		}
		return $status;
	}

	function clearBill($aid, $pdo = null)
	{
		$status = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;

			$sql = "UPDATE in_patient SET bill_status = 'Cleared' WHERE id = " . $aid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$stmt = null;

			$status = true;
		} catch (PDOException $e) {
			$stmt = null;
			$status = null;
		}
		return $status;
	}

	function getDaysOnAdmission($aid, $to = null, $pdo = null)
	{
		$days = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT DATEDIFF(" . ($to === null ? 'DATE(NOW())' : $to) . ", date_admitted) as days FROM in_patient WHERE id=$aid";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return $row['days'];
			}
			return null;
		} catch (PDOException $e) {
			return null;
		}
	}

	function generateInPatientBill($aid, $pdo)
	{
		$amount = null;
	}

	function partialDischarge($aid, $appointment=null, $medication=null, $reason, $pdo = null)
	{
		$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
		$canCommit = TRUE;
		try {
			$pdo->beginTransaction();
		} catch (Exception $ex) {
			$canCommit = FALSE;
		}
		if (!isset($_SESSION)) {
			session_start();
		}
		try {
			$staff = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID'], FALSE, $pdo);
			$sql = "UPDATE in_patient SET `status`='Discharging', date_discharged = NOW(), discharged_by = '" . $staff->getId() . "', appointment_id='". $appointment ."', medication_code='". $medication  ."', discharge_note ='" . escape($reason) . "'  WHERE id = " . $aid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$ip = $this->getInPatient($aid, TRUE, $pdo);

			$stmt = null;
			$freeBed = null;
			if ($ip->getBed() !== null) {
				$freeBed = (new BedDAO())->unAssignBed($ip->getBed()->getId(), $pdo);
			}

			//set the status of clinical tasks of this admission as `discharged`
			$sql2 = "UPDATE `clinical_task` SET `status` = 'Discharged' WHERE in_patient_id = " . $ip->getId();
			$stmt2 = $pdo->prepare($sql2, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt2->execute();

			if (($ip->getBed() != null && $freeBed == TRUE) && ($stmt2->rowCount() == 0 || $stmt2->rowCount() == 1)) {
				if ($canCommit) {
					$pdo->commit();
					return TRUE;
				}

			} else if ($ip->getBed() === null && ($stmt2->rowCount() == 0 || $stmt2->rowCount() == 1)) {
				if ($canCommit) {
					$pdo->commit();
					return TRUE;
				}
			}
			$pdo->rollBack();
		} catch (PDOException $e) {
			errorLog($e);
			$pdo->rollBack();
			return null;
		} catch (Exception $e) {
			errorLog($e);
			$pdo->rollBack();
			return null;
		}
		return null;
	}

	function claimIpEncounter($id, $pdo = null)
	{
		$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
		try {
			$sql = "UPDATE in_patient SET claimed = true WHERE id=$id";
			// error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return $this->getInPatient($id, false, $pdo);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function discharge($aid,$appointment=null, $medication=null, $reason=null, $pdo = null)
	{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
		$notInTransaction = !$pdo->inTransaction();

		if (!$pdo->inTransaction()) {
			$pdo->beginTransaction();
		}

		if (!isset($_SESSION)) {session_start();}
		$freeBed = null;
		try {
			$staff = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID'], FALSE, $pdo);
			if (!AdmissionSetting::$ipMedicationTaskRealTimeDeduct) {
				$sql = "UPDATE in_patient SET `status` = 'Discharged', date_discharged_full = NOW(), discharged_by_full = '" . $staff->getId() . "', appointment_id=". $appointment .", medication_code='". $medication  ."', discharge_note ='" . $reason . "' WHERE id = " . $aid;
			} else {
				
				$sql = "UPDATE in_patient SET `status`='Discharged', date_discharged=NOW(), date_discharged_full = NOW(), discharged_by='" . $staff->getId() . "', discharged_by_full = '" . $staff->getId() . "', appointment_id=". $appointment .", medication_code='". $medication ."', discharge_note ='" . $reason . "'  WHERE id = " . $aid;
			}
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$ip = $this->getInPatient($aid, TRUE, $pdo);
			if ($ip->getBed() !== null) {
				(new BedDAO())->unAssignBed($ip->getBed()->getId(), $pdo);
			}
			if ($stmt->rowCount() >= 0 ) {
				if ($notInTransaction) {
					$pdo->commit();
				}
				return TRUE;
			} else {
				if ($notInTransaction) {
					$pdo->rollBack();
				}
				return null;
			}

		} catch (PDOException $e) {
			errorLog($e);

			if ($notInTransaction) {
				$pdo->rollBack();
			}
			return null;
		} catch (Exception $e) {
			errorLog($e);
			if ($notInTransaction) {
				$pdo->rollBack();
			}
			return null;
		}
	}

}