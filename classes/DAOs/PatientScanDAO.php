<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/13/14
 * Time: 2:45 PM
 */
class PatientScanDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Scan.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ScanCategory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientScan.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ScanCategoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanAttachmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanNoteDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAntenatalUsagesDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ResourceDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
			
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function getScan($id, $pdo = null)
	{
		$scan = new PatientScan();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_scan WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$scan->setId($row['id']);
				$scan->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, null));
				$scan->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by_id'], FALSE, $pdo));

				$scan->setRequestDate($row['request_date']);
				$scan->setApproved((bool)$row['approved']);
				$scan->setCaptured((bool)$row['captured']);
				$scan->setRequestCode($row['requestCode']);
				$scan->setAppointment($row['appointment_id']);

				$scan->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));

				$scan->setScan((new ScanDAO())->getScan($row['scan_ids'], $pdo));
				$scan->setRequestNote($row['request_note']);
				$scan->setApprovedBy((new StaffDirectoryDAO())->getStaff($row['approved_by_id'], FALSE, $pdo));
				$scan->setDateLastModified($row['date_last_modified']);
				$scan->setAttachments((new PatientScanAttachmentDAO())->getScanAttachments($row['id'], $pdo));
				$scan->setNotes((new PatientScanNoteDAO())->getScanNotes($row['id'], $pdo));
				$scan->setStatus((bool)$row['status']);
				$scan->setApprovedDate($row['approved_date']);
				$scan->setCancelled((bool)$row['cancelled']);
				$scan->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				$scan->setCanceledBy((new StaffDirectoryDAO())->getStaff($row['canceled_by_id'], FALSE, $pdo));
				$scan->setDateCanceled($row['cancel_date']);
				if(!is_null($row['bill_line_id'])){
					$bills_ = array_filter(explode(',', $row['bill_line_id']));
					$bills = [];
					foreach ($bills_ as $b){
						$bills[] = (new BillDAO())->getBill($b, FALSE, $pdo);
					}
					if(count($bills)==1){
						$scan->setBill($bills[0]);
					} else {
						$scan->setBill($bills);
					}
				}
				$resource = (new ResourceDAO())->getResource($row['resource_id'], $pdo);
				$startDate = $row['schedule_date_start'];
				$endDate = $row['schedule_date_end'];
				$scheduledBy = (new StaffDirectoryDAO())->getStaff($row['scheduled_by_id'], FALSE, $pdo);
				$scan->setResource($resource)->setScheduleDateStart($startDate)->setScheduleDateEnd($endDate)->setScheduledOn($row['scheduled_on'])->setScheduledBy($scheduledBy);
				
			} else {
				$scan = null;
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$scan = null;
		}
		//error_log(json_encode($scan));
		return $scan;
	}

	function addScan($scan, $charged=false, $pdo = null)
	{
	  //$scan = new PatientScan();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$canCommit = !$pdo->inTransaction();
			try {
				$pdo->beginTransaction();
			} catch (PDOException $ef) {
				//
			}
			
			$patient_id = $scan->getPatient()->getId();
			$encounter = $scan->getEncounter() ? $scan->getEncounter()->getId() : "NULL";
			$encounterObject = $scan->getEncounter();
			
			$bill = null;
			$s = is_array($scan->getScan()) ? $scan->getScan()[0] : $scan->getScan();
			
			$scan_id_ = $s->getId();
			$cost = (new InsuranceItemsCostDAO())->getItemPriceByCode($s->getCode(), $patient_id, true, $pdo);
			$summary = "Radiology: " . $s->getName() . " requested.";
			
			if (!$charged) {
				$bil = new Bill();
				$bil->setPatient($scan->getPatient());
				$bil->setDescription("Radiology charges: " . $s->getName());
				$bil->setItem($s);
				$bil->setSource((new BillSourceDAO())->findSourceById(7, $pdo));
				$bil->setTransactionType("credit");
				$bil->setAmount($cost);
				$bil->setDiscounted(null);
				$bil->setDiscountedBy(null);
				$bil->setClinic(new Clinic(1));
				$bil->setBilledTo($scan->getPatient()->getScheme());
				$bil->setReferral($scan->getReferral());
				$serviceCentre = $scan->getServiceCentre();
				$bil->setCostCentre($serviceCentre ? $serviceCentre->getCostCentre() : null);
				
				$bill = (new BillDAO())->addBill($bil, 1, $pdo);
			}
			$scan_id = $scan_id_;
			$requested_by_id = $scan->getRequestedBy()->getId();
			$request_date = $scan->getRequestDate();
			$referral_id = ($scan->getReferral() !== null) ? $scan->getReferral()->getId() : "NULL";
			$request_note = escape($scan->getRequestNote());
			$serviceCentreId = $scan->getServiceCentre() ? $scan->getServiceCentre()->getId() : "NULL";
			
			$billLine = $bill != null && $bill->getId() != null ? (is_array($bill->getId()) ? "'". implode(array_map('intval', $bill->getId()),",") ."'" : $bill->getId()) : "NULL";
			$sql = "INSERT INTO patient_scan (patient_id, scan_ids, request_note, requested_by_id, request_date, referral_id, encounter_id, service_centre_id, bill_line_id) VALUES ('$patient_id', '$scan_id', '$request_note', '$requested_by_id','$request_date', $referral_id, $encounter, $serviceCentreId, $billLine)";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$scan->setId($pdo->lastInsertId());
			
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VisitNotes.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
			$note = new VisitNotes();
			$note->setNotedBy($scan->getRequestedBy());
			$note->setDateOfEntry(time());
			$note->setDescription($summary);
			$note->setHospital(new Clinic(1));
			$note->setNoteType('inv');
			$note->setPatient($scan->getPatient());
			$note->setEncounter($scan->getEncounter());
			
			(new VisitNotesDAO())->addNote($note, $pdo);
			
			//add to queue
			try {
				$queue = new PatientQueue();
				$queue->setType("Imaging");
				$queue->setSubType($s->getCategory()->getName());
				$queue->setPatient($scan->getPatient());
				(new PatientQueueDAO())->addPatientQueue($queue, $pdo);
			} catch (PDOException $e) {
				error_log("Error adding to imaging queue");
			}
			
			if ($stmt->rowCount() == 1 && $bill !== null && !$charged) {
				if ($canCommit) {
					$pdo->commit();
				}
				return $scan;
			} else if ($stmt->rowCount() == 1 && $charged) {
				if ($canCommit) {
					$pdo->rollBack();
				}
				return $scan;
			} else {
				$scan = null;
				if ($canCommit) {
					$pdo->rollBack();
				}
			}
		} catch (PDOException $e) {
			errorLog($e);
			$scan = null;
		}
		return $scan;
	}

	function getScans($start = null, $stop = null, $page = 0, $pageSize = 10, $type = null, $patient = null, $categoryId=null,$is_Admitted=null, $pdo = null)
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
		$extraFilter = "";
		if ($patient !== null) {
			$extraFilter = " ps.patient_id = " . $patient . " AND ";
		}
		
		if($categoryId !== null){
			$extraFilter .= "s.category_id=$categoryId AND ";
		}
		
		if ($is_Admitted != null){
			$extraFilter = "IS_ADMITTED(pd.patient_ID) AND";
		}
		if ($type == 'open') {
			$sql = "SELECT ps.* FROM patient_scan ps LEFT JOIN scan s ON s.id=ps.scan_ids LEFT JOIN scan_category sc ON sc.id=s.category_id LEFT JOIN patient_demograph pd ON pd.patient_ID=ps.patient_id WHERE $extraFilter DATE(request_date) BETWEEN '$dateStart' AND '$dateStop' AND approved IS FALSE AND `status` IS FALSE AND cancelled IS FALSE AND pd.active IS TRUE";
		} else if ($type === "approval") {
			$sql = "SELECT ps.* FROM patient_scan ps LEFT JOIN scan s ON s.id=ps.scan_ids LEFT JOIN scan_category sc ON sc.id=s.category_id LEFT JOIN patient_demograph pd ON pd.patient_ID=ps.patient_id WHERE $extraFilter DATE(request_date) BETWEEN '$dateStart' AND '$dateStop' AND approved IS FALSE AND `status` IS TRUE AND pd.active IS TRUE";
		} else if ($type === "cancelled") {
			$sql = "SELECT ps.* FROM patient_scan ps LEFT JOIN scan s ON s.id=ps.scan_ids LEFT JOIN scan_category sc ON sc.id=s.category_id LEFT JOIN patient_demograph pd ON pd.patient_ID=ps.patient_id WHERE $extraFilter DATE(request_date) BETWEEN '$dateStart' AND '$dateStop' AND `status` IS FALSE AND pd.active IS TRUE";
		} else if ($type === "scheduled") {
			$sql = "SELECT ps.* FROM patient_scan ps LEFT JOIN scan s ON s.id=ps.scan_ids LEFT JOIN scan_category sc ON sc.id=s.category_id LEFT JOIN patient_demograph pd ON pd.patient_ID=ps.patient_id WHERE $extraFilter DATE(request_date) BETWEEN '$dateStart' AND '$dateStop' AND `scheduled_on` IS NOT NULL AND pd.active IS TRUE";
		} else if ($type === "awaiting") {
			$sql = "SELECT ps.* FROM patient_scan ps LEFT JOIN scan s ON s.id=ps.scan_ids LEFT JOIN scan_category sc ON sc.id=s.category_id LEFT JOIN patient_demograph pd ON pd.patient_ID=ps.patient_id WHERE $extraFilter DATE(request_date) BETWEEN '$dateStart' AND '$dateStop' AND ps.captured IS TRUE AND ps.approved IS FALSE AND pd.active IS TRUE";
		} else {
			$sql = "SELECT ps.* FROM patient_scan ps LEFT JOIN scan s ON s.id=ps.scan_ids LEFT JOIN scan_category sc ON sc.id=s.category_id LEFT JOIN patient_demograph pd ON pd.patient_ID=ps.patient_id WHERE $extraFilter DATE(request_date) BETWEEN '$dateStart' AND '$dateStop' AND pd.active IS TRUE";
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
				$scans[] = $this->getScan($row['id'], $pdo);
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

	function getScansById($sid, $start = null, $stop = null, $pdo = null)
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
			$sql = "SELECT * FROM patient_scan WHERE DATE(request_date) BETWEEN '$dateStart' AND '$dateStop' AND scan_ids='" . $sid . "' ORDER BY DATE(request_date) DESC";
//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$scans[] = $this->getScan($row['id'], $pdo);
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$scans = [];
		}
		return $scans;
	}

	function findScans($filter, $page = 0, $pageSize = 10, $pdo = null)
	{
		$sql = "SELECT s.* FROM patient_scan s LEFT JOIN patient_demograph d ON d.patient_ID=s.patient_id WHERE d.active IS TRUE AND (s.requestCode LIKE '%$filter%' OR d.patient_ID LIKE '%$filter%' OR d.fname LIKE '%$filter%' OR d.lname LIKE '%$filter%')";
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
			$sql = "SELECT s.* FROM patient_scan s LEFT JOIN patient_demograph d ON d.patient_ID=s.patient_id WHERE d.active IS TRUE AND (s.requestCode LIKE '%$filter%' OR d.patient_ID LIKE '%$filter%' OR d.fname LIKE '%$filter%' OR d.lname LIKE '%$filter%') LIMIT $offset, $pageSize";

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$scan = new PatientScan();
				$scan->setId($row['id']);
				$scan->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, null));
				$scan->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by_id'], FALSE, $pdo));

				$scan->setRequestDate($row['request_date']);
				$scan->setApproved((bool)$row['approved']);
				$scan->setCaptured((bool)$row['captured']);
				$scan->setRequestCode($row['requestCode']);
				$scan->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				$scan->setScan((new ScanDAO())->getScan($row['scan_ids'], $pdo));
				$scan->setRequestNote($row['request_note']);
				$scan->setApprovedBy((new StaffDirectoryDAO())->getStaff($row['approved_by_id'], FALSE, $pdo));
				$scan->setDateLastModified($row['date_last_modified']);
				$scan->setAttachments((new PatientScanAttachmentDAO())->getScanAttachments($row['id'], $pdo));
				$scan->setNotes((new PatientScanNoteDAO())->getScanNotes($row['id'], $pdo));
				$scan->setStatus((bool)$row['status']);
				$scan->setApprovedDate($row['approved_date']);

				$scan->setCancelled((bool)$row['cancelled']);
				$scan->setCanceledBy((new StaffDirectoryDAO())->getStaff($row['canceled_by_id'], FALSE, $pdo));
				$scan->setDateCanceled($row['cancel_date']);
				$scan->setBill( (new BillDAO())->getBill($row['bill_line_id'], FALSE, $pdo) );

				$scans[] = $scan;
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

	function findScansByDateCategory($from = null, $to = null, $category_id = null, $page, $pageSize, $pdo = null)
	{
		$f = ($from == null) ? date("Y-m-d") : $from;
		$t = ($to == null) ? date("Y-m-d") : $to;
		$cid = ($category_id == null) ? '' : ' AND sc.id=' . $category_id;

		$sql = "SELECT ps.* FROM patient_scan ps LEFT JOIN scan s ON s.id=ps.scan_ids LEFT JOIN scan_category sc ON sc.id=s.category_id WHERE ps.cancelled IS NOT TRUE AND DATE(ps.request_date) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid}";
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
			$sql = "SELECT ps.*, CONCAT_WS(' ', sd.firstname, sd.lastname) AS staffFullName, CONCAT_WS(' ', pd.lname, pd.mname, pd.fname) AS patientFullName, isc.scheme_name, s.billing_code, s.name AS scanName, cc.name AS service_center FROM patient_scan ps LEFT JOIN patient_demograph pd ON pd.patient_ID=ps.patient_id LEFT JOIN staff_directory sd ON sd.staffId=ps.requested_by_id LEFT JOIN scan s ON s.id=ps.scan_ids LEFT JOIN scan_category sc ON sc.id=s.category_id LEFT JOIN service_centre cc ON cc.id=ps.service_centre_id LEFT JOIN insurance i ON i.patient_id=pd.patient_ID LEFT JOIN insurance_schemes isc ON isc.id=i.insurance_scheme WHERE ps.cancelled IS NOT TRUE AND DATE(ps.request_date) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid} ORDER BY DATE(ps.request_date) DESC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$scans[] = (object)$row;
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

	function exportScansByDateCategory($from = null, $to = null, $category_id = null, $page, $pageSize, $pdo = null)
	{
		$f = ($from == null) ? date("Y-m-d") : $from;
		$t = ($to == null) ? date("Y-m-d") : $to;
		$cid = ($category_id == null) ? '' : ' AND sc.id=' . $category_id;

		$sql = "SELECT ps.* FROM patient_scan ps LEFT JOIN scan s ON s.id=ps.scan_ids LEFT JOIN scan_category sc ON sc.id=s.category_id WHERE ps.cancelled IS NOT TRUE AND DATE(ps.request_date) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid}";
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
			$sql = "SELECT ps.* FROM patient_scan ps LEFT JOIN scan s ON s.id=ps.scan_ids LEFT JOIN scan_category sc ON sc.id=s.category_id WHERE ps.cancelled IS NOT TRUE AND DATE(ps.request_date) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid} ORDER BY DATE(ps.request_date) DESC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$report = (object)null;
				$report->Date = date('jS M, Y', strtotime($row['request_date']));
				$report->Scan = (new ScanDAO())->getScan($row['scan_ids'], $pdo)->getName();
				$report->Staff = (new StaffDirectoryDAO())->getStaff($row['requested_by_id'], FALSE, $pdo)->getFullname();
				$report->Patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, null)->getFullname();
				$report->PatientID = $row['patient_id'];
				$report->Scheme = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, null)->getScheme()->getName();
				$report->Amount = (new InsuranceItemsCostDAO())->getItemPriceByCode((new ScanDAO())->getScan($row['scan_ids'], $pdo)->getCode(), $row['patient_id'], TRUE, $pdo);

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

	function getPatientScans($pid, $page = 0, $pageSize = 10, $pdo = null)
	{
		$sql = "SELECT * FROM patient_scan WHERE patient_id = $pid";
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
			$sql = "SELECT * FROM patient_scan WHERE patient_id = $pid ORDER BY request_date DESC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$scans[] = $this->getScan($row['id'], $pdo);
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

	function approveScan($scan, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;

			$approved_by_id = $scan->getApprovedBy()->getId();
			$sql = "UPDATE patient_scan SET approved = TRUE, approved_date='" . $scan->getApprovedDate() . "', approved_by_id = '$approved_by_id', captured=TRUE WHERE id = " . $scan->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				//$scan->setId($pdo->lastInsertId());
				return $scan;
			}
			$stmt = null;
			$sql = null; //is it necessary? does it save memory?
		} catch (PDOException $e) {
			$scan = null;
		}
		return $scan;
	}


	function capturedScan($scan, $pdo = null){
		try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$captured_by = $scan->getCapturedBy()->getId();
			$sql = "UPDATE patient_scan SET `captured` = TRUE, captured_by_id = '$captured_by', captured_date='". $scan->getCapturedDate(). "'  WHERE  id= ". $scan->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() >= 0){
				return $scan;
			}
		}catch (PDOException $e){
			$scan = null;
		}
		return $scan;
	}




	function cancelScanRequest($scan, $pdo = null)
	{
		//$scan = new PatientScan();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;

			$pdo->beginTransaction();
			$canceled_by_id = $scan->getCanceledBy()->getId();
			$sql = "UPDATE patient_scan SET cancelled = TRUE, cancel_date=NOW(), canceled_by_id = '$canceled_by_id' WHERE id = " . $scan->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], TRUE, $pdo);
			$patient_id = $scan->getPatient()->getId();
			$patient = (new PatientDemographDAO())->getPatient($patient_id, FALSE, $pdo, null);
			
			// checkBill obj verifies is the bills has been transferred  as well as reversed.
			// transaction will be success if transferred and reversed back to the patient and as well as if
			// bill has not been transferred before. Should fail if transferred but not reversed.
			$checkBill = (new BillDAO())->getTransferCreditOnly($scan->getBill()->getId(), true, $pdo);
			$billTransf = (new BillDAO())->checkBill($scan->getBill()->getId(), true, $pdo);
			if($scan->getBill()){
				(new BillDAO())->cancelRelatedItems($patient_id, $scan->getScan()->getCode(), $scan->getRequestDate(), $pdo);
				
				if(is_array($scan->getBill())){
					$checkBill1 = (new BillDAO())->getTransferCreditOnly($scan->getBill()[0]->getId(), true, $pdo);
					$billTransf1 = (new BillDAO())->checkBill($scan->getBill()[0]->getId(), true, $pdo);
					if ($billTransf1 && $checkBill1 == null){
						$pdo->rollBack();
						return false;
					}
					$cost = $scan->getBill()[0]->getAmount();
					$bil = new Bill();
					$bil->setPatient($patient);
					$bil->setInPatient($scan->getBill()[0]->getInPatient());
					$bil->setDescription("" . $scan->getScan()->getName());
					$bil->setItem($scan->getScan());
					$bil->setSource((new BillSourceDAO())->findSourceById(7, $pdo));
					$bil->setTransactionType("reversal");
					$bil->setCancelledBy((new StaffDirectory($_SESSION['staffID'])));
					$bil->setTransactionDate(date("Y-m-d H:i:s"));
					$bil->setCancelledOn(date("Y-m-d H:i:s"));
					$bil->setDueDate($scan->getRequestDate());
					$bil->setAmount(0 - $cost);
					$bil->setDiscounted(null);
					$bil->setDiscountedBy(null);
					$bil->setClinic($staff->getClinic());
					$bil->setBilledTo($scan->getBill()[0]->getBilledTo());
					
					if($checkBill1 == null){
						$bil->setParent($scan->getBill()[0]);
					}else{
						$bil->setParent($checkBill1);
					}
					
					$bil->setActiveBill('not_active');
					
					$bill1 = $bil->add(1, null, $pdo);
					//============
					$cost = $scan->getBill()[1]->getAmount();
					$checkBill2 = (new BillDAO())->getTransferCreditOnly($scan->getBill()[1]->getId(), true, $pdo);
					$billTransf2 = (new BillDAO())->checkBill($scan->getBill()[1]->getId(), true, $pdo);
					if ($billTransf2 && $checkBill2 == null){
						$pdo->rollBack();
						return false;
					}
					$bil = new Bill();
					$bil->setPatient($patient);
					$bil->setInPatient($scan->getBill()[1]->getInPatient());
					$bil->setDescription("" . $scan->getScan()->getName());
					$bil->setItem($scan->getScan());
					$bil->setSource((new BillSourceDAO())->findSourceById(7, $pdo));
					$bil->setTransactionType("reversal");
					$bil->setTransactionDate(date("Y-m-d H:i:s"));
					$bil->setCancelledOn(date("Y-m-d H:i:s"));
					$bil->setCancelledBy((new StaffDirectory($_SESSION['staffID'])));
					$bil->setDueDate($scan->getRequestDate());
					$bil->setAmount(0 - $cost);
					$bil->setDiscounted(null);
					$bil->setDiscountedBy(null);
					$bil->setClinic($staff->getClinic());
					$bil->setBilledTo($scan->getBill()[1]->getBilledTo());
					if($checkBill2 == null){
						$bil->setParent($scan->getBill()[1]);
					}else{
						$bil->setParent($checkBill2);
					}
					$bil->setActiveBill('not_active');
					
					$bill2 = $bil->add(1, null, $pdo);
					
					if ($stmt->rowCount() == 1 && $bill1 !== null && $bill2 !== null) {
						$pdo->commit();
						return TRUE;
					}
				
				} else {
					$cost = $scan->getBill()->getAmount();
					if ($billTransf && $checkBill == null){
						$pdo->rollBack();
						return false;
					}
					
					$bil = new Bill();
					$bil->setPatient($patient);
					$bil->setDescription("" . $scan->getScan()->getName());
					$bil->setItem($scan->getScan());
					$bil->setSource((new BillSourceDAO())->findSourceById(7, $pdo));
					$bil->setTransactionType("reversal");
					$bil->setTransactionDate(date("Y-m-d H:i:s"));
					$bil->setCancelledOn(date("Y-m-d H:i:s"));
					$bil->setCancelledBy((new StaffDirectory($_SESSION['staffID'])));
					$bil->setDueDate($scan->getRequestDate());
					$bil->setAmount(0 - $cost);
					$bil->setDiscounted(null);
					$bil->setDiscountedBy(null);
					$bil->setClinic($staff->getClinic());
					$bil->setBilledTo($patient->getScheme());
					if($checkBill == null){
						$bil->setParent($scan->getBill());
					}else{
						$bil->setParent($checkBill);
					}
					$bil->setActiveBill('not_active');
					
					$bill = (new BillDAO())->addBill($bil, 1, $pdo, null);
					
					if ($stmt->rowCount() == 1 && $bill !== null) {
						$pdo->commit();
						return TRUE;
					}
				}
			} else if($scan->getBill() == null){
				//we didn't charge: we either used a token or antenatal package
				
				//consider the antenatal scenario first
				require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/AntenatalEnrollmentDAO.php';
				$activeAntenatalInstance = (new AntenatalEnrollmentDAO())->getActiveInstance($patient->getId(), FALSE, $pdo);
				if($activeAntenatalInstance !== null){
					//if the patient has is enrolled into antenatal and the package has the items covered
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AntenatalPackageItem.php';
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackagesDAO.php';
					$thisItemCode = $scan->getScan()->getCode();
					$itemsCodes = [];
					
					$patientTokens = (new AntenatalPackagesDAO())->get($activeAntenatalInstance->getPackage()->getId(), $pdo)->getItems();
					foreach($patientTokens as $token){
						//$token = new AntenatalPackageItem();
						$itemsCodes[$token->getItemCode()] = $token->getUsage();
					}
					//yay!!! we know the item
					if(isset($itemsCodes[$thisItemCode])){
						$billQuantity = (int)1;
						$item_type = getAntenatalItemType($thisItemCode);
						// it's a reversal
						(new PatientAntenatalUsages())->setPatient($patient)->setItemCode($thisItemCode)->setItem($scan->getScan()->getId())->setType($item_type)->setAntenatal($activeAntenatalInstance)->setUsages(parseNumber(0 - $billQuantity))->setDateUsed(date(MainConfig::$mysqlDateTimeFormat))->add($pdo);
						$pdo->commit();
						return true;
					}
					$pdo->rollBack();
					return false;
				}
				
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PackageTokenUsage.php';
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PackageTokenDAO.php';
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PackageTokenUsageDAO.php';
				$thisItemCode = $scan->getScan()->getCode();
				$itemsCodes = [];
				//$itemTokens = [];
				$patientTokens = (new PackageTokenDAO())->forPatient($patient->getId(), $pdo);
				foreach ($patientTokens as $token) {
					$itemsCodes[] = $token->getItemCode();
				}
				if (in_array($thisItemCode, $itemsCodes)) {
					//we used a token:package
					$itemQuantity = (new PackageTokenDAO())->forPatientItem($thisItemCode, $patient->getId(), $pdo);
					$availableTokenItemQty = $itemQuantity->getRemainingQuantity();
					$billQuantity = 1; //
					
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
			return FALSE;
		} catch (PDOException $e) {
			errorLog($e);
			return FALSE;
		}
	}

	function approveScan_($scan, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE patient_scan SET `status` = TRUE WHERE id = " . $scan->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$scan->setId($pdo->lastInsertId());
			}
			$stmt = null;
		} catch (PDOException $e) {
			$scan = null;
		}
		return $scan;
	}

	function rejectScan($scan, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE patient_scan SET `status` = FALSE WHERE id = " . $scan->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$scan->setId($pdo->lastInsertId());
			}
			$stmt = null;
		} catch (PDOException $e) {
			$scan = null;
		}
		return $scan;
	}

	public function getEncounterScans($id, $pdo)
	{
		$scans = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_scan WHERE encounter_id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$scan = new PatientScan($row['id']);
				$scan->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, null));
				$scan->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by_id'], FALSE, $pdo));
				$scan->setRequestCode($row['requestCode']);
				$scan->setRequestDate($row['request_date']);
				$scan->setApproved((bool)$row['approved']);
				$scan->setCaptured((bool)$row['captured']);
				$scan->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				$scan->setScan((new ScanDAO())->getScan($row['scan_ids'], $pdo));
				$scan->setRequestNote($row['request_note']);
				$scan->setApprovedBy((new StaffDirectoryDAO())->getStaff($row['approved_by_id'], FALSE, $pdo));
				$scan->setDateLastModified($row['date_last_modified']);
				$scan->setAttachments((new PatientScanAttachmentDAO())->getScanAttachments($row['id'], $pdo));
				$scan->setNotes((new PatientScanNoteDAO())->getScanNotes($row['id'], $pdo));
				$scan->setStatus((bool)$row['status']);
				$scan->setApprovedDate($row['approved_date']);
				$scan->setCancelled((bool)$row['cancelled']);
				$scan->setCanceledBy((new StaffDirectoryDAO())->getStaff($row['canceled_by_id'], FALSE, $pdo));
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


	function getUnfulfilledScans($page = 0, $pageSize = 10, $start=null, $stop=null, $pdo = null)
	{
		$f = ($start == null) ? date("Y-m-d") : $start;
		$t = ($stop == null) ? date("Y-m-d") : $stop;
		$sql = "SELECT ps.* FROM patient_scan ps LEFT JOIN patient_demograph pd ON pd.patient_ID=ps.patient_id WHERE DATE(request_date) BETWEEN DATE('$f') AND DATE('$t') AND pd.active IS TRUE AND ps.approved IS FALSE AND ps.`status` IS FALSE AND ps.cancelled IS FALSE AND ps.captured is FALSE";
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
				$scan = new PatientScan();
				$scan->setId($row['id']);
				$scan->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, null));
				$scan->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by_id'], FALSE, $pdo));
				$scan->setRequestDate($row['request_date']);
				$scan->setApproved((bool)$row['approved']);
				$scan->setRequestCode($row['requestCode']);
				$scan->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				$scan->setScan((new ScanDAO())->getScan($row['scan_ids'], $pdo));
				$scan->setRequestNote($row['request_note']);
				$scan->setApprovedBy((new StaffDirectoryDAO())->getStaff($row['approved_by_id'], FALSE, $pdo));
				$scan->setDateLastModified($row['date_last_modified']);
				$scan->setAttachments((new PatientScanAttachmentDAO())->getScanAttachments($row['id'], $pdo));
				$scan->setNotes((new PatientScanNoteDAO())->getScanNotes($row['id'], $pdo));
				$scan->setStatus((bool)$row['status']);
				$scan->setApprovedDate($row['approved_date']);

				$scan->setCancelled((bool)$row['cancelled']);
				$scan->setCanceledBy((new StaffDirectoryDAO())->getStaff($row['canceled_by_id'], FALSE, $pdo));
				$scan->setDateCanceled($row['cancel_date']);

				$scans[] = $scan;
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

} 
