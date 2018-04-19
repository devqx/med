<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AppointmentDAO
 *
 * @author pauldic
 */
class AppointmentDAO
{
	private $conn = null;
	public static $ERROR = -1;
	public static $OKAY = 0;
	public static $DUPLICATE = 1;
	public static $SOME_DUPLICATE = 2;

	public static $maxAppointmentsCount = 30;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Appointment.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AppointmentGroup.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AppointmentInvitee.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentGroupDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentInviteeDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function add($as, $pdo)
	{
		try {
			$added = 0;
			foreach ($as as $a) {
				if (!$this->hasAppointment($as[0]->getGroup()->getPatient()->getId(), [$as[0]->getGroup()->getClinic()->getId()], $a->getStartTime(), $a->getEndTime(), $pdo)) {
					$sql = "INSERT INTO appointment (group_id, start_time, end_time, editor_id) VALUES " . "(" . $as[0]->getGroup()->getId() . ", '" . $a->getStartTime() . "', " . (($a->getEndTime() === null || trim($a->getEndTime()) === "") ? 'NULL' : "'" . $a->getEndTime() . "'") . ", '" . $a->getEditor()->getId() . "')";
					$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					$stmt->execute();
					$added += 1;
				} else {
					error_log("=======error... appointment already exists ======");
				}
			}
			if ($added != sizeof($as)) {
				$as = [];
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$stmt = null;
			$as = [];
		}
		return $as;
	}
	
	
	function cancelProcedureAppointment($appt, $pdo=null){
		$status = FALSE;
		try{
		 $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
		 $sql = "UPDATE appointment a LEFT JOIN appointment_group g ON g.id=a.group_id SET a.status='Cancelled' WHERE a.status in ('Active', 'Scheduled') AND a.group_id=$appt";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$status = TRUE;
			$stmt = null;
		}catch (PDOException $e){
			$status = FALSE;
			errorLog($e);
		}
		return $status;
		
	}

	function hasAppointment($pid, $type = null, $startDate = null, $endDate=null, $pdo = null)
	{
		$status = FALSE;
		$filter = ($type == null) ? "" : " AND  g.clinic_id IN ('" . implode("', '", $type) . "')";
		$filter = $filter . (($startDate === null || $endDate === null) ? "" : " AND start_time = '" . date('Y-m-d H:i:s',strtotime($startDate) + 1) . "' AND end_time = '".date('Y-m-d H:i:s',strtotime($endDate) + 1)."'");
		// do not use the full-day date to check, use a time based period to check if not patient will not be allowed
		//to have more than one appointment in a day even at different times
		//if $startDate is not null and $endDate is null, then it's an `ALL DAY` appointment
		$filter .= ($startDate != null && $endDate == null) ? " AND DATE(start_time) = DATE('".$startDate."')" : "";
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT COUNT(*) as x FROM appointment a LEFT JOIN appointment_group g ON a.group_id=g.id WHERE a.status IN ('Scheduled', 'Active') AND g.patient_id=" . $pid . " {$filter} ORDER BY start_time #LIMIT 1";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				if ($row['x'] > 0) {
					$status = TRUE;
				}
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
		}
		return $status;
	}

	function getPatientNextAppointment($pid, $getFull = FALSE, $pdo = null)
	{
		$appoint = new Appointment();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT a.*, g.patient_id, g.id FROM appointment a LEFT JOIN appointment_group g ON a.group_id=g.id WHERE g.patient_id =$pid and date(start_time) >= date(now()) and `status` = 'scheduled' order by start_time limit 1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$appoint->setId($row['id']);
				if ($getFull) {
					$group = (new AppointmentGroupDAO())->getAppointmentGroup($row['group_id'], TRUE, $pdo);
					$editor = (new StaffDirectoryDAO())->getStaff($row['editor_id']);
				} else {
					$group = new AppointmentGroup();
					$group->setId($row['group_id']);
					$editor = new StaffDirectory();
					$editor->setId($row['editor_id']);
				}
				$appoint->setGroup($group);
				$appoint->setStartTime((explode(" ", $row['start_time'])[1] === "00:00:00") ? explode(" ", $row['start_time'])[0] : $row['start_time']);
				$appoint->setEndTime($row['end_time'] === null ? null : (explode(" ", $row['end_time'])[1] === "00:00:00") ? explode(" ", $row['end_time'])[0] : $row['end_time']);
				$appoint->setAttendedTime($row['attended_time']);
				$appoint->setStatus(($row['status'] === "Active" && strtotime(explode(" ", $row['start_time'])[0]) < strtotime(date("Y-m-d"))) ? "Missed" : "Active");
				$appoint->setEditor($editor);
			} else {
				$appoint = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$appoint = null;
		}
		return $appoint;
	}

	function getPatientNextAppointments($pid, $getFull = FALSE, $pdo = null)
	{
		$appoints = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT a.* FROM appointment a LEFT JOIN appointment_group g ON a.group_id=g.id WHERE g.patient_id=$pid AND (Date(a.start_time) >= Date(NOW()) OR (Date(a.start_time) = Date(NOW()) AND status='Scheduled')) ORDER BY start_time";
//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$appoint = new Appointment();
				$appoint->setId($row['id']);
				if ($getFull) {
					$group = (new AppointmentGroupDAO())->getAppointmentGroup($row['group_id'], TRUE, $pdo);
					$editor = (new StaffDirectoryDAO())->getStaff($row['editor_id']);
				} else {
					$group = new AppointmentGroup();
					$group->setId($row['group_id']);
					$editor = new StaffDirectory();
					$editor->setId($row['editor_id']);
				}
				$appoint->setGroup($group);
				$appoint->setStartTime((explode(" ", $row['start_time'])[1] === "00:00:00") ? explode(" ", $row['start_time'])[0] : $row['start_time']);
				$appoint->setEndTime($row['end_time'] === null ? null : (explode(" ", $row['end_time'])[1] === "00:00:00") ? explode(" ", $row['end_time'])[0] : $row['end_time']);
				$appoint->setAttendedTime($row['attended_time']);
				$appoint->setStatus(($row['status'] === "Active" && strtotime(explode(" ", $row['start_time'])[0]) < strtotime(date("Y-m-d"))) ? "Missed" : "Active");
				$appoint->setEditor($editor);

				$appoints[] = $appoint;
			}
		} catch (PDOException $e) {
		}
		return $appoints;
	}


	function getAppointment($aid, $getFull = FALSE, $pdo = null)
	{
		$appoint = new Appointment();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM appointment WHERE id=" . $aid;
//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$appoint->setId($row['id']);
				if ($getFull) {
					$group = (new AppointmentGroupDAO())->getAppointmentGroup($row['group_id'], TRUE, $pdo);
					$editor = (new StaffDirectoryDAO())->getStaff($row['editor_id'], TRUE, $pdo);
				} else {
					$group = new AppointmentGroup();
					$group->setId($row['group_id']);
					$editor = new StaffDirectory();
					$editor->setId($row['editor_id']);
				}
				$appoint->setGroup($group);
				$appoint->setStartTime((explode(" ", $row['start_time'])[1] === "00:00:00") ? explode(" ", $row['start_time'])[0] : $row['start_time']);
				$appoint->setEndTime($row['end_time'] === null ? null : (explode(" ", $row['end_time'])[1] === "00:00:00") ? explode(" ", $row['end_time'])[0] : $row['end_time']);
				$appoint->setAttendedTime($row['attended_time']);
				$appoint->setStatus(($row['status'] === "Active" && strtotime(explode(" ", $row['start_time'])[0]) < strtotime(date("Y-m-d"))) ? "Missed" : "Active");
				$appoint->setEditor($editor);

			} else {
				$appoint = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$stmt = null;
			$appoint = null;
		}
		return $appoint;
	}

	function getAppointments($aids = null, $getFull = FALSE, $pdo = null)
	{
		$appoints = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM appointment";
			$sql .= ($aids === null ? "" : " WHERE id IN (" . $aids . ")");
//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$appoint = new Appointment();
				$appoint->setId($row['id']);
				if ($getFull) {
					$group = (new AppointmentGroupDAO())->getAppointmentGroup($row['group_id'], TRUE, $pdo);
					$editor = (new StaffDirectoryDAO())->getStaff($row['editor_id']);
				} else {
					$group = new AppointmentGroup();
					$group->setId($row['group_id']);
					$editor = new StaffDirectory();
					$editor->setId($row['editor_id']);
				}
				$appoint->setGroup($group);
				$appoint->setStartTime((explode(" ", $row['start_time'])[1] === "00:00:00") ? explode(" ", $row['start_time'])[0] : $row['start_time']);
				$appoint->setEndTime($row['end_time'] === null ? null : (explode(" ", $row['end_time'])[1] === "00:00:00") ? explode(" ", $row['end_time'])[0] : $row['end_time']);
				$appoint->setAttendedTime($row['attended_time']);
				$appoint->setStatus(($row['status'] === "Active" && strtotime(explode(" ", $row['start_time'])[0]) < strtotime(date("Y-m-d"))) ? "Missed" : "Active");
				$appoint->setEditor($editor);

				$appoints[] = $appoint;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$appoints = array();
		}
		return $appoints;
	}

	function getPatientAppointments($pid, $aid = null, $getFull = FALSE, $pdo = null)
	{
		$appoints = array();
		$sql = "SELECT a.* FROM appointment a LEFT JOIN appointment_group g ON a.group_id=g.id WHERE a.id =" . $aid . " AND g.patient_id='" . $pid . "' ORDER BY start_time";

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$appoint = new Appointment();
				$appoint->setId($row['id']);
				if ($getFull) {
					$group = (new AppointmentGroupDAO())->getAppointmentGroup($row['group_id'], TRUE, $pdo);
					$editor = (new StaffDirectoryDAO())->getStaff($row['editor_id']);
				} else {
					$group = new AppointmentGroup();
					$group->setId($row['group_id']);
					$editor = new StaffDirectory();
					$editor->setId($row['editor_id']);
				}
				$appoint->setGroup($group);
				$appoint->setStartTime((explode(" ", $row['start_time'])[1] === "00:00:00") ? explode(" ", $row['start_time'])[0] : $row['start_time']);
				$appoint->setEndTime($row['end_time'] === null ? null : (explode(" ", $row['end_time'])[1] === "00:00:00") ? explode(" ", $row['end_time'])[0] : $row['end_time']);
				$appoint->setAttendedTime($row['attended_time']);
				$appoint->setStatus(($row['status'] === "Active" && strtotime(explode(" ", $row['start_time'])[0]) < strtotime(date("Y-m-d"))) ? "Missed" : "Active");
				$appoint->setEditor($editor);

				$appoints[] = $appoint;
			}
		} catch (PDOException $e) {
			$stmt = null;
			$appoints = [];
		}
		return $appoints;
	}


	function getMissedAppointments($page=0, $pageSize=10, $getFull = FALSE, $pdo = null)
	{
		$appoints = array();
		$total = 0;
		$sql = "SELECT a.* FROM appointment a LEFT JOIN appointment_group g ON a.group_id=g.id LEFT JOIN patient_demograph p ON p.patient_ID=g.patient_id WHERE Date(a.start_time) < Date(Now()) AND g.type != 'Meeting' AND status='Scheduled' ORDER BY a.start_time DESC, p.patient_ID, g.type";
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
			$sql .= " LIMIT $offset, $pageSize";
			// error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$appoint = new Appointment();
				$appoint->setId($row['id']);
				if ($getFull) {
					$group = (new AppointmentGroupDAO())->getAppointmentGroup($row['group_id'], TRUE, $pdo);
					$editor = (new StaffDirectoryDAO())->getStaff($row['editor_id']);
				} else {
					$group = new AppointmentGroup($row['group_id']);
					$editor = new StaffDirectory($row['editor_id']);
				}
				$appoint->setGroup($group);
				$appoint->setStartTime((explode(" ", $row['start_time'])[1] === "00:00:00") ? explode(" ", $row['start_time'])[0] : $row['start_time']);
				$appoint->setEndTime($row['end_time'] === null ? null : (explode(" ", $row['end_time'])[1] === "00:00:00") ? explode(" ", $row['end_time'])[0] : $row['end_time']);
				$appoint->setAttendedTime($row['attended_time']);
				$appoint->setStatus(($row['status'] === "Active" && strtotime(explode(" ", $row['start_time'])[0]) < strtotime(date("Y-m-d"))) ? "Missed" : "Active");
				$appoint->setEditor($editor);

				$appoints[] = $appoint;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$appoints = array();
		}
		$results = (object)null;
		$results->data = $appoints;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	/**
	 * Even if getFull is true, this object returns an array of appointments
	 * that are ordered by group but only the first Appointment in the list will be full
	 * @param $start
	 * @param $end
	 * @param array $status
	 * @param array $type
	 * @param bool $getFull
	 * @param int $page
	 * @param int $pageSize
	 * @param null $patient_id
	 * @param null $pdo
	 * @return object
	 */
	function getAppointmentByDate($start, $end, $status = ['scheduled'], $type = [], $getFull = FALSE, $page = 0, $pageSize = 10, $patient_id = null, $pdo = null)
	{

		$type = (isset($type) && sizeof($type) > 0) ? "AND g.type IN ('" . implode($type, "', '") . "')" : "";
		$Patient = ($patient_id != null && $patient_id != '') ? ' AND g.patient_id = ' . $patient_id : '';
		$sql = "SELECT p.*, g.patient_id FROM appointment p LEFT JOIN appointment_group g ON p.group_id=g.id LEFT JOIN appointment_clinic c ON c.id=g.clinic_id WHERE DATE(start_time) BETWEEN '" . $start . "' AND '" . $end . "' AND `status` IN ('" . implode($status, "', '") . "'){$type}{$Patient} /*AND g.department_id IS NULL*/ ORDER BY FIELD(status, 'Scheduled', 'Active', 'Completed', 'Missed' , 'Cancelled'), Date(start_time), end_time DESC";
		//error_log($sql);
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
			$appoints = array();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$appoint = new Appointment();
				$appoint->setId($row['id']);
				if ($getFull) {
					$group = (new AppointmentGroupDAO())->getAppointmentGroup($row['group_id'], TRUE, $pdo);
					$editor = (new StaffDirectoryDAO())->getStaff($row['editor_id'], FALSE, $pdo);
				} else {
					$group = new AppointmentGroup();
					$group->setId($row['group_id']);
					$editor = new StaffDirectory();
					$editor->setId($row['editor_id']);
				}
				$appoint->setGroup($group);
				$appoint->setStartTime((explode(" ", $row['start_time'])[1] === "00:00:00") ? explode(" ", $row['start_time'])[0] : $row['start_time']);
				$appoint->setEndTime($row['end_time'] === null ? null : (explode(" ", $row['end_time'])[1] === "00:00:00") ? explode(" ", $row['end_time'])[0] : $row['end_time']);
				$appoint->setAttendedTime($row['attended_time']);
				$appoint->setStatus(($row['status'] === "Scheduled" && strtotime(explode(" ", $row['start_time'])[0]) < strtotime(date("Y-m-d"))) ? "Missed" : $row['status']);
				$appoint->setEditor($editor);
				
				$appoints[] = $appoint;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$appoints = array();
		}
		$results = (object)null;
		$results->data = $appoints;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function getAppointmentByDateSlim($start, $end, $status = ['scheduled'], $type = [], $getFull = FALSE, $page = 0, $pageSize = 10, $patient_id = null, $pdo = null)
	{

		$type = (isset($type) && sizeof($type) > 0) ? "AND g.type IN ('" . implode($type, "', '") . "')" : "";
		$Patient = ($patient_id != null && $patient_id != '') ? ' AND g.patient_id = ' . $patient_id : '';
		$sql = "SELECT p.*, g.description, g.clinic_id, g.is_all_day, g.resource_id, g.patient_id, c.name AS clinic_name, c.queue_type AS clinicType FROM appointment p LEFT JOIN appointment_group g ON p.group_id=g.id LEFT JOIN appointment_clinic c ON c.id=g.clinic_id WHERE DATE(start_time) BETWEEN '" . $start . "' AND '" . $end . "' AND `status` IN ('" . implode($status, "', '") . "'){$type}{$Patient} AND g.patient_id IS NOT NULL /*AND g.department_id IS NULL*/ ORDER BY  FIELD(status, 'Scheduled', 'Active', 'Completed', 'Missed' , 'Cancelled'), Date(start_time), end_time DESC";
		//error_log($sql);
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
			$appoints = array();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$row['start_time'] = ((explode(" ", $row['start_time'])[1] === "00:00:00") ? explode(" ", $row['start_time'])[0] : $row['start_time']);
				$row['end_time'] = ($row['end_time'] === null ? null : (explode(" ", $row['end_time'])[1] === "00:00:00") ? explode(" ", $row['end_time'])[0] : $row['end_time']);
				$row['status'] = (($row['status'] === "Scheduled" && strtotime(explode(" ", $row['start_time'])[0]) < strtotime(date("Y-m-d"))) ? "Missed" : $row['status']);
				$appoints[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$appoints = array();
		}
		$results = (object)null;
		$results->data = $appoints;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function getPatientsInAppointments($start, $end, $status = ['scheduled'], $type = [], $getFull = FALSE, $page = 0, $pageSize = 10, $patFilter = null, $pdo = null)
	{

		$type = (isset($type) && sizeof($type) > 0) ? "AND type IN ('" . implode($type, "', '") . "')" : "";
		$sql = "SELECT g.patient_id FROM appointment p LEFT JOIN appointment_group g ON p.group_id=g.id WHERE  Date(start_time) BETWEEN '" . $start . "' AND '" . $end . "' AND `status` IN ('" . implode($status, "', '") . "')${type} /*AND g.department_id IS NULL*/ ORDER BY  FIELD(status, 'Scheduled', 'Active', 'Completed', 'Missed' , 'Cancelled'), Date(start_time), end_time DESC";

		$SQL = "SELECT d.*, d.patient_ID AS patientId FROM patient_demograph d WHERE d.patient_ID IN ($sql) AND (d.fname LIKE '%$patFilter%' OR d.lname LIKE '%$patFilter%' OR d.patient_ID LIKE '%$patFilter%')";

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$SQL .= "  LIMIT $offset, $pageSize";
			$patients = array();
			$stmt = $pdo->prepare($SQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$patients[] = $row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$patients = array();
			errorLog($e);
		}

		return $patients;
	}

	function countAppointmentByDate($start, $end, $status = ['scheduled'], $department_id, $pdo = null)
	{
		$count = 0;

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT COUNT(*) AS x FROM appointment LEFT JOIN appointment_group ag ON group_id = ag.id LEFT JOIN patient_demograph d ON d.patient_ID=ag.patient_id WHERE d.active IS TRUE AND Date(start_time) BETWEEN '" . $start . "' AND '" . $end . "' AND status IN ('" . implode($status, "', '") . "') #AND ag.department_id = " . $department_id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$count = $row['x'];
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$count = 0;
		}
		return $count;
	}


	function getAppointmentByDateGrouped($start, $end, $status = ['scheduled'], $getFull = FALSE, $clinicId=null, $pdo = null)
	{
		$appoints = array();


		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT a.id, a.group_id, a.start_time, a.end_time, a.attended_time, a.status,
                (SELECT COUNT(*) FROM appointment aa LEFT JOIN appointment_group gg ON aa.group_id=gg.id WHERE  Date(a.start_time) = Date(aa.start_time) AND g.clinic_id=gg.clinic_id AND aa.status IN ('" . implode($status, "', '") . "')) AS x,
                (SELECT GROUP_CONCAT(aaa.id) FROM appointment aaa LEFT JOIN appointment_group ggg ON aaa.group_id=ggg.id WHERE  Date(a.start_time) = Date(aaa.start_time) AND g.clinic_id=ggg.clinic_id AND aaa.status IN ('" . implode($status, "', '") . "')) AS ids
                FROM appointment a LEFT JOIN appointment_group g ON a.group_id=g.id 
                WHERE Date(start_time) BETWEEN '" . $start . "' AND '" . $end . "' AND a.status IN ('" . implode($status, "', '") . "')";
			if(!is_blank($clinicId)){
				$sql.= " AND clinic_id=".$clinicId;
			}
			$sql .= " ORDER BY x DESC, Date(a.start_time),  g.clinic_id, a.end_time DESC";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$appoint = new Appointment();
				$appoint->setId($row['id']);
				if ($getFull) {
					$group = (new AppointmentGroupDAO())->getAppointmentGroup($row['group_id'], TRUE, $pdo);
				} else {
					$group = new AppointmentGroup();
					$group->setId($row['group_id']);
				}
				$appoint->setGroup($group);
				$appoint->setStartTime((explode(" ", $row['start_time'])[1] === "00:00:00") ? explode(" ", $row['start_time'])[0] : $row['start_time']);
				$appoint->setEndTime($row['end_time'] === null ? null : (explode(" ", $row['end_time'])[1] === "00:00:00") ? explode(" ", $row['end_time'])[0] : $row['end_time']);
				$appoint->setAttendedTime($row['attended_time']);
				$appoint->setStatus(($row['status'] === "Active" && strtotime(explode(" ", $row['start_time'])[0]) < strtotime(date("Y-m-d"))) ? "Missed" : "Active");

				$appoint->setIds($row['ids']);
				$appoint->setCount($row['x']);
				$appoints[] = $appoint;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$pdo = null;
			$appoints = array();
		}
		return $appoints;
	}

