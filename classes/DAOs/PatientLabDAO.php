<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/17/14
 * Time: 1:10 PM
 */
class PatientLabDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			@session_start();
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientLabs.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabResult.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Lab.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AntenatalEnrollment.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabGroup.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabSpecimen.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabGroupDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabResultDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabSpecimenDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAntenatalUsagesDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/protect.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function getLab($id, $pdo = null)
	{
		$pl = new PatientLab();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//$sql = "SELECT * FROM patient_labs WHERE id = '$id'";
			$sql = "SELECT l.*, lr.id as rid, rq.service_centre_id FROM patient_labs l LEFT JOIN lab_result lr ON lr.patient_lab_id=l.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id LEFT JOIN lab_requests rq ON rq.lab_group_id=l.lab_group_id WHERE l.id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pl->setId($row['id']);
				//$labGroup = (new LabGroupDAO())->getLabGroup($row['lab_group_id'], FALSE, $pdo);
				$labGroup = (new LabGroupDAO())->getLabGroup($row['lab_group_id'], $row['patient_id'], true, $pdo);
				$pl->setLabGroup($labGroup); //a lab group object
				$pl->setTest((new LabDAO())->getLab($row['test_id'], FALSE, $pdo));
				$pl->setPerformedBy((new StaffDirectoryDAO())->getStaff($row['performed_by'], false, $pdo));
				$pl->setNotes($row['test_notes']);
				$pl->setStatus($row['_status']);
				
				$specimensss = explode(",", $row['test_specimen_ids']);
				$specimens = array();
				foreach ($specimensss as $s) {
					if (!empty($s)) {
						$specimens[] = (new LabSpecimenDAO())->getSpecimen($s, $pdo);
					}
				}
				
				$pl->setSpecimens($specimens);
				$pl->setSpecimenNote($row['specimen_notes']);
				$pl->setSpecimenDate($row['specimen_date']);
				$pl->setSpecimenCollectedBy((new StaffDirectoryDAO())->getStaff($row['specimen_collected_by'], false, $pdo));
				$pl->setTestDate($row['test_date']);
				
				$pl->setLabResult((new LabResultDAO())->getLabResult($row['rid'], false, $pdo));
				$pl->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				$pl->setReceived((bool)$row['received']);
				$pl->setReceivedBy((new StaffDirectoryDAO())->getStaff($row['specimen_received_by'], false, $pdo));
				$bills = [];
				foreach (array_filter(explode(",", $row['bill_line_id'])) as $b) {
					$bills[] = (new BillDAO())->getBill($b, true, $pdo);
				}
				
				$pl->setBill($row['bill_line_id'] != null ? $bills : NULL);
			} else {
				$pl = null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			error_log("PDO Error occurred");
		}
		return $pl;
	}
	
	
	function getLastLabResut($pid, $pdo = null)
	{
		$pl = new PatientLab();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//$sql = "SELECT * FROM patient_labs WHERE id = '$id'";
			$sql = "SELECT l.*, lr.id as rid, rq.service_centre_id FROM patient_labs l LEFT JOIN lab_result lr ON lr.patient_lab_id=l.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id LEFT JOIN lab_requests rq ON rq.lab_group_id=l.lab_group_id WHERE l.patient_id = $pid LIMIT 1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pl->setId($row['id']);
				//$labGroup = (new LabGroupDAO())->getLabGroup($row['lab_group_id'], FALSE, $pdo);
				//$labGroup = (new LabGroupDAO())->getLabGroup($row['lab_group_id'], $row['patient_id'], true, $pdo);
				//$pl->setLabGroup($labGroup); //a lab group object
				$pl->setTest((new LabDAO())->getLab($row['test_id'], FALSE, $pdo));
				$pl->setPerformedBy((new StaffDirectoryDAO())->getStaff($row['performed_by'], false, $pdo));
				$pl->setNotes($row['test_notes']);
				$pl->setStatus($row['_status']);
				$pl->setTestDate($row['test_date']);
				
				$pl->setLabResult((new LabResultDAO())->getLabResult($row['rid'], false, $pdo));
				//$pl->setReceived((bool)$row['received']);
				
			} else {
				$pl = null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			error_log("PDO Error occurred");
		}
		return $pl;
	}
	
	
	
	function getLabsToApprove($pdo = null)
	{
		$labRequests = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT pl.* FROM patient_labs pl LEFT JOIN lab_result lr ON lr.patient_lab_id=pl.id WHERE lr.approved IS FALSE";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$labRequests[] = $this->getLab($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
		}
		return $labRequests;
	}
	
	public function getPatientLabsByGroupCode($groupId, $patientId,  $getFull = false, $pdo = null)
	{
		$labRequests = array();
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT pl.*, lr.id AS rid FROM patient_labs pl LEFT JOIN lab_result lr ON lr.patient_lab_id=pl.id WHERE lab_group_id= '" . $groupId . "' AND pl.patient_id='$patientId'";/*AND  lr.approved IS TRUE */
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pl = new PatientLab();
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo);
					$labConf = (new LabDAO())->getLab($row['test_id'], false, $pdo);
					$LabGroup = (new LabGroupDAO())->getLabGroup($row['lab_group_id'], $row['patient_id'], false, $pdo);
					$specimens = array();
					foreach (explode(",", $row['test_specimen_ids']) as $s) {
						if (!empty($s))
							$specimens[] = (new LabSpecimenDAO())->getSpecimen($s, $pdo);
					}
					$sCollected = (new StaffDirectoryDAO())->getStaff($row['specimen_collected_by'], false, $pdo);
					$performedBy = (new StaffDirectoryDAO())->getStaff($row['performed_by'], false, $pdo);
					$lResult = (new LabResultDAO())->getLabResult($row['rid'], false, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$labConf = new Lab($row['test_id']);
					$LabGroup = new LabGroup($row['lab_group_id']);
					
					$specimens = array();
					foreach (explode(",", $row['test_specimen_ids']) as $s) {
						if (!empty($s)) {
							$specimens[] = new LabSpecimen($s);
						}
					}
					$sCollected = new StaffDirectory($row['performed_by']);
					$performedBy = new StaffDirectory($row['performed_by']);
					$lResult = $row['rid'] === null ? null : new LabResult($row['rid']);
				}
				$pl->setId($row['id']);
				$pl->setPatient($pat);
				$pl->setTest($labConf);
				$pl->setLabGroup($LabGroup); //a lab group object
				$pl->setPerformedBy($performedBy);
				$pl->setNotes($row['test_notes']);
				$pl->setSpecimens($specimens);
				$pl->setSpecimenCollectedBy($sCollected);
				$pl->setSpecimenNote($row['specimen_notes']);
				$pl->setSpecimenDate($row['specimen_date']);
				$pl->setTestDate($row['test_date']);
				//				$pl->setApproveDate($row['approved_date']);
				$pl->setLabResult($lResult);
				$pl->setStatus($row['_status']);
				$pl->setReceived((bool)$row['received']);
				$pl->setReceivedBy((new StaffDirectoryDAO())->getStaff($row['specimen_received_by'], false, $pdo));
				$pl->setBill((new BillDAO())->getBill($row['bill_line_id'], false, $pdo));
				
				$labRequests[] = $pl;
			}
		} catch (PDOException $e) {
			errorLog($e);
		}
		return $labRequests;
	}
	
	
	
	public function getPatientSingleLabsByGroupCode($groupId, $patientId=null, $lid, $getFull = false, $pdo = null)
	{
		$labRequests = array();
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT pl.*, lr.id AS rid FROM patient_labs pl LEFT JOIN lab_result lr ON lr.patient_lab_id=pl.id WHERE lab_group_id= '" . $groupId . "' AND pl.patient_id='$patientId' AND pl.id=$lid";/*AND  lr.approved IS TRUE */
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pl = new PatientLab();
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo);
					$labConf = (new LabDAO())->getLab($row['test_id'], false, $pdo);
					$LabGroup = (new LabGroupDAO())->getLabGroup($row['lab_group_id'], $row['patient_id'], false, $pdo);
					$specimens = array();
					foreach (explode(",", $row['test_specimen_ids']) as $s) {
						if (!empty($s))
							$specimens[] = (new LabSpecimenDAO())->getSpecimen($s, $pdo);
					}
					$sCollected = (new StaffDirectoryDAO())->getStaff($row['specimen_collected_by'], false, $pdo);
					$performedBy = (new StaffDirectoryDAO())->getStaff($row['performed_by'], false, $pdo);
					$lResult = (new LabResultDAO())->getLabResult($row['rid'], false, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$labConf = new Lab($row['test_id']);
					$LabGroup = new LabGroup($row['lab_group_id']);
					
					$specimens = array();
					foreach (explode(",", $row['test_specimen_ids']) as $s) {
						if (!empty($s)) {
							$specimens[] = new LabSpecimen($s);
						}
					}
					$sCollected = new StaffDirectory($row['performed_by']);
					$performedBy = new StaffDirectory($row['performed_by']);
					$lResult = $row['rid'] === null ? null : new LabResult($row['rid']);
				}
				$pl->setId($row['id']);
				$pl->setPatient($pat);
				$pl->setTest($labConf);
				$pl->setLabGroup($LabGroup); //a lab group object
				$pl->setPerformedBy($performedBy);
				$pl->setNotes($row['test_notes']);
				$pl->setSpecimens($specimens);
				$pl->setSpecimenCollectedBy($sCollected);
				$pl->setSpecimenNote($row['specimen_notes']);
				$pl->setSpecimenDate($row['specimen_date']);
				$pl->setTestDate($row['test_date']);
				//				$pl->setApproveDate($row['approved_date']);
				$pl->setLabResult($lResult);
				$pl->setStatus($row['_status']);
				$pl->setReceived((bool)$row['received']);
				$pl->setReceivedBy((new StaffDirectoryDAO())->getStaff($row['specimen_received_by'], false, $pdo));
				$pl->setBill((new BillDAO())->getBill($row['bill_line_id'], false, $pdo));
				
				$labRequests[] = $pl;
			}
		} catch (PDOException $e) {
			errorLog($e);
		}
		return $labRequests;
	}
	
	
	function getLabWithoutResult($page, $pageSize, $sort, $lab_centre = null, $lab_category = null, $getFUll = false, $patient = null, $is_Admitted = null,  $pdo = null)
	{
		$filter = ($lab_centre != null ? " AND service_centre_id=$lab_centre" : "");
		$cat_filter = ($lab_category != null ? " AND ltc.category_id=$lab_category" : "");
		$sort_filter = ($sort != '' ? " ORDER BY FIELD(`urgent`, TRUE) DESC, ls.time_entered " . strtoupper($sort) : " ORDER BY FIELD(`urgent`, TRUE) DESC");
		
		$extraFilter = "";
		if ($patient !== null) {
			$extraFilter = " pl.patient_id = " . $patient . " AND ";
		}
		
		$isAdmittedFilter1 = "";
		$isAdmittedFilter2 = "";
		if ($is_Admitted != null){
		   $isAdmittedFilter1 = " LEFT JOIN patient_demograph p ON pl.patient_id=p.patient_ID";
		   $isAdmittedFilter2 = "AND IS_ADMITTED(p.patient_ID)";
		   
		}
		
		$sql = "SELECT pl.*, ls.urgent FROM patient_labs pl LEFT JOIN lab_result lr ON lr.patient_lab_id=pl.id LEFT JOIN lab_requests ls ON pl.lab_group_id=ls.lab_group_id LEFT JOIN labtests_config ltc ON ltc.id=pl.test_id $isAdmittedFilter1 WHERE $extraFilter lr.id IS NULL AND pl._status = 'open' $filter$cat_filter$isAdmittedFilter2";
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
		
		$labs = array();
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			
			$sql = "SELECT pl.*, ls.urgent FROM patient_labs pl LEFT JOIN lab_result lr ON lr.patient_lab_id=pl.id LEFT JOIN lab_requests ls ON pl.lab_group_id=ls.lab_group_id LEFT JOIN labtests_config ltc ON ltc.id=pl.test_id $isAdmittedFilter1 WHERE $extraFilter lr.id IS NULL AND pl._status = 'open' $filter$cat_filter$isAdmittedFilter2 $sort_filter LIMIT $offset, $pageSize";
			// $sql = "SELECT pl.* FROM patient_labs pl LEFT JOIN lab_result lr ON lr.patient_lab_id=pl.id WHERE lr.id IS NULL AND pl._status = 'open' LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pl = new PatientLab();
				if ($getFUll) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo);
					$labConf = (new LabDAO())->getLab($row['test_id'], false, $pdo);
					$LabGroup = (new LabGroupDAO())->getLabGroup($row['lab_group_id'],$row['patient_id'], false, $pdo);
					$specimens = array();
					foreach (explode(",", $row['test_specimen_ids']) as $s) {
						if (!empty($s)) {
							$specimens[] = (new LabSpecimenDAO())->getSpecimen($s, $pdo);
						}
					}
					$sCollected = (new StaffDirectoryDAO())->getStaff($row['specimen_collected_by'], false, $pdo);
					$performedBy = (new StaffDirectoryDAO())->getStaff($row['performed_by'], false, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$labConf = new Lab($row['test_id']);
					$LabGroup = new LabGroup($row['lab_group_id']);
					
					$specimens = array();
					foreach (explode(",", $row['test_specimen_ids']) as $s) {
						if (!empty($s)) {
							$specimens[] = new LabSpecimen($s);
						}
					}
					$sCollected = new StaffDirectory($row['performed_by']);
					$performedBy = new StaffDirectory($row['performed_by']);
				}
				$pl->setId($row['id']);
				$pl->setPatient($pat);
				$pl->setTest($labConf);
				$pl->setLabGroup($LabGroup); //a lab group object
				$pl->setPerformedBy($performedBy);
				$pl->setNotes($row['test_notes']);
				$pl->setSpecimens($specimens);
				$pl->setSpecimenCollectedBy($sCollected);
				$pl->setSpecimenNote($row['specimen_notes']);
				$pl->setSpecimenDate($row['specimen_date']);
				$pl->setTestDate($row['test_date']);
				$pl->setStatus($row['_status']);
				$pl->setReceived((bool)$row['received']);
				$pl->setReceivedBy((new StaffDirectoryDAO())->getStaff($row['specimen_received_by'], false, $pdo));
				$pl->setBill((new BillDAO())->getBill($row['bill_line_id'], false, $pdo));
				
				$labs[] = $pl;
			}
		} catch (PDOException $e) {
			errorLog($e);
			$labs = [];
			$stmt = null;
		}
		
		$results = (object)null;
		$results->data = $labs;
		$results->total = $total;
		$results->page = $page;
		unset($_SESSION['pid']);
		return $results;
	}
	
	function findLabRequestsByDate($start = null, $stop = null, $page = 0, $pageSize = 10, $getFUll = false, $pdo = null)
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
		
		$sql = "SELECT pl.*, lr.id as rid, l.id as lab_request_id, l.time_entered FROM lab_requests l LEFT JOIN patient_labs pl ON l.lab_group_id=pl.lab_group_id LEFT JOIN lab_result lr ON lr.patient_lab_id=pl.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id WHERE DATE(l.time_entered) BETWEEN '$dateStart' AND '$dateStop'";
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
		
		$labRequests = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT pl.*, lr.id as rid, l.id as lab_request_id, l.time_entered, l.urgent FROM lab_requests l LEFT JOIN patient_labs pl ON l.lab_group_id=pl.lab_group_id LEFT JOIN lab_result lr ON lr.patient_lab_id=pl.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id WHERE DATE(l.time_entered) BETWEEN '$dateStart' AND '$dateStop' ORDER BY l.time_entered ASC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pl = new PatientLab();
				if ($getFUll) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, true);
					$labConf = (new LabDAO())->getLab($row['test_id'], false, $pdo);
					$LabGroup = (new LabGroupDAO())->getLabGroup($row['lab_group_id'],$row['patient_id'], false, $pdo);
					$specimens = array();
					foreach (explode(",", $row['test_specimen_ids']) as $s) {
						if (!empty($s)) {
							$specimens[] = (new LabSpecimenDAO())->getSpecimen($s, $pdo);
						}
					}
					$sCollected = (new StaffDirectoryDAO())->getStaff($row['specimen_collected_by'], false, $pdo);
					$performedBy = (new StaffDirectoryDAO())->getStaff($row['performed_by'], false, $pdo);
					$lResult = (new LabResultDAO())->getLabResult($row['rid'], false, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$labConf = new Lab($row['test_id']);
					$LabGroup = new LabGroup($row['lab_group_id']);
					
					$specimens = array();
					foreach (explode(",", $row['test_specimen_ids']) as $s) {
						if (!empty($s)) {
							$specimens[] = new LabSpecimen($s);
						}
					}
					$sCollected = new StaffDirectory($row['performed_by']);
					$performedBy = new StaffDirectory($row['performed_by']);
					$lResult = $row['rid'] === null ? null : new LabResult($row['rid']);
				}
				$pl->setId($row['id']);
				$pl->setPatient($pat);
				$pl->setTest($labConf);
				$pl->setLabGroup($LabGroup); //a lab group object
				$pl->setPerformedBy($performedBy);
				$pl->setNotes($row['test_notes']);
				$pl->setSpecimens($specimens);
				$pl->setSpecimenCollectedBy($sCollected);
				$pl->setSpecimenNote($row['specimen_notes']);
				$pl->setSpecimenDate($row['specimen_date']);
				$pl->setTestDate($row['test_date']);
				$pl->setLabResult($lResult);
				$pl->setStatus($row['_status']);
				$pl->setReceived((bool)$row['received']);
				$pl->setReceivedBy((new StaffDirectoryDAO())->getStaff($row['specimen_received_by'], false, $pdo));
				
				$labRequests[] = $pl;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
		
		$results = (object)null;
		$results->data = $labRequests;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function findLabRequestsByDateCategory($from = null, $to = null, $category_id = null, $page, $pageSize, $getFUll = false, $pdo = null)
	{
		$f = ($from == null) ? date("Y-m-d") : $from;
		$t = ($to == null) ? date("Y-m-d") : $to;
		$cid = ($category_id == null) ? '' : ' AND ltc.category_id=' . $category_id;
		
		$sql = "SELECT pl.*, lr.id as rid, l.id as lab_request_id, l.time_entered FROM lab_requests l LEFT JOIN patient_labs pl ON l.lab_group_id=pl.lab_group_id LEFT JOIN lab_result lr ON lr.patient_lab_id=pl.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id LEFT JOIN labtests_config ltc ON ltc.id=pl.test_id WHERE pl._status <> 'cancelled' AND DATE(l.time_entered) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid}";
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
		
		$labRequests = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT pl.*, lr.id as rid, l.id as lab_request_id, l.time_entered, l.urgent FROM lab_requests l LEFT JOIN patient_labs pl ON l.lab_group_id=pl.lab_group_id LEFT JOIN lab_result lr ON lr.patient_lab_id=pl.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id LEFT JOIN labtests_config ltc ON ltc.id=pl.test_id WHERE pl._status <> 'cancelled' AND DATE(l.time_entered) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid} ORDER BY DATE(l.time_entered) DESC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pl = new PatientLab();
				if ($getFUll) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo);
					$labConf = (new LabDAO())->getLab($row['test_id'], false, $pdo);
					$LabGroup = (new LabGroupDAO())->getLabGroup($row['lab_group_id'], $row['patient_id'],false, $pdo);
					$specimens = array();
					foreach (explode(",", $row['test_specimen_ids']) as $s) {
						if (!empty($s)) {
							$specimens[] = (new LabSpecimenDAO())->getSpecimen($s, $pdo);
						}
					}
					$sCollected = (new StaffDirectoryDAO())->getStaff($row['specimen_collected_by'], false, $pdo);
					$performedBy = (new StaffDirectoryDAO())->getStaff($row['performed_by'], false, $pdo);
					$lResult = (new LabResultDAO())->getLabResult($row['rid'], false, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$labConf = new Lab($row['test_id']);
					$LabGroup = new LabGroup($row['lab_group_id']);
					
					$specimens = array();
					foreach (explode(",", $row['test_specimen_ids']) as $s) {
						if (!empty($s)) {
							$specimens[] = new LabSpecimen($s);
						}
					}
					$sCollected = new StaffDirectory($row['specimen_collected_by']);
					$performedBy = new StaffDirectory($row['performed_by']);
					$lResult = $row['rid'] === null ? null : new LabResult($row['rid']);
				}
				$pl->setId($row['id']);
				$pl->setPatient($pat);
				$pl->setTest($labConf);
				$pl->setLabGroup($LabGroup); //a lab group object
				$pl->setPerformedBy($performedBy);
				$pl->setNotes(escape($row['test_notes']));
				$pl->setSpecimens($specimens);
				$pl->setSpecimenCollectedBy($sCollected);
				$pl->setSpecimenNote($row['specimen_notes']);
				$pl->setSpecimenDate($row['specimen_date']);
				$pl->setTestDate($row['test_date']);
				$pl->setLabResult($lResult);
				$pl->setStatus($row['_status']);
				$pl->setReceived((bool)$row['received']);
				$pl->setReceivedBy((new StaffDirectoryDAO())->getStaff($row['specimen_received_by'], false, $pdo));
				
				$labRequests[] = $pl;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		} catch (Exception $e) {
			errorLog($e);
			return [];
		}
		
		$results = (object)null;
		$results->data = $labRequests;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function exportLabRequestsByDateCategory($from = null, $to = null, $category_id = null, $page, $pageSize, $getFUll = false, $pdo = null)
	{
		$f = ($from == null) ? date("Y-m-d") : $from;
		$t = ($to == null) ? date("Y-m-d") : $to;
		$cid = ($category_id == null) ? '' : ' AND ltc.category_id=' . $category_id;
		
		$sql = "SELECT pl.*, lr.id as rid, l.id as lab_request_id, l.time_entered, l.urgent FROM lab_requests l LEFT JOIN patient_labs pl ON l.lab_group_id=pl.lab_group_id LEFT JOIN lab_result lr ON lr.patient_lab_id=pl.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id LEFT JOIN labtests_config ltc ON ltc.id=pl.test_id WHERE pl._status <> 'cancelled' AND DATE(l.time_entered) BETWEEN DATE('$f') AND DATE('$'){$cid}";
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
		
		$labRequests = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT pl.*, lr.id as rid, l.id as lab_request_id, l.time_entered AS requestDate, ltc.name AS testName, ltc.billing_code, cc.name AS service_center, CONCAT_WS(' ', p.fname, p.mname, p.lname) AS patientName, CONCAT_WS(' ', sd.firstname, sd.lastname) AS StaffName, sch.scheme_name FROM lab_requests l LEFT JOIN staff_directory sd ON sd.staffId=l.requested_by LEFT JOIN patient_labs pl ON l.lab_group_id=pl.lab_group_id LEFT JOIN insurance ins ON pl.patient_id=ins.patient_id LEFT JOIN service_centre cc ON cc.id=l.service_centre_id LEFT JOIN insurance_schemes sch ON sch.id=ins.insurance_scheme LEFT JOIN lab_result lr ON lr.patient_lab_id=pl.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id LEFT JOIN labtests_config ltc ON ltc.id=pl.test_id WHERE pl._status <> 'cancelled' AND DATE(l.time_entered) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid} ORDER BY DATE(l.time_entered) DESC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$report = (object)null;
				$lResult = (new LabResultDAO())->getLabResult($row['rid'], false, $pdo);
				$referral = (new LabGroupDAO())->getLabGroup($row['lab_group_id'], $row['patient_id'],false, $pdo);
				
				$report->Date = date('Y/m/d h:ia', strtotime($row['requestDate']));
				$report->approved_date = $lResult ? date('Y/m/d h:ia', strtotime($lResult->getApprovedDate())) : '';
				$report->specimen_date = date('Y/m/d h:ia', strtotime($row['specimen_date']));
				$report->referral = $referral->getReferral() ? $referral->getReferral()->getName() : '';
				$report->Lab = $row['testName'];
				$report->Patient = $row['patientName'];
				$report->PatientID = $row['patient_id'];
				$report->Scheme = $row['scheme_name'];
				$report->Amount = (new InsuranceItemsCostDAO())->getItemPriceByCode($row['billing_code'], $row['patient_id'], true, $pdo);
				$report->Staff = $row['StaffName'];
				$report->BusinessUnit = $row['service_center'];
				
				$labRequests[] = $report;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		} catch (Exception $e) {
			errorLog($e);
			return [];
		}
		
		$results = (object)null;
		$results->data = $labRequests;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function findLabRequests2($filter, $patientId = null, $page = 0, $pageSize = 10, $getFUll = false, $pdo = null)
	{
		$filter = escape($filter);
		
		$part1 = !is_blank($filter) ? " l.lab_group_id LIKE '%$filter'" : ' 1';
		$part2 = $patientId !== null ? " l.patient_id = $patientId " : ' 1';
		$sql = "SELECT l.*, lr.id as rid FROM patient_labs l LEFT JOIN lab_result lr ON lr.patient_lab_id=l.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id WHERE $part1 AND $part2";
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
		
		$labRequests = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pl = new PatientLab();
				if ($getFUll) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo);
					$labConf = (new LabDAO())->getLab($row['test_id'], false, $pdo);
					$LabGroup = (new LabGroupDAO())->getLabGroup($row['lab_group_id'], $row['patient_id'],false, $pdo);
					$specimens = array();
					foreach (explode(",", $row['test_specimen_ids']) as $s) {
						if (!empty($s)) {
							$specimens[] = (new LabSpecimenDAO())->getSpecimen($s, $pdo);
						}
					}
					$sCollected = (new StaffDirectoryDAO())->getStaff($row['specimen_collected_by'], false, $pdo);
					$performedBy = (new StaffDirectoryDAO())->getStaff($row['performed_by'], false, $pdo);
					$lResult = (new LabResultDAO())->getLabResult($row['rid'], false, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$labConf = new Lab($row['test_id']);
					$LabGroup = new LabGroup($row['lab_group_id']);
					
					$specimens = array();
					foreach (explode(",", $row['test_specimen_ids']) as $s) {
						if (!empty($s)) {
							$specimens[] = new LabSpecimen($s);
						}
					}
					$sCollected = new StaffDirectory($row['specimen_collected_by']);
					$performedBy = new StaffDirectory($row['performed_by']);
					$lResult = $row['rid'] === null ? null : new LabResult($row['rid']);
				}
				$pl->setId($row['id']);
				$pl->setPatient($pat);
				$pl->setTest($labConf);
				$pl->setLabGroup($LabGroup); //a lab group object
				$pl->setPerformedBy($performedBy);
				$pl->setNotes(escape($row['test_notes']));
				$pl->setSpecimens($specimens);
				$pl->setSpecimenCollectedBy($sCollected);
				$pl->setSpecimenNote($row['specimen_notes']);
				$pl->setSpecimenDate($row['specimen_date']);
				$pl->setTestDate($row['test_date']);
				$pl->setLabResult($lResult);
				$pl->setStatus($row['_status']);
				$pl->setReceived((bool)$row['received']);
				$pl->setReceivedBy((new StaffDirectoryDAO())->getStaff($row['specimen_received_by'], false, $pdo));
				
				$labRequests[] = $pl;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		} catch (Exception $e) {
			errorLog($e);
			return [];
		}
		
		$results = (object)null;
		$results->data = $labRequests;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function getLabbyPatientDate($pid, $when, $pdo = null)
	{
		$pl = new PatientLab();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT l.lab_group_id, l.patient_id FROM patient_labs l LEFT JOIN lab_requests lr ON lr.lab_group_id=l.lab_group_id WHERE l.patient_id='$pid' AND DATE(lr.time_entered) = '$when' AND l._status<>'cancelled'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$labGroup = (new LabGroupDAO())->getLabGroup($row['lab_group_id'],$row['patient_id'], false, $pdo);
				$pl->setLabGroup($labGroup); //a lab group object
			} else {
				$pl = null;
			}
		} catch (PDOException $e) {
			errorLog($e);
		}
		return $pl;
	}
	
	function generateLabNumber($pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT LPAD( COUNT(*)+1 , 5, 0) AS val FROM `lab_requests` WHERE MONTH(`time_entered`) = MONTH(NOW());";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			wait();
			wait();
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return 'LR' . date("y/m/") . $row['val'];
			}
			return 'LR' . date("y/m/") . strtoupper(substr(str_shuffle(md5(microtime())), 0, 5));
		} catch (PDOException $e) {
			error_log("PDO Error occurred");
			return 'LR' . date("y/m/") . strtoupper(substr(str_shuffle(md5(microtime())), 0, 5));
		}
	}
	
	function newPatientLabRequest($lab, $charged = false, $pdo = null)
	{
		//$lab = new LabGroup();
		$encounter = $lab->getEncounter() ? $lab->getEncounter()->getId() : "NULL";
		$encounterObject = $lab->getEncounter() ? $lab->getEncounter() : null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$lab->setGroupName($this->generateLabNumber($pdo));
			if ($lab->getPatient() === null) {
				return 'error:No patient';
			}
			$canCommit = !$pdo->inTransaction();
			try {
				$pdo->beginTransaction();
			} catch (PDOException $ef) {
				//
			}
			
			$pref_specs = array();
			foreach ($lab->getPreferredSpecimens() as $sel_specs) {
				$pref_specs[] = $sel_specs->getId();
			}
			$pref_specs = implode(",", $pref_specs);
			$referral_id = ($lab->getReferral() !== null) ? $lab->getReferral()->getId() : "NULL";
			$ip_id = ($lab->getInPatient() !== null) ? $lab->getInPatient()->getId() : "NULL";
			$requestNote = !is_blank($lab->getRequestNote()) ? quote_esc_str($lab->getRequestNote()) : "NULL";
			$urgent = $lab->getUrgent() ? var_export($lab->getUrgent(), true) : 'FALSE';
			$serviceCenter = $lab->getServiceCentre() ? $lab->getServiceCentre()->getId() : "NULL";
			$sql = "INSERT INTO lab_requests (patient_id, requested_by, request_note, lab_group_id, preferred_specimens, referral_id, service_centre_id, in_patient_id, encounter_id, `urgent`) VALUES (" . $lab->getPatient()->getId() . ", " . $lab->getRequestedBy()->getId() . ", $requestNote, '" . $lab->getGroupName() . "', '" . $pref_specs . "', $referral_id, " . $serviceCenter . ", $ip_id, $encounter, $urgent)";
			// error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			//sleep(0.5);
			
			$lab_data = $lab->getRequestData();
			$lab->setRequestTime(date(MainConfig::$mysqlDateTimeFormat));
			
			//$summarynote = "Lab" . ((count($lab_data) > 1) ? "s" : "") . " for ";
			$requestItems = [];
			
			$sql2 = "INSERT INTO patient_labs (patient_id, test_id,lab_group_id, bill_line_id) VALUES ";
			
			$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false, $pdo);
			//$get_active_enrollment = (new AntenatalEnrollmentDAO())->getActiveInstance($lab->getPatient()->getId(), FALSE, $pdo);
			foreach ($lab_data as $i => $data) {
				$amount = (new InsuranceItemsCostDAO())->getItemPriceByCode($data->getCode(), $lab->getPatient()->getId(), true, $pdo);
				$billLineId = "NULL";
				if (!$charged) {
					$bil = new Bill();
					$bil->setPatient($lab->getPatient());
					$bil->setDescription("Lab charges: " . $data->getName());
					$bil->setItem($data);
					$bil->setSource((new BillSourceDAO())->findSourceById(1, $pdo));
					$bil->setTransactionType("credit");
					$bil->setAmount($amount);
					$bil->setInPatient($lab->getInPatient());
					$bil->setDiscounted(null);
					$bil->setDiscountedBy(null);
					$bil->setClinic($staff->getClinic());
					$bil->setBilledTo($lab->getPatient()->getScheme());
					//
					$bil->setTransactionDate($lab->getRequestTime());
					$bil->setReferral($lab->getReferral());
					$bil->setCostCentre((is_null($lab->getServiceCentre())) ? null : $lab->getServiceCentre()->getCostCentre());
					
					$ipId = $lab->getInPatient() ? $lab->getInPatient()->getId() : null;
					$bill = (new BillDAO())->addBill($bil, 1, $pdo, $ipId);
					
					$billLineId = $bill != null && $bill->getId() ? (is_array($bill->getId()) ? "'".implode(",", $bill->getId())."'" : $bill->getId()) : "NULL";
				}
				
				$sql2 .= "('" . $lab->getPatient()->getId() . "', '" . $data->getId() . "','" . $lab->getGroupName() . "', $billLineId)";
				$requestItems[] = $data->getName();
				if ($i != count($lab_data) - 1) {
					$sql2 .= ", ";
				}
			}
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VisitNotes.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
			foreach ($requestItems as $requestItem) {
				$note = new VisitNotes();
				$note->setNotedBy($staff);
				$note->setDateOfEntry(time());
				$note->setDescription("Lab: $requestItem");
				//$note->setDescription($summarynote . implode(", ", $requestItems). ' requested');
				$note->setHospital(new Clinic(1));
				$note->setNoteType('inv');
				$note->setPatient($lab->getPatient());
				$note->setEncounter($encounterObject);
				
				$n = (new VisitNotesDAO())->addNote($note, $pdo);
			}
			
			$stmt2 = $pdo->prepare($sql2, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			//error_log($sql2);
			$stmt2->execute();
			$lab->setId($pdo->lastInsertId());
			
			//sleep(0.05);
			if ($stmt2->rowCount() !== count($lab_data)) {
				$pdo->rollBack();
				return null;
			}
			/* create the Lab Queue for this patient*/
			require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/PatientQueue.php');
			require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php');
			require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php');
			
			$pat = new PatientDemograph();
			$pat->setId($lab->getPatient()->getId());
			$pq = new PatientQueue();
			$pq->setType("Lab");
			$pq->setPatient($pat);
			(new PatientQueueDAO())->addPatientQueue($pq, $pdo);
			/*added to Lab queue*/
			if ($stmt->rowCount() == 1) {
				if ($canCommit) {
					$pdo->commit();
				}
				return $lab;
			}
			$pdo->rollBack();
			return null;
			
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function takeSpecimen($plObj, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$specimens = $plObj->getSpecimens();
			$specimen_ids = array();
			$specimenNote = !is_blank($plObj->getSpecimenNote()) ? quote_esc_str($plObj->getSpecimenNote()) : "NULL";
			foreach ($specimens as $s) {
				$specimen_ids[] = $s->getId();
			}
			
			$sql = "UPDATE patient_labs SET test_specimen_ids = '" . implode(",", $specimen_ids) . "', specimen_collected_by='" . $plObj->getSpecimenCollectedBy()->getId() . "', specimen_notes=$specimenNote, specimen_date = '" . $plObj->getSpecimenDate() . "' WHERE id = " . $plObj->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return $plObj;
			} else {
				return null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function saveResult($plObj, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$canCommit = true;
			try {
				$pdo->beginTransaction();
			} catch (PDOException $e) {
				$canCommit = false;
			}
			$sql = "UPDATE patient_labs SET test_notes='" . escape($plObj->getNotes()) . "', test_date='" . $plObj->getTestDate() . "', performed_by = '" . $plObj->getPerformedBy()->getId() . "' WHERE id = " . $plObj->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ((new LabResultDAO())->addLabResult($plObj->getLabResult(), $pdo) === null) {
				error_log("ERROR: Failed to add lab result");
				$plObj = null;
				$pdo->rollBack();
			} else {
				
				if ($stmt->rowCount() != 1) {
					$plObj = null;
					$pdo->rollBack();
				} else {
					if ($canCommit) {
						$pdo->commit();
					} else {
						$pdo->rollBack();
					}
				}
			}
		} catch (PDOException $e) {
			error_log($e->getFile() . ":" . $e->getLine() . " (" . $e->getMessage() . ")");
		}
		return $plObj;
	}
	
	function cancelLab($pl, $pdo = null)
	{
		//$pl = new PatientLab();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			
			$pdo->beginTransaction();
			$sql = "UPDATE patient_labs SET _status = 'cancelled' WHERE id = " . $pl->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				// update the status of this lab as cancelled,
				// then add a reversal bill
				// $price of this lab =
				// get the line charged for the item and undo the related bill lines
				//// new method:
				$billTransf = (new BillDAO())->checkBill($pl->getBill()[0]->getId(), true, $pdo);
				//// get bills if transferred for cancellation
				$checkBill = (new BillDAO())->getTransferCreditOnly($pl->getBill()[0]->getId(), true, $pdo);
				if ($billTransf && $checkBill == null){
					$pdo->rollBack();
					return false;
				}
				
				if ($pl->getBill() !== [null]) {
					foreach ($pl->getBill() as $b){
						// it means we charged for this lab
						$item = (new BillDAO())->cancelRelatedItems($b->getPatient()->getId(), $b->getItemCode(), $b->getTransactionDate(), $pdo);
						
						if ($item == null) {
							$pdo->rollBack();
							return array('status' => 'error', 'message' => 'Failed to cancel lab related bill lines');
						}
						
						
						// continue to do the reversal
						$price = $b->getAmount();
						// (new InsuranceItemsCostDAO())->getItemPriceByCode($pl->getTest()->getCode(), $pl->getLabGroup()->getPatient()->getId(), true, $pdo);
						
						$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
						$patient = (new PatientDemographDAO())->getPatient($pl->getLabGroup()->getPatient()->getId(), false, $pdo, null);
						
						$bil = new Bill();
						$bil->setPatient($patient);
						$bil->setDescription("" . $pl->getTest()->getName());
						$bil->setItem($pl->getTest());
						$bil->setSource((new BillSourceDAO())->findSourceById(1, $pdo));
						$bil->setTransactionType("reversal");
						$bil->setTransactionDate(date("Y-m-d H:i:s"));
						$bil->setCancelledBy((new StaffDirectory($_SESSION['staffID'])));
						$bil->setCancelledOn(date("Y-m-d H:i:s"));
						$bil->setDueDate($pl->getLabGroup()->getRequestTime());
						$bil->setAmount(0 - $price);
						$bil->setDiscounted('NO');
						$bil->setDiscountedBy(null);
						$bil->setClinic($staff->getClinic());
						$bil->setBilledTo($b->getBilledTo());
						$bil->setActiveBill('not_active');
						$costCentre = (is_null($pl->getServiceCentre())) ? null : (new ServiceCenterDAO())->get($pl->getServiceCentre()->getId(), $pdo)->getCostCentre();
						$bil->setCostCentre($costCentre);
						$parent = (is_null($checkBill)) ? $bil->setParent($b->getBill()) : $bil->setParent($checkBill);
						$bil->setParent($parent->getParent());
						
						$bill = (new BillDAO())->addBill($bil, 1, $pdo, null);
					}
					$pdo->commit();
					return true;
				} else if ($pl->getBill() === null) {
					//we didn't charge: we either used a token or antenatal package
					
					//consider the antenatal scenario first
					require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/AntenatalEnrollmentDAO.php';
					$activeAntenatalInstance = (new AntenatalEnrollmentDAO())->getActiveInstance($pl->getLabGroup()->getPatient()->getId(), FALSE, $pdo);
					if($activeAntenatalInstance !== null){
						//error_log("HERE is an antenatal patient");
						//if the patient has is enrolled into antenatal and the package has the items covered
						//yay!!! we know the item
						require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AntenatalPackageItem.php';
						require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
						require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
						require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackagesDAO.php';
						$thisItemCode = $pl->getTest()->getCode();
						$itemsCodes = [];
						
						$patientTokens = (new AntenatalPackagesDAO())->get($activeAntenatalInstance->getPackage()->getId(), $pdo)->getItems();
						foreach($patientTokens as $token){
							//$token = new AntenatalPackageItem();
							$itemsCodes[$token->getItemCode()] = $token->getUsage();
						}
						
						//if(in_array($thisItemCode, $itemsCodes)){
						if(isset($itemsCodes[$thisItemCode])){
							$billQuantity = (int)1;
							$item_type = getAntenatalItemType($thisItemCode);
							// it's a reversal
							(new PatientAntenatalUsages())->setPatient($pl->getLabGroup()->getPatient())->setItemCode($thisItemCode)->setItem($pl->getTest()->getId())->setType($item_type)->setAntenatal($activeAntenatalInstance)->setUsages(parseNumber(0 - $billQuantity))->setDateUsed(date(MainConfig::$mysqlDateTimeFormat))->add($pdo);
							$pdo->commit();
							return true;
						}
						$pdo->rollBack();
						return false;
					}
					
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PackageTokenUsage.php';
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PackageTokenDAO.php';
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PackageTokenUsageDAO.php';
					$thisItemCode = $pl->getTest()->getCode();
					$itemsCodes = [];
					//$itemTokens = [];
					$patientTokens = (new PackageTokenDAO())->forPatient($pl->getLabGroup()->getPatient()->getId(), $pdo);
					foreach ($patientTokens as $token) {
						//$token = new PackageToken();
						//$itemTokens[] = array('code'=>$token->getItemCode(),'quantity_left'=>$token->getRemainingQuantity());
						$itemsCodes[] = $token->getItemCode();
					}
					if (in_array($thisItemCode, $itemsCodes)) {
						//we used a token:package
						$itemQuantity = (new PackageTokenDAO())->forPatientItem($thisItemCode, $pl->getLabGroup()->getPatient()->getId(), $pdo);
						$availableTokenItemQty = $itemQuantity->getRemainingQuantity();
						$billQuantity = 1; // todo: are we using dynamic quantity in the requests yet?
						
						$itemQuantity->setRemainingQuantity($availableTokenItemQty+$billQuantity)->setPatient($pl->getLabGroup()->getPatient())->update($pdo);
						(new PackageTokenUsage())->setItemCode($thisItemCode)->setPatient($pl->getLabGroup()->getPatient())->setQuantity(0-$billQuantity)->add($pdo);
						
						//we have reduced token, so we need not charge this patient, exit from this function
						$pdo->commit();
						return true;
						
						//$pdo->rollBack();
						//error_log('cant cancel lab');
						//return array('status' => 'error', 'message' => 'Failed to cancel lab bill line');
					} else if (!in_array($thisItemCode, $itemsCodes)){
						$pdo->commit();
						return true;
					}
				}
				$pdo->rollBack();
				return false;
			} else {
				//lab is already cancelled
				$pdo->rollBack();
				return false;
			}
		} catch (PDOException $e) {
			error_log("Exception Lab cancellation");
			return false;
		}
	}
	
	public function updateResult($pl, $pdo = null)
	{
		//        $pl = new PatientLab();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$sql = "UPDATE lab_result_data SET `value` = ? WHERE lab_result_id = ? AND lab_template_data_id = ?";
			$stmt = $pdo->prepare($sql);
			
			$exec = [];
			foreach ($pl->getLabResult()->getData() as $data) {
				$stmt->bindParam(1, escape($data->getValue()), PDO::PARAM_STR);
				$stmt->bindParam(2, $data->getLabResult()->getId(), PDO::PARAM_INT);
				$stmt->bindParam(3, $data->getLabTemplateData()->getId(), PDO::PARAM_INT);
				$exec[] = $stmt->execute();
			}
			$sq = "UPDATE patient_labs SET test_notes = '" . $pl->getNotes() . "' WHERE id = " . $pl->getId();
			$st2 = $pdo->prepare($sq);
			$exec[] = $st2->execute();
			
			if (in_array(false, $exec)) {
				error_log("ERROR: One of the statements did not execute in transaction");
				$pdo->rollBack();
				return null;
			}
			
			$pdo->commit();
			return $pl;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function receiveLab($pl, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			
			$pdo->beginTransaction();
			$sql = "UPDATE patient_labs SET received = TRUE, specimen_received_by=" . $pl->getReceivedBy()->getId() . " WHERE id = " . $pl->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$pdo->commit();
			return true;
		} catch (PDOException $e) {
			error_log("Exception receive lab");
			return false;
		}
	}
	
	public function getEncounterLabs($id, $pdo)
	{
		$labRequests = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT pl.id FROM patient_labs pl LEFT JOIN lab_requests lr ON pl.lab_group_id=lr.lab_group_id WHERE lr.encounter_id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$labRequests[] = $this->getLab($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
		}
		return $labRequests;
	}
	
	function getUnfulfilledLabs($page, $pageSize, $getFUll = false, $start = null, $stop = null, $pdo = null)
	{
		$f = ($start == null) ? date("Y-m-d") : $start;
		$t = ($stop == null) ? date("Y-m-d") : $stop;
		$sql = "SELECT pl.* FROM patient_labs pl LEFT JOIN lab_result lr ON lr.patient_lab_id=pl.id LEFT JOIN lab_requests ls ON pl.lab_group_id=ls.lab_group_id LEFT JOIN labtests_config ltc ON ltc.id=pl.test_id WHERE lr.id IS NULL AND pl._status = 'open' AND pl.specimen_collected_by IS NULL AND DATE(ls.time_entered) BETWEEN DATE('$f') AND DATE('$t')";
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
		
		$labs = array();
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			
			$sql .= " LIMIT $offset, $pageSize";
			//error_log($sql);
			// $sql = "SELECT pl.* FROM patient_labs pl LEFT JOIN lab_result lr ON lr.patient_lab_id=pl.id WHERE lr.id IS NULL AND pl._status = 'open' LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pl = new PatientLab();
				if ($getFUll) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo);
					$labConf = (new LabDAO())->getLab($row['test_id'], false, $pdo);
					$LabGroup = (new LabGroupDAO())->getLabGroup($row['lab_group_id'], $row['patient_id'],false, $pdo);
					$specimens = array();
					foreach (explode(",", $row['test_specimen_ids']) as $s) {
						if (!empty($s)) {
							$specimens[] = (new LabSpecimenDAO())->getSpecimen($s, $pdo);
						}
					}
					$sCollected = (new StaffDirectoryDAO())->getStaff($row['specimen_collected_by'], false, $pdo);
					$performedBy = (new StaffDirectoryDAO())->getStaff($row['performed_by'], false, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$labConf = new Lab($row['test_id']);
					$LabGroup = new LabGroup($row['lab_group_id']);
					
					$specimens = array();
					foreach (explode(",", $row['test_specimen_ids']) as $s) {
						if (!empty($s)) {
							$specimens[] = new LabSpecimen($s);
						}
					}
					$sCollected = new StaffDirectory($row['performed_by']);
					$performedBy = new StaffDirectory($row['performed_by']);
				}
				$pl->setId($row['id']);
				$pl->setPatient($pat);
				$pl->setTest($labConf);
				$pl->setLabGroup($LabGroup); //a lab group object
				$pl->setPerformedBy($performedBy);
				$pl->setNotes($row['test_notes']);
				$pl->setSpecimens($specimens);
				$pl->setSpecimenCollectedBy($sCollected);
				$pl->setSpecimenNote($row['specimen_notes']);
				$pl->setSpecimenDate($row['specimen_date']);
				$pl->setTestDate($row['test_date']);
				$pl->setStatus($row['_status']);
				$pl->setReceived((bool)$row['received']);
				$pl->setReceivedBy((new StaffDirectoryDAO())->getStaff($row['specimen_received_by'], false, $pdo));
				
				$labs[] = $pl;
			}
		} catch (PDOException $e) {
			errorLog($e);
			$labs = [];
			$stmt = null;
		}
		
		$results = (object)null;
		$results->data = $labs;
		$results->total = $total;
		$results->page = $page;
		unset($_SESSION['pid']);
		return $results;
	}
}
