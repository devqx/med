<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/17/14
 * Time: 1:10 PM
 */
class PatientOphthalmologyDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			@session_start();
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientOphthalmology.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/OphthalmologyResult.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Ophthalmology.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/OphthalmologyGroup.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyGroupDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyResultDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/protect.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $pdo = null)
	{
		$pl = new PatientOphthalmology();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//$sql = "SELECT * FROM patient_labs WHERE id = '$id'";
			$sql = "SELECT l.*, lr.id as rid FROM patient_ophthalmology l LEFT JOIN ophthalmology_result lr ON lr.patient_ophthalmology_id=l.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id WHERE l.id = '$id'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pl->setId($row['id']);
				$ophGroup = (new OphthalmologyGroupDAO())->getGroup($row['ophthalmology_group_code'], TRUE, $pdo);
				$pl->setOphthalmologyGroup($ophGroup); //a lab group object
				$pl->setOphthalmology((new OphthalmologyDAO())->get($row['ophthalmology_id'], $pdo));
				$pl->setPerformedBy((new StaffDirectoryDAO())->getStaff($row['performed_by'], FALSE, $pdo));
				$pl->setNotes($row['test_notes']);
				$pl->setStatus($row['_status']);
				$pl->setTestDate($row['test_date']);
				$pl->setBill((new BillDAO())->getBill($row['bill_line_id'], false, $pdo));
				$pl->setPatient((new PatientDemograph($row['patient_id'], $pdo)));

				$pl->setOphthalmologyResult((new OphthalmologyResultDAO())->get($row['rid'], FALSE, $pdo));
				//$pl->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
			} else {
				$pl = null;
			}
		} catch (PDOException $e) {
			error_log("PDO Error occurred");
		}
		return $pl;
	}

	function getRequestsToApprove($pdo = null)
	{
		$ophRequests = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT pl.* FROM patient_ophthalmology pl LEFT JOIN ophthalmology_result lr ON lr.patient_ophthalmology_id=pl.id WHERE lr.approved IS FALSE";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ophRequests[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
		}
		return $ophRequests;
	}


	public function getPatientOphthalmologyByGroupCode($groupId, $getFull = FALSE, $pdo = null)
	{
		$Requests = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT pl.*, lr.id AS rid FROM patient_ophthalmology pl LEFT JOIN ophthalmology_result lr ON lr.patient_ophthalmology_id=pl.id WHERE ophthalmology_group_code= '" . $groupId . "' #AND  lr.approved IS TRUE";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pl = new PatientOphthalmology();
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$labConf = (new OphthalmologyDAO())->get($row['ophthalmology_id'], FALSE, $pdo);
					$LabGroup = (new OphthalmologyGroupDAO())->getGroup($row['ophthalmology_group_code'], FALSE, $pdo);
					$performedBy = (new StaffDirectoryDAO())->getStaff($row['performed_by'], FALSE, $pdo);
					$lResult = (new OphthalmologyResultDAO())->get($row['rid'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$labConf = new Ophthalmology($row['ophthalmology_id']);
					$LabGroup = new OphthalmologyGroup($row['ophthalmology_group_code']);
					$performedBy = new StaffDirectory($row['performed_by']);
					$lResult = $row['rid'] === null ? null : new OphthalmologyResult($row['rid']);
				}
				$pl->setId($row['id']);
				$pl->setPatient($pat);
				$pl->setOphthalmology($labConf);
				$pl->setOphthalmologyGroup($LabGroup); //a lab group object
				$pl->setPerformedBy($performedBy);
				$pl->setNotes($row['test_notes']);
				$pl->setTestDate($row['test_date']);
				$pl->setOphthalmologyResult($lResult);
				$pl->setStatus($row['_status']);

				$Requests[] = $pl;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
		return $Requests;
	}


	function getRequestsWithoutResult($page, $pageSize, $sort = 'asc', $_centre = null, $_category = null, $getFUll = FALSE, $pdo = null)
	{
		$filter = ($_centre != null ? " AND service_centre_id=$_centre" : "");
		$cat_filter = ($_category != null ? " AND ltc.category_id=$_category" : "");
		$sql = "SELECT pl.* FROM patient_ophthalmology pl LEFT JOIN ophthalmology_result lr ON lr.patient_ophthalmology_id=pl.id LEFT JOIN ophthalmology_requests ls ON pl.ophthalmology_group_code=ls.group_code LEFT JOIN ophthalmology ltc ON ltc.id=pl.ophthalmology_id WHERE lr.id IS NULL AND pl._status = 'open' $filter$cat_filter";
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

		$data = array();

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;

			$sql = $sql . " /*ORDER BY */ LIMIT $offset, $pageSize";
			//$sql = "SELECT pl.* FROM patient_labs pl LEFT JOIN lab_result lr ON lr.patient_lab_id=pl.id WHERE lr.id IS NULL AND pl._status = 'open' LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pl = new PatientOphthalmology();
				if ($getFUll) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$ophthalmologyConf = (new OphthalmologyDAO())->get($row['ophthalmology_id'], FALSE, $pdo);
					$OphthalmologyGroup = (new OphthalmologyGroupDAO())->getGroup($row['ophthalmology_group_code'], FALSE, $pdo);

					$performedBy = (new StaffDirectoryDAO())->getStaff($row['performed_by'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$ophthalmologyConf = new Ophthalmology($row['ophthalmology_id']);
					$OphthalmologyGroup = new OphthalmologyGroup($row['ophthalmology_group_code']);
					$performedBy = new StaffDirectory($row['performed_by']);
				}
				$pl->setId($row['id']);
				$pl->setPatient($pat);
				$pl->setOphthalmology($ophthalmologyConf);
				$pl->setOphthalmologyGroup($OphthalmologyGroup); //a lab group object
				$pl->setPerformedBy($performedBy);
				$pl->setNotes($row['test_notes']);
				$pl->setTestDate($row['test_date']);
				$pl->setStatus($row['_status']);

				$data[] = $pl;
			}
		} catch (PDOException $e) {
			errorLog($e);
			$data = [];
			$stmt = null;
		}

		$results = (object)null;
		$results->data = $data;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function findRequestsByDate($start = null, $stop = null, $page = 0, $pageSize = 10, $getFUll = FALSE, $pdo = null)
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

		$sql = "SELECT pl.*, lr.id as rid, l.id as ophth_request_id, l.time_entered FROM ophthalmology_requests l LEFT JOIN patient_ophthalmology pl ON l.group_code=pl.ophthalmology_group_code LEFT JOIN ophthalmology_result lr ON lr.patient_ophthalmology_id=pl.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id WHERE DATE(l.time_entered) BETWEEN '$dateStart' AND '$dateStop'";
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
			$sql = "SELECT pl.*, lr.id as rid, l.id as ophth_request_id, l.time_entered FROM ophthalmology_requests l LEFT JOIN patient_ophthalmology pl ON l.group_code=pl.ophthalmology_group_code LEFT JOIN ophthalmology_result lr ON lr.patient_ophthalmology_id=pl.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id WHERE DATE(l.time_entered) BETWEEN '$dateStart' AND '$dateStop' ORDER BY l.time_entered ASC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pl = new PatientOphthalmology();
				if ($getFUll) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, TRUE);
					$labConf = (new OphthalmologyDAO())->get($row['ophthalmology_id'], FALSE, $pdo);
					$LabGroup = (new OphthalmologyGroupDAO())->getGroup($row['ophthalmology_group_code'], FALSE, $pdo);
					$performedBy = (new StaffDirectoryDAO())->getStaff($row['performed_by'], FALSE, $pdo);
					$lResult = (new OphthalmologyResultDAO())->get($row['rid'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$labConf = new Ophthalmology($row['ophthalmology_id']);
					$LabGroup = new OphthalmologyGroup($row['ophthalmology_group_code']);
					$performedBy = new StaffDirectory($row['performed_by']);
					$lResult = $row['rid'] === null ? null : new OphthalmologyResult($row['rid']);
				}
				$pl->setId($row['id']);
				$pl->setPatient($pat);
				$pl->setOphthalmology($labConf);
				$pl->setOphthalmologyGroup($LabGroup); //a lab group object
				$pl->setPerformedBy($performedBy);
				$pl->setNotes($row['test_notes']);
				$pl->setTestDate($row['test_date']);
				$pl->setOphthalmologyResult($lResult);
				$pl->setStatus($row['_status']);

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

	function findRequestsByDateCategory($from = null, $to = null, $category_id = null, $page, $pageSize, $getFUll = FALSE, $pdo = null)
	{
		$f = ($from == null) ? date("Y-m-d") : $from;
		$t = ($to == null) ? date("Y-m-d") : $to;
		$cid = ($category_id == null) ? '' : ' AND ltc.category_id=' . $category_id;

		$sql = "SELECT pl.*, lr.id as rid, l.id as ophthalmology_request_id, l.time_entered FROM ophthalmology_requests l LEFT JOIN patient_ophthalmology pl ON l.group_code=pl.ophthalmology_group_code LEFT JOIN ophthalmology_result lr ON lr.patient_ophthalmology_id=pl.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id LEFT JOIN ophthalmology ltc ON ltc.id=pl.ophthalmology_id WHERE pl._status <> 'cancelled' AND DATE(l.time_entered) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid}";
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
			$sql = "SELECT pl.*, lr.id as rid, l.id as ophthalmology_request_id, l.time_entered FROM ophthalmology_requests l LEFT JOIN patient_ophthalmology pl ON l.group_code=pl.ophthalmology_group_code LEFT JOIN ophthalmology_result lr ON lr.patient_ophthalmology_id=pl.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id LEFT JOIN ophthalmology ltc ON ltc.id=pl.ophthalmology_id WHERE pl._status <> 'cancelled' AND DATE(l.time_entered) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid} ORDER BY DATE(l.time_entered) DESC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pl = new PatientOphthalmology();
				if ($getFUll) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$labConf = (new LabDAO())->getLab($row['ophthalmology_id'], FALSE, $pdo);
					$LabGroup = (new LabGroupDAO())->getLabGroup($row['ophthalmology_group_code'], $row['patient_id'], FALSE, $pdo);
					$performedBy = (new StaffDirectoryDAO())->getStaff($row['performed_by'], FALSE, $pdo);
					$lResult = (new LabResultDAO())->getLabResult($row['rid'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$labConf = new Lab($row['ophthalmology_id']);
					$LabGroup = new LabGroup($row['ophthalmology_group_code']);
					$performedBy = new StaffDirectory($row['performed_by']);
					$lResult = $row['rid'] === null ? null : new LabResult($row['rid']);
				}
				$pl->setId($row['id']);
				$pl->setPatient($pat);
				$pl->setOphthalmology($labConf);
				$pl->setOphthalmologyGroup($LabGroup); //a lab group object
				$pl->setPerformedBy($performedBy);
				$pl->setNotes(escape($row['test_notes']));
				$pl->setTestDate($row['test_date']);
				$pl->setOphthalmologyResult($lResult);
				$pl->setStatus($row['_status']);

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

	function exportRequestsByDateCategory($from = null, $to = null, $category_id = null, $page, $pageSize, $getFUll = FALSE, $pdo = null)
	{
		$f = ($from == null) ? date("Y-m-d") : $from;
		$t = ($to == null) ? date("Y-m-d") : $to;
		$cid = ($category_id == null) ? '' : ' AND ltc.category_id=' . $category_id;

		$sql = "SELECT pl.*, lr.id as rid, l.id as ophthalmology_request_id, l.time_entered FROM ophthalmology_requests l LEFT JOIN patient_ophthalmology pl ON l.group_code=pl.ophthalmology_group_code LEFT JOIN ophthalmology_result lr ON lr.patient_ophthalmology_id=pl.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id LEFT JOIN ophthalmology ltc ON ltc.id=pl.ophthalmology_id WHERE pl._status <> 'cancelled' AND DATE(l.time_entered) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid}";
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
			$sql = "SELECT pl.*, lr.id as rid, l.id as ophthalmology_request_id, l.time_entered FROM ophthalmology_requests l LEFT JOIN patient_ophthalmology pl ON l.group_code=pl.ophthalmology_group_code LEFT JOIN ophthalmology_result lr ON lr.patient_ophthalmology_id=pl.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id LEFT JOIN ophthalmology ltc ON ltc.id=pl.ophthalmology_id WHERE pl._status <> 'cancelled' AND DATE(l.time_entered) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$cid} ORDER BY DATE(l.time_entered) DESC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$report = (object)null;

				$report->Date = date('jS M, Y', strtotime((new OphthalmologyGroupDAO())->getGroup($row['ophthalmology_group_code'], FALSE, $pdo)->getRequestTime()));
				$report->Lab = (new OphthalmologyDAO())->get($row['ophthalmology_id'], FALSE, $pdo)->getName();
				$report->Staff = (new OphthalmologyGroupDAO())->getGroup($row['ophthalmology_group_code'], FALSE, $pdo)->getRequestedBy()->getFullname();
				$report->Patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo)->getFullname();
				$report->Scheme = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo)->getScheme()->getName();
				//fixme, get the price as it relates to the patient
				$report->Amount = (new OphthalmologyDAO())->get($row['ophthalmology_id'], FALSE, $pdo)->getBasePrice();

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

	/* done */
	function findRequests($filter, $page = 0, $pageSize = 10, $getFUll = FALSE, $pdo = null)
	{
		$sql = "SELECT lr.id as rid FROM patient_ophthalmology l LEFT JOIN ophthalmology_result lr ON lr.patient_ophthalmology_id=l.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id WHERE l.patient_id LIKE '%$filter%' OR p.fname LIKE '%$filter%' OR p.lname LIKE '%$filter%' OR l.ophthalmology_group_code LIKE '%$filter%'";
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

		$Requests = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT l.*, lr.id as rid FROM patient_ophthalmology l LEFT JOIN ophthalmology_result lr ON lr.patient_ophthalmology_id=l.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id WHERE l.patient_id LIKE '%$filter%' OR p.fname LIKE '%$filter%' OR p.lname LIKE '%$filter%' OR l.ophthalmology_group_code LIKE '%$filter%' LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pl = new PatientOphthalmology();
				if ($getFUll) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$ophthConf = (new OphthalmologyDAO())->get($row['ophthalmology_id'], FALSE, $pdo);
					$OphthGroup = (new OphthalmologyGroupDAO())->getGroup($row['ophthalmology_group_code'], FALSE, $pdo);
					$performedBy = (new StaffDirectoryDAO())->getStaff($row['performed_by'], FALSE, $pdo);
					$lResult = (new OphthalmologyResultDAO())->get($row['rid'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$ophthConf = new Ophthalmology($row['ophthalmology_id']);
					$OphthGroup = new OphthalmologyGroup($row['ophthalmology_group_code']);
					$performedBy = new StaffDirectory($row['performed_by']);
					$lResult = $row['rid'] === null ? null : new OphthalmologyResult($row['rid']);
				}
				$pl->setId($row['id']);
				$pl->setPatient($pat);
				$pl->setOphthalmology($ophthConf);
				$pl->setOphthalmologyGroup($OphthGroup); //a lab group object
				$pl->setPerformedBy($performedBy);
				$pl->setNotes(escape($row['test_notes']));
				$pl->setTestDate($row['test_date']);
				$pl->setOphthalmologyResult($lResult);
				$pl->setStatus($row['_status']);

				$Requests[] = $pl;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		} catch (Exception $e) {
			errorLog($e);
			return [];
		}

		$results = (object)null;
		$results->data = $Requests;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function findOphthalmologyRequests($pid, $page = 0, $pageSize = 10, $getFUll = FALSE, $pdo = null)
	{
		$sql = "SELECT lr.id as rid FROM patient_ophthalmology l LEFT JOIN ophthalmology_result lr ON lr.patient_ophthalmology_id=l.id WHERE l.patient_id = $pid";
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

		$Requests = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT l.*, lr.id as rid FROM patient_ophthalmology l LEFT JOIN ophthalmology_result lr ON lr.patient_ophthalmology_id=l.id WHERE l.patient_id = $pid LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pl = new PatientOphthalmology();
				if ($getFUll) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$ophthConf = (new OphthalmologyDAO())->get($row['ophthalmology_id'], FALSE, $pdo);
					$OphthGroup = (new OphthalmologyGroupDAO())->getGroup($row['ophthalmology_group_code'], FALSE, $pdo);
					$performedBy = (new StaffDirectoryDAO())->getStaff($row['performed_by'], FALSE, $pdo);
					$lResult = (new OphthalmologyResultDAO())->get($row['rid'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$ophthConf = new Ophthalmology($row['ophthalmology_id']);
					$OphthGroup = new OphthalmologyGroup($row['ophthalmology_group_code']);
					$performedBy = new StaffDirectory($row['performed_by']);
					$lResult = $row['rid'] === null ? null : new OphthalmologyResult($row['rid']);
				}
				$pl->setId($row['id']);
				$pl->setPatient($pat);
				$pl->setOphthalmology($ophthConf);
				$pl->setOphthalmologyGroup($OphthGroup); //a lab group object
				$pl->setPerformedBy($performedBy);
				$pl->setNotes(escape($row['test_notes']));
				$pl->setTestDate($row['test_date']);
				$pl->setOphthalmologyResult($lResult);
				$pl->setStatus($row['_status']);

				$Requests[] = $pl;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		} catch (Exception $e) {
			errorLog($e);
			return [];
		}

		$results = (object)null;
		$results->data = $Requests;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function generateRequestSequence()
	{
		require_once $_SERVER ['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT LPAD(AUTO_INCREMENT, 5, 0) AS val FROM information_schema.tables WHERE table_schema = '" . $pdo->getDBName() . "' AND table_name = 'ophthalmology_requests'";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();

		$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		return 'OP' . $row_data['val'];
	}

	function newRequest($ophth, $pdo = null)
	{
		//$ophth = new OphthalmologyGroup();
		$ophth->setGroupName($this->generateRequestSequence());
		if ($ophth->getPatient() === null) {
			return 'error:No patient';
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;

			$pdo->beginTransaction();

			$referral_id = ($ophth->getReferral() !== null) ? $ophth->getReferral()->getId() : "NULL";

			$sql = "INSERT INTO ophthalmology_requests (patient_id, requested_by,group_code, referral_id, service_centre_id) VALUES (" . $ophth->getPatient()->getId() . ", " . $ophth->getRequestedBy()->getId() . ", '" . $ophth->getGroupName() . "', $referral_id, " . (is_null($ophth->getServiceCentre()) ? "NULL" : $ophth->getServiceCentre()->getId()) . ")";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$lab_data = $ophth->getRequestData();
			$summarynote = "Ophthalmology Request" . ((count($lab_data) > 1) ? "s" : "") . " for ";
			$sql2 = "INSERT INTO patient_ophthalmology (patient_id, ophthalmology_id, ophthalmology_group_code,bill_line_id) VALUES ";

			$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], FALSE, $pdo);
			foreach ($lab_data as $i => $data) {
				$amount = (new InsuranceItemsCostDAO())->getItemPriceByCode($data->getCode(), $ophth->getPatient()->getId(), TRUE, $pdo);
				$bil = new Bill();
				$bil->setPatient($ophth->getPatient());
				$bil->setDescription("Ophthalmology charges: " . $data->getName());

				$bil->setItem($data);
				$bil->setSource((new BillSourceDAO())->findSourceById(13, $pdo));
				$bil->setTransactionType("credit");
				$bil->setAmount($amount);
				$bil->setDiscounted(null);
				$bil->setDiscountedBy(null);
				$bil->setClinic($staff->getClinic());
				$bil->setBilledTo($ophth->getPatient()->getScheme());
				$bil->setReferral($ophth->getReferral());
				$bil->setCostCentre((is_null($ophth->getServiceCentre())) ? null : $ophth->getServiceCentre()->getCostCentre());

				$bill = (new BillDAO())->addBill($bil, 1, $pdo);
				
				$sql2 .= "('" . $ophth->getPatient()->getId() . "', '" . $data->getId() . "','" . $ophth->getGroupName() . "', '" . $bill->getId() ."')";
				$summarynote .= $data->getName();
				if ($i != count($lab_data) - 1) {
					$summarynote .= ", ";
					$sql2 .= ", ";
				} else {
					$summarynote .= " requested.";
				}
				
			}
			$stmt2 = $pdo->prepare($sql2, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt2->execute();

			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VisitNotes.php';
			$vNote = (new VisitNotes())->setPatient($ophth->getPatient())->setNoteType('inv')->setDescription($summarynote)->setDateOfEntry(date('Y-m-d H:i:s'))->setNotedBy($staff);

			if (!(new VisitNotesDAO())->addNote($vNote, $pdo)) {
				$pdo->rollBack();
				exit("error:Failed to save Note");
			}
			/* create the Lab Queue for this patient*/
			require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/PatientQueue.php');
			require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php');
			require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php');

			$pat = new PatientDemograph();
			$pat->setId($ophth->getPatient()->getId());
			$pq = new PatientQueue();
			$pq->setType("Ophthalmology");
			$pq->setPatient($pat);
			(new PatientQueueDAO())->addPatientQueue($pq, $pdo);
			/*added to Ophthalmology queue*/

			if ($stmt->rowCount() == 1 && $stmt2->rowCount() == count($lab_data)) {
				$pdo->commit();
				return $ophth;
			}
			$pdo->rollBack();
			return null;

		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function saveResult($plObj, $pdo = null)
	{
//        $plObj = new PatientOphthalmology();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$canCommit = TRUE;
			try {
				$pdo->beginTransaction();
			} catch (PDOException $e) {
				$canCommit = FALSE;
			}
			$sql = "UPDATE patient_ophthalmology SET test_notes='" . escape($plObj->getNotes()) . "', test_date='" . $plObj->getTestDate() . "', performed_by = '" . $plObj->getPerformedBy()->getId() . "' WHERE id = " . $plObj->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ((new OphthalmologyResultDAO())->addResult($plObj->getOphthalmologyResult(), $pdo) === null) {
				error_log("ERROR: Failed to add result");
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

	function cancel($pl, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;

			$pdo->beginTransaction();
			$sql = "UPDATE patient_ophthalmology SET _status = 'cancelled' WHERE id = " . $pl->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				//update the status of this lab as cancelled,
				//then add a reversal bill
				//$price of this lab =
				$bill_old = (new BillDAO())->getBill($pl->getBill()->getId(), true, $pdo);
				// check if the bill has been transferred on not
				 $billTransf = (new BillDAO())->checkBill($pl->getBill()->getId(), true, $pdo);
				// get bills if transferred for cancellation
				$checkBill = (new BillDAO())->getTransferCreditOnly($pl->getBill()->getId(), true, $pdo);
				 if ($billTransf && $checkBill == null){
				 	$pdo->rollBack();
					 return false;
				 }
				 $price =  $pl->getBill()->getAmount();
				$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], TRUE, $pdo);
				 $patient = (new PatientDemographDAO())->getPatient($pl->getOphthalmologyGroup()->getPatient()->getId(), FALSE, $pdo, null);
					$bil = new Bill();
					$bil->setPatient($patient);
					$bil->setDescription("Ophthalmology Service Cancellation: " . $pl->getOphthalmology()->getName());
					$bil->setItem($pl->getOphthalmology());
					$bil->setSource((new BillSourceDAO())->findSourceById(13, $pdo));
					$bil->setTransactionType("reversal");
					$bil->setActiveBill('not_active');
					$bil->setTransactionDate(date("Y-m-d H:i:s"));
					$bil->setCancelledOn(date("Y-m-d H:i:s"));
					$bil->setCancelledBy((new StaffDirectory($_SESSION['staffID'])));
					$bil->setAmount(0 - $price);
					$bil->setDiscounted(null);
					$bil->setDiscountedBy(null);
					$bil->setClinic($staff->getClinic());
				  $bil->setBilledTo($patient->getScheme());
				
					if($checkBill == null){
						$bil->setParent($pl->getBill());
					}else{
						
						$bil->setParent($checkBill);
					}
				
					$costCentre = (is_null($pl->getServiceCentre())) ? null : (new ServiceCenterDAO())->get($pl->getServiceCentre()->getId(), $pdo)->getCostCentre();
					$bil->setCostCentre($costCentre);
					
					$bill = (new BillDAO())->addBill($bil, 1, $pdo, null);
					
					$c = (is_null($checkBill)) ? $bill_old->setCancelledBy((new StaffDirectory($_SESSION['staffID'])))->setCancelledOn(date(MainConfig::$mysqlDateTimeFormat))->setActiveBill('not_active')->update($pdo) : $checkBill->setCancelledBy((new StaffDirectory($_SESSION['staffID'])))->setCancelledOn(date(MainConfig::$mysqlDateTimeFormat))->setActiveBill('not_active')->update($pdo);
					
					if ($bill !== null && $c !== null) {
						$pdo->commit();
						return true;
					} else {
						$pdo->rollBack();
						return false;
					}
				
				
				
			}
		} catch (PDOException $e) {
			
			errorLog("Exception Ophthalmology request cancellation",$e);
			return FALSE;
		}
	}

	function updateResult($pl, $pdo = null)
	{
//        $pl = new PatientLab();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$sql = "UPDATE ophthalmology_result_data SET `value` = ? WHERE ophthalmology_result_id = ? AND ophthalmology_template_data_id = ?";
			$stmt = $pdo->prepare($sql);

			$exec = [];
			foreach ($pl->getOphthalmologyResult()->getData() as $data) {
				$stmt->bindParam(1, escape($data->getValue()), PDO::PARAM_STR);
				$stmt->bindParam(2, $data->getOphthalmologyResult()->getId(), PDO::PARAM_INT);
				$stmt->bindParam(3, $data->getOphthalmologyTemplateData()->getId(), PDO::PARAM_INT);
				$exec[] = $stmt->execute();
			}
			$sq = "UPDATE patient_ophthalmology SET test_notes = '" . $pl->getNotes() . "' WHERE id = " . $pl->getId();
			$st2 = $pdo->prepare($sq);
			$exec[] = $st2->execute();

			if (in_array(FALSE, $exec)) {
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
}