function getAppointmentByDateGroupedSlim($start, $end, $status = ['scheduled'], $getFull = FALSE, $clinicId=null, $pdo = null)
	{
		$appoints = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT a.id, a.group_id, a.start_time, a.end_time, a.attended_time, a.status, ac.name AS clinic_name, g.is_all_day, g.patient_id FROM appointment a LEFT JOIN appointment_group g ON a.group_id=g.id LEFT JOIN appointment_clinic ac ON g.clinic_id=ac.id WHERE DATE(start_time) BETWEEN '$start' AND '$end' AND a.status IN ('" . implode($status, "', '") . "')";
			//$sql = "SELECT a.id, a.group_id, a.start_time, a.end_time, a.attended_time, a.status, ac.name AS clinic_name, g.is_all_day, g.patient_id, (SELECT COUNT(*) FROM appointment aa LEFT JOIN appointment_group gg ON aa.group_id=gg.id WHERE  Date(a.start_time) = Date(aa.start_time) AND g.clinic_id=gg.clinic_id AND aa.status IN ('" . implode($status, "', '") . "')) AS x, (SELECT GROUP_CONCAT(aaa.id) FROM appointment aaa LEFT JOIN appointment_group ggg ON aaa.group_id=ggg.id WHERE  Date(a.start_time) = Date(aaa.start_time) AND g.clinic_id=ggg.clinic_id AND aaa.status IN ('" . implode($status, "', '") . "')) AS ids FROM appointment a LEFT JOIN appointment_group g ON a.group_id=g.id LEFT JOIN appointment_clinic ac ON g.clinic_id=ac.id WHERE DATE(start_time) BETWEEN '$start' AND '$end' AND a.status IN ('" . implode($status, "', '") . "')";
			if(!is_blank($clinicId)){
				$sql.= " AND g.clinic_id=".$clinicId;
			}
			//$sql .= " ORDER BY x DESC, Date(a.start_time),  g.clinic_id, a.end_time DESC";
			$sql .= " ORDER BY Date(a.start_time),  g.clinic_id, a.end_time DESC";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {

				$row['start_time'] = ((explode(" ", $row['start_time'])[1] === "00:00:00") ? explode(" ", $row['start_time'])[0] : $row['start_time']);
				$row['end_time'] = ($row['end_time'] === null ? null : (explode(" ", $row['end_time'])[1] === "00:00:00") ? explode(" ", $row['end_time'])[0] : $row['end_time']);
				$row['status'] = (($row['status'] === "Active" && strtotime(explode(" ", $row['start_time'])[0]) < strtotime(date("Y-m-d"))) ? "Missed" : "Active");

				//$appoint->setIds($row['ids']);
				//$appoint->setCount($row['x']);
				$appoints[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$pdo = null;
			$appoints = array();
		}
		return $appoints;
	}


	function getAppointmentByPatient($pid, $start = null, $end = null, $status = ['scheduled'], $getFull = FALSE, $pdo = null)
	{
		$appoints = array();
		$filter = ($start === null && $end === null) ? "" : " AND DATE(start_time) BETWEEN '" . $start . "' AND '" . $end . "'";
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT a.* FROM appointment a LEFT JOIN appointment_group g ON a.group_id=g.id WHERE " . "g.patient_id='" . $pid . "' AND `status` IN ('" . implode($status, "', '") . "') $filter";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$appoint = new Appointment();
				$appoint->setId($row['id']);
				if ($getFull) {
					$group = (new AppointmentGroupDAO())->getAppointmentGroup($row['group_id'], TRUE, $pdo);
					$editor = (new StaffDirectoryDAO())->getStaff($row['editor_id']);
				} else {
					$group = new AppointmentGroup();
					$group->setId($row['group_id']);
					$editor = new StaffDirectory();
					$editor->setId($row['editor_id']);
				}
				$appoint->setGroup($group);
				$appoint->setStartTime((explode(" ", $row['start_time'])[1] === "00:00:00") ? explode(" ", $row['start_time'])[0] : $row['start_time']);
				$appoint->setEndTime($row['end_time'] === null ? null : (explode(" ", $row['end_time'])[1] === "00:00:00") ? explode(" ", $row['end_time'])[0] : $row['end_time']);
				$appoint->setAttendedTime($row['attended_time']);
				$appoint->setStatus(($row['status'] === "Active" && strtotime(explode(" ", $row['start_time'])[0]) < strtotime(date("Y-m-d"))) ? "Missed" : "Active");
				$appoint->setEditor($editor);

				$appoints[] = $appoint;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;$pdo = null;
			$appoints = array();
		}
		return $appoints;
	}
