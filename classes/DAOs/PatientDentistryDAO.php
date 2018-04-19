<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/13/14
 * Time: 2:45 PM
 */

class PatientDentistryDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Dentistry.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DentistryCategory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDentistry.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryCategoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDentistryNoteDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAntenatalUsagesDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo = null)
	{
		$service = new PatientDentistry();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_dentistry WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$service->setId($row['id']);
				$service->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, null));
				$service->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by_id'], false, $pdo));
				
				$service->setRequestDate($row['request_date']);
				$service->setApproved((bool)$row['approved']);
				$service->setRequestCode($row['requestCode']);
				
				$service->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				$rqs = explode(",", $row['dentistry_ids']);
				$items = array();
				foreach ($rqs as $s) {
					$items[] = (new DentistryDAO())->get($s, $pdo);
				}
				
				$service->setServices($items);
				$service->setRequestNote($row['request_note']);
				$service->setApprovedBy((new StaffDirectoryDAO())->getStaff($row['approved_by_id'], false, $pdo));
				$service->setDateLastModified($row['date_last_modified']);
				$service->setNotes((new PatientDentistryNoteDAO())->getDentistryNotes($row['id'], $pdo));
				$service->setStatus((bool)$row['status']);
				$service->setApprovedDate($row['approved_date']);
				
				$service->setCancelled((bool)$row['cancelled']);
				$service->setCanceledBy((new StaffDirectoryDAO())->getStaff($row['canceled_by_id'], false, $pdo));
				$service->setDateCanceled($row['cancel_date']);
			} else {
				$service = null;
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$service = null;
		}
		return $service;
	}
	
	function add($service, $pdo = null)
	{
		//$service = new PatientDentistry();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$canCommit = !$pdo->inTransaction();
			try {
				$pdo->beginTransaction();
			} catch (PDOException $ef) {
				//
			}
			
			$patient_id = $service->getPatient()->getId();
			$summary = "Dentistry Service" . ((count($service->getServices()) > 1) ? "s" : "") . ": ";
			$scan_ids_ = $summary_ = array();
			$bill = array();
			foreach ($service->getServices() as $i => $s) {
				//this is a custom object passed during creating
				$scan_ids_[] = $s->id;
				$summary_[] = $s->name;
				
				$cost = (new InsuranceItemsCostDAO())->getItemPriceByCode($s->code, $patient_id, true, $pdo) * $s->orderQuantity;
				
				$bil = new Bill();
				$bil->setPatient($service->getPatient());
				$bil->setDescription("Dentistry charges: " . $s->name);
				$bil->setItem((new DentistryDAO())->get($s->id, $pdo));
				$bil->setSource((new BillSourceDAO())->findSourceById(14, $pdo));
				$bil->setTransactionType("credit");
				$bil->setAmount($cost);
				$bil->setDiscounted(null);
				$bil->setDiscountedBy(null);
				$bil->setClinic($service->getRequestedBy()->getClinic());
				$bil->setBilledTo($service->getPatient()->getScheme());
				$bil->setReferral($service->getReferral());
				$serviceCentre = $service->getServiceCenter();
				$bil->setCostCentre($serviceCentre ? $serviceCentre->getCostCentre() : null);
				$bill[] = (new BillDAO())->addBill($bil, $s->orderQuantity, $pdo);
			}
			$scan_ids = implode(",", $scan_ids_);
			$requested_by_id = $service->getRequestedBy()->getId();
			$request_date = $service->getRequestDate();
			$referral_id = ($service->getReferral() !== null) ? $service->getReferral()->getId() : "NULL";
			$request_note = $service->getRequestNote() ? "'" . escape($service->getRequestNote()) . "'" : "NULL";
			$sql = "INSERT INTO patient_dentistry (patient_id, dentistry_ids, request_note, requested_by_id, request_date, referral_id) VALUES ($patient_id, '$scan_ids', $request_note, $requested_by_id,'$request_date', $referral_id)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$service->setId($pdo->lastInsertId());
			$summary .= implode(", ", $summary_);
			$summary .= " requested";
			
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VisitNotes.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
			$note = new VisitNotes();
			$note->setNotedBy($service->getRequestedBy());
			$note->setDateOfEntry(time());
			$note->setDescription($summary);
			$note->setHospital(new Clinic(1));
			$note->setNoteType('inv');
			$note->setPatient($service->getPatient());
			
			(new VisitNotesDAO())->addNote($note, $pdo);
			
			//add to queue
			try {
				$queue = new PatientQueue();
				$queue->setType("Dentistry");
				$queue->setPatient($service->getPatient());
				(new PatientQueueDAO())->addPatientQueue($queue, $pdo);
			} catch (PDOException $e) {
				error_log("Error adding to dentistry queue");
			}
			
			if ($stmt->rowCount() == 1 && !in_array(null, $bill)) {
				if ($canCommit) {
					$pdo->commit();
				}
			} else {
				$service = null;
				$pdo->rollBack();
			}
			$stmt = null;
			
		} catch (PDOException $e) {
			errorLog($e);
			$service = null;
		}
		return $service;
	}
	
	function getServices($start = null, $stop = null, $page = 0, $pageSize = 10, $type = null, $patient = null, $pdo = null)
	{
		$dateStart = ($start == null) ? '1970-01-01' : date("Y-m-d", strtotime($start));
		$dateStop = ($stop == null) ? date("Y-m-d") : date("Y-m-d", strtotime($stop));
		
		if (isset($start, $stop)) {
			//swap the dates, since mysql does not really obey negative date between`s
			//and assign in a single line. double line assignment fails
			//because by the time the later comparison is called,
			//they would be equal and things are not consistent anymore
			list($dateStart, $dateStop) = [min($dateStart, $dateStop), max($dateStart, $dateStop)];
		}
		$extraFilter = "";
		if ($patient !== null) {
			$extraFilter = " patient_id = " . $patient . " AND ";
		}
		if ($type == 'open') {
			$sql = "SELECT * FROM patient_dentistry WHERE $extraFilter DATE(request_date) BETWEEN '$dateStart' AND '$dateStop' AND approved IS FALSE AND `status` IS FALSE AND cancelled IS FALSE";
		} else if ($type === "approval") {
			$sql = "SELECT * FROM patient_dentistry WHERE $extraFilter DATE(request_date) BETWEEN '$dateStart' AND '$dateStop' AND approved IS FALSE AND `status` IS TRUE";
		} else {
			$sql = "SELECT * FROM patient_dentistry WHERE $extraFilter DATE(request_date) BETWEEN '$dateStart' AND '$dateStop'";
		}
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
		$scans = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$scans[] = $this->get($row['id'], $pdo);
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			errorLog($e);
			$scans = [];
		}
		$results = (object)null;
		$results->data = $scans;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function getServicesById($sid, $start = null, $stop = null, $pdo = null)
	{
		if ($start == null) {
			$dateStart = '1970-01-01';
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
		$scans = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_dentistry WHERE DATE(request_date) BETWEEN '$dateStart' AND '$dateStop' AND dentistry_ids='" . $sid . "' ORDER BY DATE(request_date) DESC";
			//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$scan = new PatientDentistry();
				$scan->setId($row['id']);
				$scan->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, null));
				$scan->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by_id'], false, $pdo));
				
				$scan->setRequestDate($row['request_date']);
				$scan->setApproved((bool)$row['approved']);
				$scan->setRequestCode($row['requestCode']);
				$scan->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				
				$rqs = explode(",", $row['dentistry_ids']);
				$rq_scans = array();
				foreach ($rqs as $s) {
					$rq_scans[] = (new DentistryDAO())->get($s, $pdo);
				}
				
				$scan->setServices($rq_scans);
				$scan->setRequestNote($row['request_note']);
				$scan->setApprovedBy((new StaffDirectoryDAO())->getStaff($row['approved_by_id'], false, $pdo));
				$scan->setDateLastModified($row['date_last_modified']);
				$scan->setNotes((new PatientDentistryNoteDAO())->getDentistryNotes($row['id'], $pdo));
				$scan->setStatus((bool)$row['status']);
				$scan->setApprovedDate($row['approved_date']);
				
				$scan->setCancelled((bool)$row['cancelled']);
				$scan->setCanceledBy((new StaffDirectoryDAO())->getStaff($row['canceled_by_id'], false, $pdo));
				$scan->setDateCanceled($row['cancel_date']);
				
				$scans[] = $scan;
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$scans = [];
		}
		return $scans;
	}
	
	function search($filter, $page = 0, $pageSize = 10, $pdo = null)
	{
		$sql = "SELECT s.* FROM patient_dentistry s LEFT JOIN patient_demograph d ON d.patient_ID=s.patient_id WHERE d.active IS TRUE AND (s.requestCode LIKE '%$filter%' OR d.patient_ID LIKE '%$filter%' OR d.fname LIKE '%$filter%' OR d.lname LIKE '%$filter%')";
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
		$scans = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT s.* FROM patient_dentistry s LEFT JOIN patient_demograph d ON d.patient_ID=s.patient_id WHERE d.active IS TRUE AND (s.requestCode LIKE '%$filter%' OR d.patient_ID LIKE '%$filter%' OR d.fname LIKE '%$filter%' OR d.lname LIKE '%$filter%') LIMIT $offset, $pageSize";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$service = new PatientDentistry();
				$service->setId($row['id']);
				$service->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, null));
				$service->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by_id'], false, $pdo));
				
				$service->setRequestDate($row['request_date']);
				$service->setApproved((bool)$row['approved']);
				$service->setRequestCode($row['requestCode']);
				$service->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				
				$rqs = array_filter(explode(",", $row['dentistry_ids']));
				$rq_scans = array();
				foreach ($rqs as $s) {
					$rq_scans[] = (new DentistryDAO())->get($s, $pdo);
				}
				
				$service->setServices($rq_scans);
				$service->setRequestNote($row['request_note']);
				$service->setApprovedBy((new StaffDirectoryDAO())->getStaff($row['approved_by_id'], false, $pdo));
				$service->setDateLastModified($row['date_last_modified']);
				$service->setNotes((new PatientDentistryNoteDAO())->getDentistryNotes($row['id'], $pdo));
				$service->setStatus((bool)$row['status']);
				$service->setApprovedDate($row['approved_date']);
				
				$service->setCancelled((bool)$row['cancelled']);
				$service->setCanceledBy((new StaffDirectoryDAO())->getStaff($row['canceled_by_id'], false, $pdo));
				$service->setDateCanceled($row['cancel_date']);
				
				$scans[] = $service;
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$scans = [];
		}
		$results = (object)null;
		$results->data = $scans;
		$results->total = $total;
		$results->page = $page;
		return $results;
	}
	
	function findByDateCategory($from = null, $to = null, $category_id = null, $page, $pageSize, $pdo = null)
	{
		$f = ($from == null) ? date("Y-m-d") : $from;
		$t = ($to == null) ? date("Y-m-d") : $to;
		$cid = ($category_id == null) ? '' : ' AND sc.id=' . $category_id;
		
		$sql = "SELECT ps.* FROM patient_dentistry ps LEFT JOIN dentistry s ON s.id=ps.dentistry_ids LEFT JOIN dentistry_category sc ON sc.id=s.category_id WHERE ps.cancelled IS NOT TRUE AND DATE(ps.request_date) BETWEEN DATE('$f') AND DATE('$t'){$cid}";
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
		
		$scans = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " ORDER BY DATE(ps.request_date) DESC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$service = new PatientDentistry();
				$service->setId($row['id']);
				$service->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, null));
				$service->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by_id'], false, $pdo));
				
				$service->setRequestDate($row['request_date']);
				$service->setApproved((bool)$row['approved']);
				$service->setRequestCode($row['requestCode']);
				$service->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				
				$rqs = explode(",", $row['dentistry_ids']);
				$rq_scans = array();
				foreach ($rqs as $s) {
					$rq_scans[] = (new DentistryDAO())->get($s, $pdo);
				}
				
				$service->setServices($rq_scans);
				$service->setRequestNote($row['request_note']);
				$service->setApprovedBy((new StaffDirectoryDAO())->getStaff($row['approved_by_id'], false, $pdo));
				$service->setDateLastModified($row['date_last_modified']);
				$service->setNotes((new PatientDentistryNoteDAO())->getDentistryNotes($row['id'], $pdo));
				$service->setStatus((bool)$row['status']);
				$service->setApprovedDate($row['approved_date']);
				
				$service->setCancelled((bool)$row['cancelled']);
				$service->setCanceledBy((new StaffDirectoryDAO())->getStaff($row['canceled_by_id'], false, $pdo));
				$service->setDateCanceled($row['cancel_date']);
				
				$scans[] = $service;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$scans = [];
			$stmt = null;
		}
		
		$results = (object)null;
		$results->data = $scans;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function exportByDateCategory($from = null, $to = null, $category_id = null, $page, $pageSize, $pdo = null)
	{
		$f = ($from == null) ? date("Y-m-d") : $from;
		$t = ($to == null) ? date("Y-m-d") : $to;
		$cid = ($category_id == null) ? '' : ' AND sc.id=' . $category_id;
		
		$sql = "SELECT ps.* FROM patient_dentistry ps LEFT JOIN dentistry s ON s.id=ps.dentistry_ids LEFT JOIN dentistry_category sc ON sc.id=s.category_id WHERE ps.cancelled IS NOT TRUE AND DATE(ps.request_date) BETWEEN DATE('$f') AND DATE('$t'){$cid}";
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
		
		$scans = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " ORDER BY DATE(ps.request_date) DESC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$report = (object)null;
				
				$report->Date = date('jS M, Y', strtotime($row['request_date']));
				$report->Dentistry = (new DentistryDAO())->get($row['dentistry_ids'], $pdo)->getName();
				$report->Staff = (new StaffDirectoryDAO())->getStaff($row['requested_by_id'], false, $pdo)->getFullname();
				$report->Patient = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, null)->getFullname();
				$report->PatientID = $row['patient_id'];
				$report->Scheme = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, null)->getScheme()->getName();
				$report->Amount = (new InsuranceItemsCostDAO())->getItemPriceByCode((new DentistryDAO())->get($row['dentistry_ids'], $pdo)->getCode(), $row['patient_id'], true, $pdo);
				
				$scans[] = $report;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$scans = [];
			$stmt = null;
		}
		
		$results = (object)null;
		$results->data = $scans;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function getPatientRequests($pid, $page = 0, $pageSize = 10, $pdo = null)
	{
		$sql = "SELECT * FROM patient_dentistry WHERE patient_id = $pid";
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
		$services = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$service = new PatientDentistry($row['id']);
				$service->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, null));
				$service->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by_id'], false, $pdo));
				$service->setRequestCode($row['requestCode']);
				$service->setRequestDate($row['request_date']);
				$service->setApproved((bool)$row['approved']);
				$service->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				
				$rqs = array_filter(explode(",", $row['dentistry_ids']));
				$rq_scans = array();
				foreach ($rqs as $s) {
					$rq_scans[] = (new DentistryDAO())->get($s, $pdo);
				}
				
				$service->setServices($rq_scans);
				$service->setRequestNote($row['request_note']);
				$service->setApprovedBy((new StaffDirectoryDAO())->getStaff($row['approved_by_id'], false, $pdo));
				$service->setDateLastModified($row['date_last_modified']);
				$service->setNotes((new PatientDentistryNoteDAO())->getDentistryNotes($row['id'], $pdo));
				$service->setStatus((bool)$row['status']);
				$service->setApprovedDate($row['approved_date']);
				
				$service->setCancelled((bool)$row['cancelled']);
				$service->setCanceledBy((new StaffDirectoryDAO())->getStaff($row['canceled_by_id'], false, $pdo));
				$service->setDateCanceled($row['cancel_date']);
				
				$services[] = $service;
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$services = [];
		}
		$results = (object)null;
		$results->data = $services;
		$results->total = $total;
		$results->page = $page;
		return $results;
	}
	
	function approve($scan, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			
			$approved_by_id = $scan->getApprovedBy()->getId();
			$sql = "UPDATE patient_dentistry SET approved = TRUE, approved_date='" . $scan->getApprovedDate() . "', approved_by_id = $approved_by_id WHERE id = " . $scan->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$scan->setId($pdo->lastInsertId());
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$scan = null;
		}
		return $scan;
	}
	
	function approvePartial($request, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE patient_dentistry SET `status` = TRUE WHERE id = " . $request->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$request->setId($pdo->lastInsertId());
			}
			$stmt = null;
		} catch (PDOException $e) {
			$request = null;
		}
		return $request;
	}
	
	
	function cancel($service, $pdo = null)
	{
		//$service = new PatientDentistry();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			
			$pdo->beginTransaction();
			$canceled_by_id = $service->getCanceledBy()->getId();
			$sql = "UPDATE patient_dentistry SET cancelled = TRUE, cancel_date=NOW(), canceled_by_id = $canceled_by_id WHERE id = " . $service->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
			$patient_id = $service->getPatient()->getId();
			$patient = (new PatientDemographDAO())->getPatient($patient_id, false, $pdo, null);
			
			$cost = (new InsuranceItemsCostDAO())->getItemPriceByCode($service->getServices()[0]->getCode(), $patient_id, true, $pdo);
			
			$bil = new Bill();
			$bil->setPatient($patient);
			$bil->setDescription("" . $service->getServices()[0]->getName());
			$bil->setItem($service->getServices()[0]);
			$bil->setSource((new BillSourceDAO())->findSourceById(14, $pdo));
			$bil->setTransactionType("reversal");
			$bil->setTransactionDate(date("Y-m-d H:i:s"));
			$bil->setDueDate($service->getRequestDate());
			$bil->setActiveBill('not_active');
			$bil->setAmount(0 - $cost);
			$bil->setDiscounted(null);
			$bil->setDiscountedBy(null);
			$bil->setClinic($staff->getClinic());
			$bil->setBilledTo($patient->getScheme());
			
			
			$bill = (new BillDAO())->addBill($bil, 1, $pdo, null);
			if ($stmt->rowCount() == 1 && $bill !== null) {
				$stmt = null;
				$sql = null;
				$pdo->commit();
				return true;
			}
			error_log("ERROR: Something happened during cancellation!");
			$pdo->rollBack();
			return false;
		} catch (PDOException $e) {
			errorLog($e);
			return false;
		}
	}
	
	function reject($request, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE patient_dentistry SET `status` = FALSE WHERE id = " . $request->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$request->setId($pdo->lastInsertId());
			}
			$stmt = null;
		} catch (PDOException $e) {
			$request = null;
		}
		return $request;
	}
} 