<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/9/14
 * Time: 4:02 PM
 */
class NurseReportDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/NurseReport.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			if (!isset($_SESSION))
				session_start();
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function getVisits($from = '', $to = '', $scheme = null, $provider = null, $pdo = null)
	{
		$visits = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$f = ($from == '') ? date("Y-m-d") : $from;
			$t = ($to == '') ? date("Y-m-d") : $to;
			$sid = ($scheme == null || $scheme == '') ? '' : ' AND e.scheme_id=' . $scheme;
			$prId = ($provider == null || $provider == '') ? '' : ' AND o.id=' . $provider;
			$group = ($scheme == null || $scheme == '') ? '' : ' , e.scheme_id';
			$group2 = ($provider == null || $provider == '') ? '' : ' , o.id';
			
			if ($scheme != null || $scheme != '' && ($provider != null && $provider != '')) {
				$group2 = '';
				$prId = '';
			}
			
			//$sql = "SELECT pq.patient_id, pq.*, COUNT(DISTINCT(pq.patient_id)) AS `count`, DATE(pq.entry_time) AS entry_time_, i.insurance_scheme, o.id AS scheme_owner FROM patient_queue pq LEFT JOIN insurance i ON i.patient_id = pq.patient_id LEFT JOIN insurance_schemes s ON s.id=i.insurance_scheme LEFT JOIN insurance_owners o ON o.id=s.scheme_owner_id WHERE DATE(pq.entry_time) BETWEEN DATE('$f') AND DATE('$t'){$sid}{$prId} GROUP BY DATE(pq.entry_time){$group}{$group2}";
			$sql = "SELECT COUNT(DISTINCT(e.patient_id)) AS `count`, e.* FROM encounter e LEFT JOIN insurance o ON o.insurance_scheme=e.scheme_id WHERE DATE(e.start_date) BETWEEN DATE('$f') AND DATE('$t'){$sid}{$prId} GROUP BY DATE(e.start_date){$group}{$group2}";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$visit = new NurseReport();
				
				$visit->setId($row['id']);
				$visit->setDate($row['start_date']);
				
				$patient = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, true);
				$visit->setPatient($patient);
				$visit->setScheme((($patient == null) ? (new InsuranceSchemeDAO())->get($row['scheme_id'], false, $pdo) : $patient->getScheme()));
				$visit->setType('Visit');
				//$visit->setMeta("SELECT pq.*, DATE(pq.entry_time) AS entry_time_, i.insurance_scheme, o.id AS scheme_owner FROM patient_queue pq LEFT JOIN insurance i ON i.patient_id = pq.patient_id LEFT JOIN insurance_schemes s ON s.id=i.insurance_scheme LEFT JOIN insurance_owners o ON o.id=s.scheme_owner_id WHERE DATE(pq.entry_time) = DATE('" . $row['entry_time_'] . "'){$sid}{$prId}");
				$visit->setMeta("SELECT e.*, DATE(e.start_date) AS entry_time_, e.scheme_id, o.id AS scheme_owner FROM encounter e LEFT JOIN insurance o ON o.insurance_scheme=e.scheme_id WHERE DATE(e.start_date) = DATE('" . $row['start_date'] . "'){$sid}{$prId}");
				
				//                $ins_scheme = ($scheme == NULL || $scheme == '')? NULL : $row['insurance_scheme'];
				//                $ins_owner = ($provider == NULL || $provider == '')? NULL : $row['scheme_owner'];
				//                $visit->setCount($this->countVisitsByDate($row['entry_time'], $ins_scheme, $ins_owner, $pdo));
				$visit->setCount($row['count']);
				
				$visits[] = $visit;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$visits = [];
		}
		return $visits;
	}
	
	private function countVisitsByDate($date, $scheme, $pdo = null)
	{
		$insurance = ($scheme == null || $scheme == '') ? '' : ' AND i.insurance_scheme="' . $scheme . '"';
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT pq.* FROM patient_queue pq LEFT JOIN insurance i ON i.patient_id = pq.patient_id WHERE DATE(pq.entry_time) = DATE('" . $date . "'){$insurance} GROUP BY pq.patient_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$visits = $stmt->rowCount();
			$stmt = null;
		} catch (PDOException $e) {
			$visits = 0;
		}
		return $visits;
	}
	
	function getVisitsByDate($date, $scheme, $pdo = null)
	{
		$insurance = ($scheme == null || $scheme == '') ? '' : 'AND i.insurance_scheme="' . $scheme . '" ';
		$visits = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT pq.* FROM patient_queue pq LEFT JOIN insurance i ON i.patient_id = pq.patient_id WHERE DATE(pq.entry_time) = DATE('" . $date . "') {$insurance} GROUP BY pq.patient_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$visits[] = $this->getVisit($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			return null;
		}
		return $visits;
	}
	
	function getVisitsByMeta($meta, $pdo = null)
	{
		$visits = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$meta = urldecode($meta) . " GROUP BY e.patient_id ORDER BY e.scheme_id";
			$stmt = $pdo->prepare($meta, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$visits[] = $this->getVisit($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
		return $visits;
	}
	
	function getVisit($id, $pdo = null)
	{
		$visit = new NurseReport();
		if (is_array($id))
			$id = $id[0];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM encounter WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$visit->setId($row['id']);
				$visit->setDate($row['start_date']);
				
				$patient = (new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo, true);
				$visit->setPatient($patient);
				$visit->setScheme((($patient == null) ? (new InsuranceSchemeDAO())->get($row['scheme_id'], false, $pdo) : $patient->getScheme()));
				$visit->setType('Visit');
				
			} else {
				return null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			return null;
		}
		return $visit;
	}
	
	function getEnrollments($from = null, $to = null, $scheme = null, $provider = null, $pdo = null)
	{
		$registrations = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$f = ($from == null) ? date("Y-m-d") : $from;
			$t = ($to == null) ? date("Y-m-d") : $to;
			$sid = ($scheme == null) ? '' : ' AND i.insurance_scheme=' . $scheme;
			$prId = ($provider == null || $provider == '') ? '' : ' AND o.id=' . $provider;
			$group = ($scheme == null || $scheme == '') ? '' : ', i.insurance_scheme';
			$group2 = ($provider == null || $provider == '') ? '' : ', o.id';
			
			$sql = "SELECT pd.*, i.insurance_scheme, o.id, COUNT(DISTINCT(pd.patient_ID)) AS `no_of_enrollments` FROM patient_demograph pd LEFT JOIN insurance i ON i.patient_id=pd.patient_ID LEFT JOIN insurance_schemes s ON s.id=i.insurance_scheme LEFT JOIN insurance_owners o ON o.id=s.scheme_owner_id WHERE DATE(pd.enrollment_date) BETWEEN DATE('$f') AND DATE('$t'){$sid}{$prId} GROUP BY DATE(pd.enrollment_date){$group}{$group2}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$reg = new NurseReport();
				
				$reg->setId($row['patient_ID']);
				$reg->setDate($row['enrollment_date']);
				
				$patient = (new PatientDemographDAO())->getPatient($row['patient_ID'], false, $pdo, true);
				$reg->setPatient($patient);
				$reg->setType('enrollment');
				$reg->setMeta("SELECT pd.*, i.insurance_scheme, o.id FROM patient_demograph pd LEFT JOIN insurance i ON i.patient_id=pd.patient_ID LEFT JOIN insurance_schemes s ON s.id=i.insurance_scheme LEFT JOIN insurance_owners o ON o.id=s.scheme_owner_id WHERE DATE(pd.enrollment_date) = DATE('" . $row['enrollment_date'] . "') {$sid}{$prId}");
				$reg->setScheme((($patient == null) ? (new InsuranceSchemeDAO())->get($row['insurance_scheme'], false, $pdo) : $patient->getScheme()));
				$reg->setCount($row['no_of_enrollments']);
				
				$registrations[] = $reg;
			}
			$stmt = null;
		} catch (PDOException $e) {
			return null;
		}
		return $registrations;
	}
	
	function getEnrollmentsByDate($date, $scheme, $pdo = null)
	{
		$insurance = ($scheme == null || $scheme == '') ? '' : ' AND i.insurance_scheme="' . $scheme . '"';
		$enrollments = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT pd.* FROM patient_demograph pd LEFT JOIN insurance i ON i.patient_id = pd.patient_ID WHERE DATE(pd.enrollment_date) = DATE('" . $date . "') {$insurance}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$enrollments[] = $this->getEnrollment($row['patient_ID'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			return null;
		}
		return $enrollments;
	}
	
	function getEnrollmentsByMeta($meta, $pdo = null)
	{
		$enrollments = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$meta = urldecode($meta);
			$stmt = $pdo->prepare($meta, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$enrollments[] = $this->getEnrollment($row['patient_ID'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			return null;
		}
		return $enrollments;
	}
	
	function getEnrollment($pid, $pdo = null)
	{
		$reg = new NurseReport();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_demograph WHERE patient_ID = '$pid'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$reg->setId($row['patient_ID']);
				$reg->setDate($row['enrollment_date']);
				
				$patient = (new PatientDemographDAO())->getPatient($row['patient_ID'], false, $pdo, true);
				$reg->setPatient($patient);
				$reg->setType('enrollment');
				$reg->setScheme((($patient == null) ? (new InsuranceSchemeDAO())->get($row['insurance_scheme'], false, $pdo) : $patient->getScheme()));
				
			} else {
				return null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			return null;
		}
		return $reg;
	}
}