function getAppointmentByPatientSlim($pid, $start = null, $end = null, $status = ['scheduled'], $getFull = FALSE, $clinic=null, $pdo = null)
	{
		$appoints = array();
		$filter = ($start === null && $end === null) ? "" : " AND DATE(a.start_time) BETWEEN '" . $start . "' AND '" . $end . "'";
		if(!is_blank($clinic)){
			$filter .= " AND g.clinic_id = $clinic";
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//$sql = "SELECT a.* FROM appointment a LEFT JOIN appointment_group g ON a.group_id=g.id WHERE " . "g.patient_id='" . $pid . "' AND `status` IN ('" . implode($status, "', '") . "') $filter";
			$sql = "SELECT a.id, a.group_id, a.start_time, a.end_time, a.status, g.patient_id, g.is_all_day, g.description, c.name AS clinic_name, g.resource_id FROM appointment a LEFT JOIN appointment_group g ON a.group_id=g.id LEFT JOIN appointment_clinic c ON c.id=g.clinic_id WHERE g.patient_id=$pid AND a.status IN ('" . implode($status, "', '") . "') $filter";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$row['start_time'] = ((explode(" ", $row['start_time'])[1] === "00:00:00") ? explode(" ", $row['start_time'])[0] : $row['start_time']);
				$row['end_time'] = ($row['end_time'] === null ? null : (explode(" ", $row['end_time'])[1] === "00:00:00") ? explode(" ", $row['end_time'])[0] : $row['end_time']);
				$row['status'] = (($row['status'] === "Active" && strtotime(explode(" ", $row['start_time'])[0]) < strtotime(date("Y-m-d"))) ? "Missed" : "Active");

				$appoints[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog( $e );
			$pdo = null;
			$appoints = array();
		}
		return $appoints;
	}

	function getAppointmentByStaffSlim($staff_id, $start = null, $end = null, $status = ['scheduled'], $getFull = FALSE, $pdo = null)
	{
		if (is_blank($staff_id)) {
			$staff_query = " LIKE '%'";
		} else {
			$staff_query = " = " . $staff_id;
		}
		$appoints = array();
		$filter = ($start === null && $end === null) ? "" : " AND DATE(start_time) BETWEEN '" . $start . "' AND '" . $end . "'";
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT a.id, a.group_id, a.start_time, a.end_time, a.`status`, g.clinic_id, g.description, g.patient_id, g.is_all_day, c.name AS clinic_name FROM appointment a LEFT JOIN appointment_group g ON a.group_id=g.id LEFT JOIN appointment_clinic c ON c.id=g.clinic_id LEFT JOIN appointment_invitee i ON g.id=i.group_id WHERE i.staff_id $staff_query AND `status` IN ('" . implode($status, "', '") . "') $filter";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$row['start_time'] = ((explode(" ", $row['start_time'])[1] === "00:00:00") ? explode(" ", $row['start_time'])[0] : $row['start_time']);
				$row['end_time'] = ($row['end_time'] === null ? null : (explode(" ", $row['end_time'])[1] === "00:00:00") ? explode(" ", $row['end_time'])[0] : $row['end_time']);
				$row['status'] = (($row['status'] === "Active" && strtotime(explode(" ", $row['start_time'])[0]) < strtotime(date("Y-m-d"))) ? "Missed" : "Active");

				$appoints[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$pdo = null;
			$appoints = array();
		}
		return $appoints;
	}


	function getAppointmentByResourceSlim($resourceId, $start = null, $end = null, $status = ['scheduled'], $getFull = FALSE, $pdo = null)
	{
		if (is_blank($resourceId)) {
			$filterSQL = " LIKE '%'";
		} else {
			$filterSQL = " = " . $resourceId;
		}
		$appoints = array();
		$filter = ($start === null && $end === null) ? "" : " AND DATE(start_time) BETWEEN '" . $start . "' AND '" . $end . "'";
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT a.id, a.group_id, a.start_time, a.end_time, a.`status`, g.clinic_id, g.description, g.patient_id, g.is_all_day, c.name AS clinic_name FROM appointment a LEFT JOIN appointment_group g ON a.group_id=g.id LEFT JOIN appointment_clinic c ON c.id=g.clinic_id LEFT JOIN appointment_resource i ON g.id=i.group_id WHERE i.resource_id $filterSQL AND `status` IN ('" . implode($status, "', '") . "') $filter";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$row['start_time'] = ((explode(" ", $row['start_time'])[1] === "00:00:00") ? explode(" ", $row['start_time'])[0] : $row['start_time']);
				$row['end_time'] = ($row['end_time'] === null ? null : (explode(" ", $row['end_time'])[1] === "00:00:00") ? explode(" ", $row['end_time'])[0] : $row['end_time']);
				$row['status'] = (($row['status'] === "Active" && strtotime(explode(" ", $row['start_time'])[0]) < strtotime(date("Y-m-d"))) ? "Missed" : "Active");

				$appoints[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$pdo = null;
			$appoints = array();
		}
		return $appoints;
	}


	function getAppointmentByStaff($staff_id, $start = null, $end = null, $status = ['scheduled'], $getFull = FALSE, $pdo = null)
	{
		if (is_blank($staff_id)) {
			$staff_query = " LIKE '%'";
		} else {
			$staff_query = " = " . $staff_id;
		}
		$appoints = array();
		$filter = ($start === null && $end === null) ? "" : " AND Date(start_time) BETWEEN '" . $start . "' AND '" . $end . "'";
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT a.* FROM appointment a LEFT JOIN appointment_group g ON a.group_id=g.id LEFT JOIN appointment_invitee i ON g.id=i.group_id WHERE " . "i.staff_id $staff_query AND status IN ('" . implode($status, "', '") . "') $filter";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$appoint = new Appointment();
				$appoint->setId($row['id']);
				if ($getFull) {
					$group = (new AppointmentGroupDAO())->getAppointmentGroup($row['group_id'], TRUE, $pdo);
					$editor = (new StaffDirectoryDAO())->getStaff($row['editor_id']);
				} else {
					$group = new AppointmentGroup();
					$group->setId($row['group_id']);
					$editor = new StaffDirectory();
					$editor->setId($row['editor_id']);
				}
				$appoint->setGroup($group);
				$appoint->setStartTime((explode(" ", $row['start_time'])[1] === "00:00:00") ? explode(" ", $row['start_time'])[0] : $row['start_time']);
				$appoint->setEndTime($row['end_time'] === null ? null : (explode(" ", $row['end_time'])[1] === "00:00:00") ? explode(" ", $row['end_time'])[0] : $row['end_time']);
				$appoint->setAttendedTime($row['attended_time']);
				$appoint->setStatus(($row['status'] === "Active" && strtotime(explode(" ", $row['start_time'])[0]) < strtotime(date("Y-m-d"))) ? "Missed" : "Active");
				$appoint->setEditor($editor);

				$appoints[] = $appoint;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = $pdo = null;
			$appoints = array();
		}
		return $appoints;
	}

	function getAppointmentByGroup($gid, $getFull = FALSE, $pdo = null)
	{
		$appoints = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM appointment WHERE group_id=" . $gid . " ORDER BY start_time";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$appoint = new Appointment();
				$appoint->setId($row['id']);
				if ($getFull) {
					$group = (new AppointmentGroupDAO())->getAppointmentGroup($row['group_id'], TRUE, $pdo);
					$editor = (new StaffDirectoryDAO())->getStaff($row['editor_id']);
				} else {
					$group = new AppointmentGroup();
					$group->setId($row['group_id']);
					$editor = new StaffDirectory();
					$editor->setId($row['editor_id']);
				}
				$appoint->setGroup($group);
				$appoint->setStartTime((explode(" ", $row['start_time'])[1] === "00:00:00") ? explode(" ", $row['start_time'])[0] : $row['start_time']);
				$appoint->setEndTime((explode(" ", $row['end_time'])[1] === "00:00:00") ? explode(" ", $row['end_time'])[0] : $row['end_time']);
				$appoint->setAttendedTime($row['attended_time']);
				$appoint->setStatus(($row['status'] === "Active" && strtotime(explode(" ", $row['start_time'])[0]) < strtotime(date("Y-m-d"))) ? "Missed" : "Active");
				$appoint->setEditor($editor);

				$appoints[] = $appoint;
				$gid = $row['group_id'];
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = $pdo = null;
			$appoints = array();
		}
		return $appoints;
	}

	function setStatus($aid, $new_status, $sid, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE appointment SET `status` = '" . $new_status . "', editor_id='" . $sid . "', attended_time=IF('$new_status'='Active', NOW(), attended_time) WHERE id=" . $aid;
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() > 0) {
				return true;
			}
			return false;
		} catch (PDOException $e) {
			errorLog($e);
			return false;
		}
	}

	function updateAppointment($app, $pdo = null)
	{
		$status = TRUE;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE appointment SET start_time = '" . $app->getStartTime() . "', end_time = " . (($app->getEndTime() === null || trim($app->getEndTime()) === "") ? 'NULL' : "'" . $app->getEndTime() . "'") . ", editor_id='" . $app->getEditor()->getId() . "' WHERE id=" . $app->getId();
//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$stmt = null;
		} catch (PDOException $e) {
			$status = FALSE;
		}
		return $status;
	}

	function cancelAppointment($qid = null, $pid = null, $pdo = null)
	{
		$status = FALSE;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE appointment a LEFT JOIN appointment_group g ON g.id=a.group_id SET a.status = 'Cancelled' WHERE a.start_time > NOW() AND " . ($qid === null ? " g.patient_id=$pid" : " a.id=$qid");
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$status = TRUE;
			$stmt = null;
		} catch (PDOException $e) {
			$status = FALSE;
		}
		return $status;
	}

	function getNumAppointments($staffId, $date, $pdo = null)
	{
		$sql = "SELECT count(*) AS `num`, v.staff_id, appointment.start_time FROM appointment LEFT JOIN `appointment_invitee` v ON v.group_id=appointment.group_id WHERE staff_id=$staffId /*AND DATE(start_time)=DATE('$date')*/ GROUP BY staff_id, DATE(start_time) ORDER BY `appointment`.`start_time` DESC";

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			// error_log($sql);
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return $row['num'];
			}
			return 0;
		} catch (PDOException $e) {
			errorLog($e);
			return 0;
		}
	}

}
