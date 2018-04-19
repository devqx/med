<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/15/14
 * Time: 12:08 PM
 */
class PatientProcedureDAO
{

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientProcedure.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InPatient.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Procedure.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureNoteDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureRegimenDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureResourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureMedicalReportDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ResourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DiagnosisDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureNursingTaskDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/NursingServiceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureActionListDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/BodyPart.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BodyPartDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureAttachmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientProcedureItem.php';


			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function getPatientProcedures($pid, $start = null, $stop = null, $page = 0, $pageSize = 10, $source = null, $serviceCentreId = null, $pdo = null)
	{
		if ($start == null) {
			$dateStart = date("1970-01-01");
		} else {
			$dateStart = date("Y-m-d", strtotime($start));
		}
		if ($stop == null) {
			$dateStop = date("Y-m-d");
		} else {
			$dateStop = date("Y-m-d", strtotime($stop));
		}

		if (isset($start, $stop)) {
			//swap the dates, since mysql does not really obey negative date between`s
			//and assign in a single line. double line assignment fails
			//because by the time the later comparison is called,
			//they would be equal and things are not consistent anymore
			list($dateStart, $dateStop) = [min($dateStart, $dateStop), max($dateStart, $dateStop)];
		}
		$centre = $serviceCentreId !== null ? " AND service_centre_id=$serviceCentreId" : "";
		$sql = "SELECT * FROM patient_procedure WHERE patient_id = $pid AND DATE(request_date) BETWEEN '$dateStart' AND '$dateStop'{$centre}";
		$sql .= $source != null ? " AND source='" . $source->name . "' AND source_instance_id=" . $source->instance : " AND source IS NULL AND source_instance_id IS NULL";
		$sql .= " ORDER BY scheduled_on ASC, request_date DESC";
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

		$procedures = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$conditions_ = array_filter(explode(",", $row['condition_ids']));
				$conditions = [];
				foreach ($conditions_ as $condition) {
					$conditions[] = (new DiagnosisDAO())->getDiagnosis($condition, $pdo);
				}
				$sResources = [];
				foreach (array_filter(explode(',', $row['scheduled_resource_ids'])) as $sRes) {
					$sResources[] = (new ResourceDAO())->getResource($sRes, $pdo);
				}
				$procedures[] = (new PatientProcedure($row['id']))->setRequestCode($row['request_id'])->setProcedure((new ProcedureDAO())->getProcedure($row['procedure_id'], $pdo))->setRequestDate($row['request_date'])->setStatus($row['_status'])->setBodyPart((new BodyPartDAO())->get($row['bodypart_id'], $pdo))->setInPatient((new InPatient($row['in_patient_id'])))->setConditions($conditions)->setTimeStart($row['time_start'])->setTimeStop($row['time_stop'])->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by_id'], FALSE, $pdo))->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo))->setTheatre((new ResourceDAO())->getResource($row['theatre_id'], $pdo))->setHasSurgeon((bool)($row['has_surgeon']))->setHasAnesthesiologist((bool)$row['has_anesthesiologist'])->setNotes((new PatientProcedureNoteDAO())->getProcedureNotes(new PatientProcedure($row['id']), $pdo))->setResources((new PatientProcedureResourceDAO())->getProcedureResources(new PatientProcedure($row['id']), $pdo))->setRegimens((new PatientProcedureRegimenDAO())->getProcedureRegimens(new PatientProcedure($row['id']), $pdo))->setReports((new PatientProcedureMedicalReportDAO())->getProcedureReports(new PatientProcedure($row['id']), $pdo))->setSurgeon((new StaffDirectoryDAO())->getStaff($row['surgeon_id'], TRUE, $pdo))->setAnesthesiologist((new StaffDirectoryDAO())->getStaff($row['anesthesiologist_id'], TRUE, $pdo))->setScheduledResources($sResources)->setScheduledOn($row['scheduled_on'])->setScheduledBy((new StaffDirectoryDAO())->getStaff($row['scheduled_by'], FALSE, $pdo));
			}
		} catch (PDOException $e) {
			errorLog($e);
		}

		$results = (object)null;
		$results->data = $procedures;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function all($start = null, $stop = null, $start1 = null, $stop1 = null, $serviceCentreId = null, $category_id=null, $schResId=null, $page = 0, $pageSize = 10, $state = "open", $pdo = null)
	{
		$centre = $serviceCentreId !== null ? " AND p.service_centre_id=$serviceCentreId" : "";
		$category = $category_id !== null ? " AND c.category_id=$category_id" : "";
		$schResources = $schResId !== null ? " AND LOCATE($schResId, scheduled_resource_ids) is not null and LOCATE($schResId, scheduled_resource_ids) <> 0" : "";
		$requestState = $state !== null ? " AND _status='" . $state . "'" : "";
		if ($start == null) {
			$dateStart = '1970-01-01';
		} else {
			$dateStart = date("Y-m-d", strtotime($start));
		}
		if ($start1 == null) {
			$dateStart1 = '1970-01-01';
		} else {
			$dateStart1 = date("Y-m-d", strtotime($start1));
		}
		if ($stop == null) {
			$dateStop = date("Y-m-d");
		} else {
			$dateStop = date("Y-m-d", strtotime($stop));
		}
		if ($stop1 == null) {
			$dateStop1 = date("Y-m-d");
		} else {
			$dateStop1 = date("Y-m-d", strtotime($stop1));
		}

		if (isset($start, $stop)) {
			list($dateStart, $dateStop) = [min($dateStart, $dateStop), max($dateStart, $dateStop)];
		}
		//if (isset($start1, $stop1)) {
		if (!is_null($start1) && !is_null($stop1)) {
			list($dateStart1, $dateStop1) = [min($dateStart1, $dateStop1), max($dateStart1, $dateStop1)];
		}

		$timeStart = !is_null($start1) && !is_null($stop1) ? " AND DATE(p.time_start) BETWEEN '$dateStart1' AND '$dateStop1'" : "";

		$sql = "SELECT p.* FROM patient_procedure p LEFT JOIN patient_demograph d ON d.patient_ID=p.patient_id LEFT JOIN `procedure` c ON c.id=p.procedure_id WHERE DATE(p.request_date) BETWEEN '$dateStart' AND '$dateStop' $timeStart AND d.active IS TRUE AND p.`source` IS NULL $centre{$category}{$requestState}{$schResources} ORDER BY p.time_start, p.request_date ";#, p.patient_id
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

		$procedures = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			//error_log($sql);
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$proc = new PatientProcedure($row['id']);
				$proc->setRequestCode($row['request_id']);
				$proc->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, null));
				$proc->setProcedure((new ProcedureDAO())->getProcedure($row['procedure_id'], $pdo));
				$proc->setRequestDate($row['request_date']);
				$proc->setStatus($row['_status']);
				$proc->setBodyPart((new BodyPartDAO())->get($row['bodypart_id'], $pdo));
				$conditions_ = array_filter(explode(",", $row['condition_ids']));
				$conditions = [];
				foreach ($conditions_ as $condition) {
					$conditions[] = (new DiagnosisDAO())->getDiagnosis($condition, $pdo);
				}
				$proc->setInPatient((new InPatient($row['in_patient_id'])));
				
				$proc->setConditions($conditions);
				$proc->setTimeStart($row['time_start']);
				$proc->setTimeStop($row['time_stop']);
				$proc->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by_id'], FALSE, $pdo));
				$proc->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				$proc->setTheatre((new ResourceDAO())->getResource($row['theatre_id'], $pdo));

				$proc->setHasSurgeon((bool)($row['has_surgeon']));
				$proc->setHasAnesthesiologist((bool)$row['has_anesthesiologist']);

				$proc->setNotes((new PatientProcedureNoteDAO())->getProcedureNotes(new PatientProcedure($row['id']), $pdo));
				$proc->setResources((new PatientProcedureResourceDAO())->getProcedureResources(new PatientProcedure($row['id']), $pdo));
				$proc->setRegimens((new PatientProcedureRegimenDAO())->getProcedureRegimens(new PatientProcedure($row['id']), $pdo));
				$proc->setReports((new PatientProcedureMedicalReportDAO())->getProcedureReports(new PatientProcedure($row['id']), $pdo));

				$proc->setSurgeon((new StaffDirectoryDAO())->getStaff($row['surgeon_id'], FALSE, $pdo));
				$proc->setAnesthesiologist((new StaffDirectoryDAO())->getStaff($row['anesthesiologist_id'], FALSE, $pdo));
				$sResources = [];
				foreach (array_filter(explode(',', $row['scheduled_resource_ids'])) as $sRes) {
					$sResources[] = (new ResourceDAO())->getResource($sRes, $pdo);
				}
				$proc->setScheduledResources($sResources);
				$proc->setScheduledOn($row['scheduled_on']);
				$proc->setScheduledBy((new StaffDirectoryDAO())->getStaff($row['scheduled_by'], FALSE, $pdo));
				$procedures[] = $proc;
			}
		} catch (PDOException $e) {
			$procedures = [];
			errorLog($e);
		}
		$results = (object)null;
		$results->data = $procedures;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function getProceduresReport($from = null, $to = null, $category_id = null, $page, $pageSize, $pdo = null)
	{
		$f = ($from == null) ? date("Y-m-d") : $from;
		$t = ($to == null) ? date("Y-m-d") : $to;
		$cid = ($category_id == null) ? '' : ' AND pc.id=' . $category_id;

		$sql = "SELECT pp.* FROM patient_procedure pp LEFT JOIN `procedure` p ON p.id=pp.procedure_id LEFT JOIN procedure_category pc ON pc.id=p.category_id WHERE DATE(pp.request_date) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid}";
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

		$procedures = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT pp.* FROM patient_procedure pp LEFT JOIN `procedure` p ON p.id=pp.procedure_id LEFT JOIN procedure_category pc ON pc.id=p.category_id WHERE DATE(pp.request_date) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid} ORDER BY DATE(pp.request_date) ASC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$proc = new PatientProcedure($row['id']);
				$proc->setRequestCode($row['request_id']);
				$proc->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, TRUE));
				$proc->setProcedure((new ProcedureDAO())->getProcedure($row['procedure_id'], $pdo));
				$proc->setRequestDate($row['request_date']);
				$proc->setStatus($row['_status']);
				$proc->setBodyPart((new BodyPartDAO())->get($row['bodypart_id'], $pdo));
				$conditions_ = array_filter(explode(",", $row['condition_ids']));
				$conditions = [];
				foreach ($conditions_ as $condition) {
					$conditions[] = (new DiagnosisDAO())->getDiagnosis($condition, $pdo);
				}
				$proc->setInPatient((new InPatient($row['in_patient_id'])));

				$proc->setConditions($conditions);
				$proc->setTimeStart($row['time_start']);
				$proc->setTimeStop($row['time_stop']);
				$proc->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by_id'], FALSE, $pdo));
				$proc->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				$proc->setTheatre((new ResourceDAO())->getResource($row['theatre_id'], $pdo));

				$proc->setHasSurgeon((bool)($row['has_surgeon']));
				$proc->setHasAnesthesiologist((bool)$row['has_anesthesiologist']);

				$proc->setNotes((new PatientProcedureNoteDAO())->getProcedureNotes(new PatientProcedure($row['id']), $pdo));
				$proc->setResources((new PatientProcedureResourceDAO())->getProcedureResources(new PatientProcedure($row['id']), TRUE, $pdo));
				$proc->setRegimens((new PatientProcedureRegimenDAO())->getProcedureRegimens(new PatientProcedure($row['id']), $pdo));
				$proc->setReports((new PatientProcedureMedicalReportDAO())->getProcedureReports(new PatientProcedure($row['id']), $pdo));
				$proc->setTasks((new PatientProcedureNursingTaskDAO())->getProcedureTasks(new PatientProcedure($row['id']), $pdo));

				$proc->setSurgeon((new StaffDirectoryDAO())->getStaff($row['surgeon_id'], TRUE, $pdo));
				$proc->setAnesthesiologist((new StaffDirectoryDAO())->getStaff($row['anesthesiologist_id'], TRUE, $pdo));

				$procedures[] = $proc;
			}
		} catch (PDOException $e) {
			$procedures = [];
		}
		$results = (object)null;
		$results->data = $procedures;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}
	
	
	function getStartedProceduresReport($from = null, $to = null, $category_id = null, $page, $pageSize, $pdo = null)
	{
		$f = ($from == null) ? date("Y-m-d") : $from;
		$t = ($to == null) ? date("Y-m-d") : $to;
		$cid = ($category_id == null) ? '' : ' AND pc.id=' . $category_id;
		
		$sql = "SELECT pp.* FROM patient_procedure pp LEFT JOIN `procedure` p ON p.id=pp.procedure_id LEFT JOIN procedure_category pc ON pc.id=p.category_id WHERE DATE(pp.time_started) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid}";
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
		
		$procedures = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT pp.* FROM patient_procedure pp LEFT JOIN `procedure` p ON p.id=pp.procedure_id LEFT JOIN procedure_category pc ON pc.id=p.category_id WHERE DATE(pp.time_started) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid} ORDER BY DATE(pp.time_started) ASC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$proc = new PatientProcedure($row['id']);
				$proc->setRequestCode($row['request_id']);
				$proc->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, TRUE));
				$proc->setProcedure((new ProcedureDAO())->getProcedure($row['procedure_id'], $pdo));
				$proc->setRequestDate($row['request_date']);
				$proc->setStatus($row['_status']);
				$proc->setBodyPart((new BodyPartDAO())->get($row['bodypart_id'], $pdo));
				$conditions_ = array_filter(explode(",", $row['condition_ids']));
				$conditions = [];
				foreach ($conditions_ as $condition) {
					$conditions[] = (new DiagnosisDAO())->getDiagnosis($condition, $pdo);
				}
				$proc->setInPatient((new InPatient($row['in_patient_id'])));
				
				$proc->setConditions($conditions);
				$proc->setTimeStart($row['time_start']);
				$proc->setTimeStop($row['time_stop']);
				$proc->setTimeStarted($row['time_started']);
				$proc->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by_id'], FALSE, $pdo));
				$proc->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				$proc->setTheatre((new ResourceDAO())->getResource($row['theatre_id'], $pdo));
				
				$proc->setHasSurgeon((bool)($row['has_surgeon']));
				$proc->setHasAnesthesiologist((bool)$row['has_anesthesiologist']);
				
				$proc->setNotes((new PatientProcedureNoteDAO())->getProcedureNotes(new PatientProcedure($row['id']), $pdo));
				$proc->setResources((new PatientProcedureResourceDAO())->getProcedureResources(new PatientProcedure($row['id']), TRUE, $pdo));
				$proc->setRegimens((new PatientProcedureRegimenDAO())->getProcedureRegimens(new PatientProcedure($row['id']), $pdo));
				$proc->setReports((new PatientProcedureMedicalReportDAO())->getProcedureReports(new PatientProcedure($row['id']), $pdo));
				$proc->setTasks((new PatientProcedureNursingTaskDAO())->getProcedureTasks(new PatientProcedure($row['id']), $pdo));
				
				$proc->setSurgeon((new StaffDirectoryDAO())->getStaff($row['surgeon_id'], TRUE, $pdo));
				$proc->setAnesthesiologist((new StaffDirectoryDAO())->getStaff($row['anesthesiologist_id'], TRUE, $pdo));
				
				$procedures[] = $proc;
			}
		} catch (PDOException $e) {
			$procedures = [];
		}
		$results = (object)null;
		$results->data = $procedures;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function exportProceduresReport($from = null, $to = null, $category_id = null, $page, $pageSize, $pdo = null)
	{
		$f = ($from == null) ? date("Y-m-d") : $from;
		$t = ($to == null) ? date("Y-m-d") : $to;
		$cid = ($category_id == null) ? '' : ' AND pc.id=' . $category_id;

		$sql = "SELECT pp.* FROM patient_procedure pp LEFT JOIN `procedure` p ON p.id=pp.procedure_id LEFT JOIN procedure_category pc ON pc.id=p.category_id WHERE DATE(pp.request_date) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid}";
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
			$sql = "SELECT pp.* FROM patient_procedure pp LEFT JOIN `procedure` p ON p.id=pp.procedure_id LEFT JOIN procedure_category pc ON pc.id=p.category_id WHERE DATE(pp.request_date) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid} ORDER BY DATE(pp.request_date) ASC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$report = (object)null;

				$report->Date = date('jS M, Y', strtotime($row['request_date']));
				$report->Patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, TRUE)->getFullname();
				$report->PatientId = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, TRUE)->getId();
				$report->Age = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, TRUE)->getAge();
				$report->Procedure = (new ProcedureDAO())->getProcedure($row['procedure_id'], $pdo)->getName();
				$report->bodypart = $row['bodypart_id'] !== null ? (new BodyPartDAO())->get($row['bodypart_id'], $pdo)->getName() : "-----";

				$conditions_ = array_filter(explode(",", $row['condition_ids']));
				$conditions = [];
				foreach ($conditions_ as $condition) {
					$conditions[] = (new DiagnosisDAO())->getDiagnosis($condition, $pdo)->getName();
				}
				$report->Diagnosis = implode(', ', $conditions);

				$resource_ = (new PatientProcedureResourceDAO())->getProcedureResources(new PatientProcedure($row['id']), FALSE, $pdo);
				$participants = [];
				foreach ($resource_ as $resource) {
					$participants[] = $resource->getResource()->getShortname();
				}
				$report->Participants = implode(', ', $participants);
				$report->ServiceCenter = $row['service_centre_id'] ? (new ServiceCenterDAO())->get($row['service_centre_id'], $pdo)->getName() : '--';

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

	function get($id, $pdo = null)
	{
		$proc = new PatientProcedure();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;

			$sql = "SELECT pr.* FROM patient_procedure pr LEFT JOIN patient_demograph d ON d.patient_ID=pr.patient_id WHERE d.active IS TRUE AND id = " . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$proc->setId($row['id']);
				$proc->setRequestCode($row['request_id']);
				$proc->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, null));
				$proc->setProcedure((new ProcedureDAO())->getProcedure($row['procedure_id'], $pdo));
				$proc->setRequestDate($row['request_date']);
				$proc->setStatus($row['_status']);
				$proc->setAppointmentId($row['appointment_id']);
				$proc->setBodyPart((new BodyPartDAO())->get($row['bodypart_id'], $pdo));
				$conditions_ = array_filter(explode(",", $row['condition_ids']));
				$conditions = [];
				foreach ($conditions_ as $condition) {
					$conditions[] = (new DiagnosisDAO())->getDiagnosis($condition, $pdo);
				}
				$proc->setInPatient($row['in_patient_id'] != null ? new InPatient($row['in_patient_id']) : null);
				$proc->setConditions($conditions);
				$proc->setTimeStart($row['time_start']);
				$proc->setTimeStop($row['time_stop']);
				$proc->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by_id'], FALSE, $pdo));
				$proc->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				$proc->setTheatre((new ResourceDAO())->getResource($row['theatre_id'], $pdo));
				$proc->setHasSurgeon((bool)($row['has_surgeon']));
				$proc->setHasAnesthesiologist((bool)$row['has_anesthesiologist']);
				$proc->setNotes((new PatientProcedureNoteDAO())->getProcedureNotes(new PatientProcedure($row['id']), $pdo));
				$proc->setRegimens((new PatientProcedureRegimenDAO())->getProcedureRegimens(new PatientProcedure($row['id']), $pdo));
				$proc->setResources((new PatientProcedureResourceDAO())->getProcedureResources(new PatientProcedure($row['id']), FALSE, $pdo));
				$proc->setReports((new PatientProcedureMedicalReportDAO())->getProcedureReports(new PatientProcedure($row['id']), $pdo));
				$proc->setTasks((new PatientProcedureNursingTaskDAO())->getProcedureTasks(new PatientProcedure($row['id']), $pdo));
				$proc->setActionList((new ProcedureActionListDAO())->forPatProcedure($row['id'], $pdo));
				$proc->setAttachments((new ProcedureAttachmentDAO())->forPatProcedure($row['id'], $pdo));
				$proc->setSurgeon((new StaffDirectoryDAO())->getStaff($row['surgeon_id'], true, $pdo));
				$proc->setAnesthesiologist((new StaffDirectoryDAO())->getStaff($row['anesthesiologist_id'], true, $pdo));
				
				$sResources = [];
				foreach (array_filter(explode(',', $row['scheduled_resource_ids'])) as $sRes) {
					$sResources[] = (new ResourceDAO())->getResource($sRes, $pdo);
				}
				$proc->setScheduledResources($sResources);
				$proc->setScheduledOn($row['scheduled_on']);
				$proc->setScheduledBy((new StaffDirectoryDAO())->getStaff($row['scheduled_by'], FALSE, $pdo));
				$proc->setBilled((bool)$row['billed']);
				
			} else {
				$proc = null;
			}
		} catch (PDOException $e) {
			$proc = null;
		}
		return $proc;
	}
	
	function add($procedure, $charged = false, $pdo = null)
	{
		//$procedure = new PatientProcedure();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$canCommit = !$pdo->inTransaction();
			try {
				$pdo->beginTransaction();
			} catch (PDOException $e) {
			
			}
			$conditions = $procedure->getConditions();
			$condition_ids = array();
			foreach ($conditions as $c) {
				$condition_ids[] = $c;
			}
			
			$anes = var_export($procedure->getHasAnesthesiologist(), true);
			$surg = var_export($procedure->getHasSurgeon(), true);
			$inPatient = ($procedure->getInPatient() !== null) ? "'" . $procedure->getInPatient()->getId() . "'" : "NULL";
			$referral_id = ($procedure->getReferral() !== null) ? $procedure->getReferral()->getId() : "NULL";
			$service_centre_id = ($procedure->getServiceCentre() !== null) ? $procedure->getServiceCentre()->getId() : "NULL";
			$body_id = ($procedure->getBodyPart() !== null) ? $procedure->getBodyPart()->getId() : "NULL";
			$timeStart = $procedure->getTimeStart() ? quote_esc_str($procedure->getTimeStart()) : "NULL";
			$timeStop = $procedure->getTimeStop() ? quote_esc_str($procedure->getTimeStop()) : "NULL";
			$source = 'null';
			$sourceInstance = 'null';
			$requestNote = !is_blank($procedure->getRequestNote()) ? quote_esc_str($procedure->getRequestNote()):'NULL';
			if (!is_blank($procedure->getSource()) && !is_blank($procedure->getSourceInstanceId())) {
				$source = quote_esc_str($procedure->getSource());
				$sourceInstance = $procedure->getSourceInstanceId();
			}
			
			$billed = var_export($procedure->getBilled(), true);
			$sql = "INSERT INTO patient_procedure (patient_id, procedure_id, request_date, request_note, condition_ids, time_start, time_stop, resource_id, requested_by_id, has_anesthesiologist, has_surgeon, in_patient_id, referral_id, service_centre_id, bodypart_id, `source`, source_instance_id, billed) VALUES ('" . $procedure->getPatient()->getId() . "', '" . $procedure->getProcedure()->getId() . "', NOW(), $requestNote, '" . implode(",", $condition_ids) . "', $timeStart, $timeStop, NULL, '" . $procedure->getRequestedBy()->getId() . "', $anes, $surg, $inPatient, $referral_id, $service_centre_id, $body_id, $source, $sourceInstance, $billed)";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$_ = new Procedure();
			$desc = $_::$desc;
			
			if ($stmt->rowCount() == 1) {
				$procedure->setId($pdo->lastInsertId());
				//continue to bill this patient
				$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
				if (!$charged) {
					$bil = new Bill();
					$bil->setPatient($procedure->getPatient());
					$bil->setDescription($desc[0] . $procedure->getProcedure()->getName());
					$bil->setPriceType('selling_price');
					
					$bil->setItem($procedure->getProcedure());
					$bil->setSource((new BillSourceDAO())->findSourceById(8, $pdo));
					$bil->setTransactionType("credit");
					
					$item = (new InsuranceItemsCostDAO())->getItemPricesByCode($procedure->getProcedure()->getCode(), $procedure->getPatient()->getId(), true, $pdo);
					$bil->setAmount($item->sellingPrice);
					$bil->setPriceType('selling_price');
					$bil->setDiscounted(null);
					$bil->setDiscountedBy(null);
										
					$patientScheme = (new InsuranceDAO())->getInsurance($procedure->getPatient()->getId(), false, $pdo)->getScheme();
					$bil->setClinic($staff->getClinic());
					$bil->setBilledTo($patientScheme);
					$bil->setReferral($procedure->getReferral());
					$bil->setCostCentre((new ServiceCenterDAO())->get($service_centre_id, $pdo) ? (new ServiceCenterDAO())->get($service_centre_id, $pdo)->getCostCentre() : null);
					
					$bill = (new BillDAO())->addBill($bil, 1, $pdo);
					
					if ($bill == null) {
						if ($canCommit) {
							$pdo->rollBack();
						}
						return null;
					}
					if ($procedure->getHasSurgeon()) {
						$bil = new Bill();
						$bil->setPatient($procedure->getPatient());
						$bil->setDescription($desc[1] . $procedure->getProcedure()->getName());
						
						$bil->setItem($procedure->getProcedure());
						$bil->setSource((new BillSourceDAO())->findSourceById(30, $pdo));
						$bil->setTransactionType("credit");
						
						$item = (new InsuranceItemsCostDAO())->getItemPricesByCode($procedure->getProcedure()->getCode(), $procedure->getPatient()->getId(), true, $pdo);
						$bil->setAmount($item->surgeonPrice);
						$bil->setPriceType('surgeonPrice');
						$bil->setDiscounted(null);
						$bil->setDiscountedBy(null);
						
						$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
						$bil->setClinic($staff->getClinic());
						$bil->setBilledTo($procedure->getPatient()->getScheme());
						$bil->setReferral($procedure->getReferral());
						$bil->setCostCentre((new ServiceCenterDAO())->get($service_centre_id, $pdo) ? (new ServiceCenterDAO())->get($service_centre_id, $pdo)->getCostCentre() : null);
						
						$bill = (new BillDAO())->addBill($bil, 1, $pdo);
						
						if ($bill == null) {
							if ($canCommit) {
								$pdo->rollBack();
							}
							return null;
						}
					}
					if ($procedure->getHasAnesthesiologist()) {
						$bil = new Bill();
						$bil->setPatient($procedure->getPatient());
						$bil->setDescription($desc[2] . $procedure->getProcedure()->getName());
						
						$bil->setItem($procedure->getProcedure());
						$bil->setSource((new BillSourceDAO())->findSourceById(28, $pdo));
						$bil->setTransactionType("credit");
						
						$item = (new InsuranceItemsCostDAO())->getItemPricesByCode($procedure->getProcedure()->getCode(), $procedure->getPatient()->getId(), true, $pdo);
						$bil->setAmount($item->anaesthesiaPrice);
						$bil->setPriceType('anaesthesiaPrice');
						$bil->setDiscounted(null);
						$bil->setDiscountedBy(null);
						
						$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
						$bil->setClinic($staff->getClinic());
						$bil->setBilledTo($procedure->getPatient()->getScheme());
						$bil->setReferral($procedure->getReferral());
						$bil->setCostCentre((new ServiceCenterDAO())->get($service_centre_id, $pdo) ? (new ServiceCenterDAO())->get($service_centre_id, $pdo)->getCostCentre() : null);
						
						$bill = (new BillDAO())->addBill($bil, 1, $pdo);
						
						if ($bill == null) {
							if ($canCommit) {
								$pdo->rollBack();
							}
							return null;
						}
						
						
					}
					
					$bil = new Bill();
					$bil->setPatient($procedure->getPatient());
					$bil->setDescription($desc[3] . $procedure->getProcedure()->getName());
					
					$bil->setItem($procedure->getProcedure());
					$bil->setSource((new BillSourceDAO())->findSourceById(27, $pdo));
					$bil->setTransactionType("credit");
					
					$item = (new InsuranceItemsCostDAO())->getItemPricesByCode($procedure->getProcedure()->getCode(), $procedure->getPatient()->getId(), true, $pdo);
					$bil->setAmount($item->theatrePrice);
					$bil->setPriceType('theatrePrice');
					$bil->setDiscounted(null);
					$bil->setDiscountedBy(null);
					
					$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
					$bil->setClinic($staff->getClinic());
					$bil->setBilledTo($procedure->getPatient()->getScheme());
					$bil->setReferral($procedure->getReferral());
					$bil->setCostCentre((new ServiceCenterDAO())->get($service_centre_id, $pdo) ? (new ServiceCenterDAO())->get($service_centre_id, $pdo)->getCostCentre() : null);
					
					$bill = (new BillDAO())->addBill($bil, 1, $pdo);
					
					if ($bill == null) {
						if ($canCommit) {
							$pdo->rollBack();
						}
						return null;
					}
					
					$queue = new PatientQueue();
					$queue->setType("Procedure");
					$queue->setPatient($procedure->getPatient());
					$q = (new PatientQueueDAO())->addPatientQueue($queue, $pdo);
					
					if ($q == null) {
						if ($canCommit) {
							$pdo->rollBack();
						}
						return null;
					}
				}
				
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VisitNotes.php';
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
				$note = (new VisitNotes())->setNotedBy($staff)->setDateOfEntry(time())->setDescription("Procedure " . $procedure->getProcedure()->getName() . " Scheduled")->setHospital(new Clinic(1))->setNoteType('inv')->setPatient($procedure->getPatient());
				
				if ((new VisitNotesDAO())->addNote($note, $pdo)) {
					if ($canCommit) {
						$pdo->commit();
					}
					return $procedure;
				}
				if ($canCommit) {
					$pdo->rollBack();
				}
				return null;
			}
			if ($canCommit) {
				$pdo->rollBack();
			}
			
			return null;
		} catch (PDOException $e) {
			error_log($e->getMessage() . " at " . $e->getLine());
			return null;
		}
	}

	function addItem($pro, $pdo = null)
	{
		$qty = $pro->getQuantity();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			if ($this->materialExist($pro->getProcedure(), $pro->getItem(), $pdo)) {
				$sql = "UPDATE patient_procedure_items SET quantity=(quantity + $qty) WHERE patient_procedure_id='" . $pro->getProcedure() . "' AND item_id='" . $pro->getItem() . "'";
			} else {
				$sql = "INSERT INTO patient_procedure_items (patient_procedure_id, item_id, generic_id, service_center_id, quantity, batch_id) VALUES ('" . $pro->getProcedure() . "', '" . $pro->getItem() . "', '" . $pro->getGeneric() . "', '" . $pro->getServiceCenter() . "', '" . $qty . "', '" . $pro->getBatch() . "')";
			}
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				$pdo->commit();
				return $pro;
			}
			$pdo->rollBack();
			return null;
			
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	private function materialExist($pid, $iid, $pdo)
	{
		$status = false;
		try {
			$stmt = $pdo->prepare("SELECT * FROM patient_procedure_items WHERE patient_procedure_id=$pid AND item_id=$iid ", array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$status = true;
			}
		} catch (PDOException $e) {
			$stmt = null;
			$status = false;
		}
		return $status;
	}
	
	function findProcedures($query, $page = 0, $pageSize = 10, $pdo = null)
	{
		$sql = "SELECT pr.* FROM patient_procedure pr LEFT JOIN patient_demograph d ON pr.patient_id=d.patient_ID WHERE d.active IS TRUE AND pr.source IS NULL AND (pr.request_id LIKE '%$query%' OR d.patient_ID LIKE '%$query%' OR d.fname LIKE '%$query%' OR d.mname LIKE '%$query%' OR d.lname LIKE '%$query%' OR d.phonenumber LIKE '%$query%') ";
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
		$procedures = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIt $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				
				$procedures[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			return [];
			
		}
		$results = (object)null;
		$results->data = $procedures;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function updateProcedure($patientProcedure, $pdo = null)
	{
		try {
			// $patientProcedure = new PatientProcedure();
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$theatre = ($patientProcedure->getTheatre() !== null) ? "'" . $patientProcedure->getTheatre()->getId() . "'" : "NULL";
			$anesthesiologist = ($patientProcedure->getAnesthesiologist() !== null) ? "'" . $patientProcedure->getAnesthesiologist()->getId() . "'" : "NULL";
			$surgeon = ($patientProcedure->getSurgeon() !== null) ? "'" . $patientProcedure->getSurgeon()->getId() . "'" : "NULL";
			$sql = "UPDATE patient_procedure SET theatre_id = $theatre, anesthesiologist_id = $anesthesiologist, surgeon_id = $surgeon WHERE id = " . $patientProcedure->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				return $patientProcedure;
			} else {
				return null;
			}
			
		} catch (PDOException $e) {
			return null;
		}
	}
	
	function changeProcedureStatus($patientProcedure, $status, $message = null, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$patientProcedure = (new PatientProcedureDAO())->get($patientProcedure->getId(), $pdo);
			$patient = (new PatientDemographDAO())->getPatient($patientProcedure->getPatient()->getId(), false, $pdo);
			
			$Procedure = (new PatientProcedureDAO())->get($patientProcedure->getId(), $pdo)->getProcedure();
			// $amount = (new InsuranceItemsCostDAO())->getItemPriceByCode($Procedure->getCode(), $patientProcedure->getPatient()->getId(), TRUE, $pdo);
			$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false, $pdo);
			$extraSQL = "";
			$reversal = null;
			if ($status == "started") {
				$extraSQL = ", time_started = NOW()";
			} else if ($status == "closed") {
				$extraSQL = ", time_stop = NOW(), closing_text = '" . escape($message) . "'";
			} else if ($status == "cancelled") {
				//$extraSQL = ", time_stop = NOW()";
			}
			if ($status == "cancelled") {
				$_ = new Procedure();
				$desc = $_::$desc;
				
				$pdo->beginTransaction();
				
				if($patientProcedure->getBilled()){
					$item = (new InsuranceItemsCostDAO())->getItemPricesByCode($Procedure->getCode(), $patientProcedure->getPatient()->getId(), true, $pdo);
					//get the line charged for the item and undo the related bill lines
					(new BillDAO())->cancelRelatedItems($patient->getId(), $patientProcedure->getProcedure()->getCode(), $patientProcedure->getRequestDate(), $pdo);
					if ($patientProcedure->getHasSurgeon()) {
						$bil = new Bill();
						$bil->setPatient($patient);
						$bil->setDescription($desc[1] . $Procedure->getName());
						$bil->setItem($patientProcedure->getProcedure());
						$bil->setSource((new BillSourceDAO())->findSourceById(30, $pdo));
						$bil->setTransactionType("reversal");
						$bil->setTransactionDate(date("Y-m-d H:i:s"));
						$bil->setDueDate($patientProcedure->getRequestDate());
						$bil->setAmount(0 - $item->surgeonPrice);
						$bil->setPriceType('surgeonPrice');
						$bil->setDiscounted(null);
						$bil->setDiscountedBy(null);
						$bil->setClinic($staff->getClinic());
						$bil->setBilledTo($patient->getScheme());
						$bil->setCostCentre(null);
						$bil->setActiveBill('not_active');
						$serviceCentre = $patientProcedure->getServiceCentre();
						$bil->setCostCentre($serviceCentre ? $serviceCentre->getCostCentre() : null);
						$reversal = (new BillDAO())->addBill($bil, 1, $pdo, null);
						if ($reversal == null) {
							$pdo->rollBack();
							return false;
						}
					}
					
					if ($patientProcedure->getHasAnesthesiologist()) {
						$bil = new Bill();
						$bil->setPatient($patient);
						$bil->setDescription($desc[2] . $Procedure->getName());
						$bil->setItem($patientProcedure->getProcedure());
						$bil->setSource((new BillSourceDAO())->findSourceById(28, $pdo));
						$bil->setTransactionType("reversal");
						$bil->setTransactionDate(date("Y-m-d H:i:s"));
						$bil->setAmount(0 - $item->anaesthesiaPrice);
						$bil->setPriceType('anaesthesiaPrice');
						$bil->setDiscounted(null);
						$bil->setDiscountedBy(null);
						$bil->setClinic($staff->getClinic());
						$bil->setBilledTo($patient->getScheme());
						$bil->setCostCentre(null);
						$bil->setActiveBill('not_active');
						$serviceCentre = $patientProcedure->getServiceCentre();
						$bil->setCostCentre($serviceCentre ? $serviceCentre->getCostCentre() : null);
						$reversal = (new BillDAO())->addBill($bil, 1, $pdo, null);
						if ($reversal == null) {
							$pdo->rollBack();
							return false;
						}
					}
					
					$bil = new Bill();
					$bil->setPatient($patient);
					$bil->setDescription($desc[3] . $Procedure->getName());
					$bil->setItem($patientProcedure->getProcedure());
					$bil->setSource((new BillSourceDAO())->findSourceById(27, $pdo));
					$bil->setTransactionType("reversal");
					$bil->setTransactionDate(date("Y-m-d H:i:s"));
					$bil->setAmount(0 - $item->theatrePrice);
					$bil->setPriceType('theatrePrice');
					$bil->setDiscounted(null);
					$bil->setDiscountedBy(null);
					$bil->setClinic($staff->getClinic());
					$bil->setBilledTo($patient->getScheme());
					$bil->setCostCentre(null);
					$bil->setActiveBill('not_active');
					$serviceCentre = $patientProcedure->getServiceCentre();
					$bil->setCostCentre($serviceCentre ? $serviceCentre->getCostCentre() : null);
					$reversal = (new BillDAO())->addBill($bil, 1, $pdo, null);
					if ($reversal == null) {
						$pdo->rollBack();
						return false;
					}
					$bil = new Bill();
					$bil->setPatient($patient);
					$bil->setDescription($desc[0] . $Procedure->getName());
					$bil->setItem($patientProcedure->getProcedure());
					$bil->setSource((new BillSourceDAO())->findSourceById(8, $pdo));
					$bil->setTransactionType("reversal");
					$bil->setTransactionDate(date("Y-m-d H:i:s"));
					$bil->setAmount(0 - $item->sellingPrice);
					$bil->setPriceType('selling_price');
					$bil->setDiscounted(null);
					$bil->setDiscountedBy(null);
					$bil->setClinic($staff->getClinic());
					$bil->setBilledTo($patient->getScheme());
					$bil->setCostCentre(null);
					$bil->setActiveBill('not_active');
					$serviceCentre = $patientProcedure->getServiceCentre();
					$bil->setCostCentre($serviceCentre ? $serviceCentre->getCostCentre() : null);
					$reversal = (new BillDAO())->addBill($bil, 1, $pdo, null);
					if ($reversal == null) {
						$pdo->rollBack();
						return false;
					}
				}
			}
			
			// if cancelled, who cancelled it, and the time of cancellation,
			// you can get that from the bill line's `responsible`
			$sql = "UPDATE patient_procedure SET _status = '$status'{$extraSQL} WHERE id=" . $patientProcedure->getId();
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				if ($pdo->inTransaction() && $reversal !== null && $patientProcedure->getBilled()) {
					$pdo->commit();
					return true;
				} else if ($pdo->inTransaction() && $reversal === null && $patientProcedure->getBilled()) {
					$pdo->rollBack();
					error_log("FAILED TO CANCEL PROCEDURE...");
					return false;
				}  else if ($pdo->inTransaction() && $reversal === null && !$patientProcedure->getBilled()) {
					$pdo->commit();
					return true;
				}
				return true;
			} else {
				return false;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return false;
		}
	}
	
	/*function cancelProcedure($patientProcedure, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE patient_procedure SET _status = 'cancelled' WHERE id =" . $patientProcedure->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				return true;
			} else {
				return false;
			}
		} catch (PDOException $e) {
			return false;
		}
	}*/
	
	public function getEncounterProcedures($id, $pdo)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_procedure WHERE encounter_id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			return [];
		}
		return $data;
	}
	
	function unfulfilledRequests($page = 0, $pageSize = 10, $start = null, $stop = null, $pdo = null)
	{
		$f = ($start == null) ? date("Y-m-d") : $start;
		$t = ($stop == null) ? date("Y-m-d") : $stop;
		$sql = "SELECT p.* FROM patient_procedure p LEFT JOIN patient_demograph d ON d.patient_ID=p.patient_id WHERE p.`_status`='open' AND d.active IS TRUE AND DATE(request_date) BETWEEN DATE('$f') AND DATE('$t') ORDER BY p.request_date, p.patient_id";
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
		
		$procedures = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$proc = new PatientProcedure($row['id']);
				$proc->setRequestCode($row['request_id']);
				$proc->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, null));
				$proc->setProcedure((new ProcedureDAO())->getProcedure($row['procedure_id'], $pdo));
				$proc->setRequestDate($row['request_date']);
				$proc->setStatus($row['_status']);
				$proc->setBodyPart((new BodyPartDAO())->get($row['bodypart_id'], $pdo));
				$conditions_ = array_filter(explode(",", $row['condition_ids']));
				$conditions = [];
				foreach ($conditions_ as $condition) {
					$conditions[] = (new DiagnosisDAO())->getDiagnosis($condition, $pdo);
				}
				$proc->setInPatient((new InPatient($row['in_patient_id'])));
				
				$proc->setConditions($conditions);
				$proc->setTimeStart($row['time_start']);
				$proc->setTimeStop($row['time_stop']);
				$proc->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by_id'], false, $pdo));
				$proc->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				$proc->setTheatre((new ResourceDAO())->getResource($row['theatre_id'], $pdo));
				
				$proc->setHasSurgeon((bool)($row['has_surgeon']));
				$proc->setHasAnesthesiologist((bool)$row['has_anesthesiologist']);
				
				$proc->setNotes((new PatientProcedureNoteDAO())->getProcedureNotes(new PatientProcedure($row['id']), $pdo));
				$proc->setResources((new PatientProcedureResourceDAO())->getProcedureResources(new PatientProcedure($row['id']), $pdo));
				$proc->setRegimens((new PatientProcedureRegimenDAO())->getProcedureRegimens(new PatientProcedure($row['id']), $pdo));
				$proc->setReports((new PatientProcedureMedicalReportDAO())->getProcedureReports(new PatientProcedure($row['id']), $pdo));
				
				$proc->setSurgeon((new StaffDirectoryDAO())->getStaff($row['surgeon_id'], true, $pdo));
				$proc->setAnesthesiologist((new StaffDirectoryDAO())->getStaff($row['anesthesiologist_id'], true, $pdo));
				
				$procedures[] = $proc;
			}
		} catch (PDOException $e) {
			$procedures = [];
			
		}
		$results = (object)null;
		$results->data = $procedures;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
}
