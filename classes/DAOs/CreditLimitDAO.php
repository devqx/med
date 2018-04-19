<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/29/14
 * Time: 3:39 PM
 */
class CreditLimitDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/CreditLimit.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function getCreditLimit($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM credit_limit WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$limit = new CreditLimit();
				$limit->setId($row['id']);
				$limit->setPatient( (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, NULL) );
				$limit->setAmount($row['amount']);
				$limit->setExpiration($row['expiration']);
				$limit->setSetBy((new StaffDirectoryDAO())->getStaff($row['set_by'], FALSE, $pdo));

				$stmt = null;
				return $limit;
			} else {
				$stmt = null;
				return null;
			}
		} catch (PDOException $e) {
			return null;
		}
	}

	function allAudit($page=0, $pageSize=10, $dates, $staff=NULL, $pdo = null)
	{
		$startDate = $dates[0];
		$endDate = $dates[1];
		$total = 0;
		$sql = "SELECT cr.* FROM credit_limit_audit cr LEFT JOIN patient_demograph pd ON pd.patient_ID=cr.patient_id WHERE pd.active is TRUE AND DATE(cr.date_) BETWEEN DATE('$startDate') AND DATE('$endDate')";
		$sql .= !is_null($staff) ? " AND set_by=$staff":"";
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
			$sql .= " LIMIT $offset, $pageSize";
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$data = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$limit = (new CreditLimit($row['id']))
					->setPatient( (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, NULL) )
					->setAmount($row['amount'])
					->setExpiration($row['expiration'])
					->setReason($row['reason'])
					->setDate($row['date_'])
					->setSetBy((new StaffDirectoryDAO())->getStaff($row['set_by'], FALSE, $pdo));

				$data[] = $limit;
			}
		} catch (PDOException $e) {
			$data = [];
		}
		$results = (object)null;
		$results->data = $data;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}


	function getPatientLimit($patient_id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM credit_limit WHERE patient_id=$patient_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$limit = new CreditLimit();
				$limit->setId($row['id']);
//                $limit->setPatient( (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, NULL) );
				$limit->setAmount($row['amount']);
				$limit->setExpiration($row['expiration']);
				$limit->setSetBy((new StaffDirectoryDAO())->getStaff($row['set_by'], FALSE, $pdo));
				$limit->setReason($row['reason']);
				$stmt = null;
				return $limit;
			} else {
				$stmt = null;
				return 109;
			}
		} catch (PDOException $e) {
			return null;
		}
	}

	function addPatientLimit($c_limit, $pdo = null)
	{
		// $c_limit = new CreditLimit();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO credit_limit (patient_id, amount, expiration) VALUES (" . $c_limit->getPatient()->getId() . ", " . $c_limit->getAmount() . ", '" . date('Y-m-d') . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$stmt = null;
				$c_limit->setId($pdo->lastInsertId());
				return $c_limit;
			} else {
				$stmt = null;
				return null;
			}
		} catch (PDOException $e) {
			error_log("Exception: at add credit limit");
			$stmt = null;
			return null;
		}
	}

	function setPatientLimit($c_limit, $pdo = null)
	{
		@session_start();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$staff = $_SESSION['staffID'];
			$reason = escape($c_limit->getReason());
			$sql = "UPDATE credit_limit SET amount = " . $c_limit->getAmount() . ", expiration='" . $c_limit->getExpiration() . "', set_by=$staff, reason='$reason' WHERE id = " . $c_limit->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$stmt = null;
				return $c_limit;
			} else {
				$stmt = null;
				return null;
			}
		} catch (PDOException $e) {
			$stmt = null;
			return null;
		}
	}
} 