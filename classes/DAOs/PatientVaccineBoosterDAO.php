<?php

/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 4/2/15
 * Time: 9:04 AM
 */
class PatientVaccineBoosterDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Vaccine.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientVaccine.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VaccineDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientVaccineBooster.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/VaccineBooster.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VaccineBoosterDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function add($pvb, $pdo = null)
	{
		$ret = FALSE;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO patient_vaccine_booster (patient_id, vaccinebooster_id, start_date, next_due_date) VALUES ('" . $pvb->getPatient()->getId() . "', " . $pvb->getVaccineBooster()->getId() . ", '" . $pvb->getStartDate() . "', '" . $pvb->getNextDueDate() . "')";
//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$pvb->setId($pdo->lastInsertId());
				$ret = TRUE;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$pvb = null;
		}
		return $ret;
	}

	function addLastTaken($pvb, $pdo = null)
	{
		$ret = FALSE;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE patient_vaccine_booster SET next_due_date='{$pvb->getNextDueDate()}', last_taken = '{$pvb->getLastTaken()}', charged=FALSE WHERE id={$pvb->getId()};";

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$ret = TRUE;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$stmt = null;
			$pvb = null;
		}
		return $ret;
	}

	function getPatientVaccineBooster($id, $getFull = FALSE, $pdo = null)
	{
		$pvb = new PatientVaccineBooster();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_vaccine_booster WHERE id=" . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pvb->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, null);
					$vb = (new VaccineBoosterDAO())->getVaccineBooster($row['vaccinebooster_id'], TRUE, $pdo);
				} else {
					$pat = new PatientDemograph();
					$pat->setId($row['patient_id']);
					$vb = new VaccineBooster();
					$vb->setId($row['vaccinebooster_id']);
				}
				$pvb->setPatient($pat);
				$pvb->setVaccineBooster($vb);
				$pvb->setStartDate($row['start_date']);
				$pvb->setNextDueDate($row['next_due_date']);
				$pvb->setLastTaken($row['last_taken']);
			} else {
				$pvb = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			error_log($e);
			$pvb = null;
		}
		return $pvb;
	}

	function getPatientVaccineBoosterByPatient($patientID, $getFull = FALSE, $pdo = null)
	{
		$p_b_vac = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_vaccine_booster WHERE patient_id=$patientID";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, null);
					$vb = (new VaccineBoosterDAO())->getVaccineBooster($row['vaccinebooster_id'], TRUE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$vb = new VaccineBooster($row['vaccinebooster_id']);
				}
				$p_b_vac[] = (new PatientVaccineBooster($row['id']))->setPatient($pat)->setVaccineBooster($vb)->setStartDate($row['start_date'])->setNextDueDate($row['next_due_date'])->setLastTaken($row['last_taken'])->setCharged((bool)$row['charged']);
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$p_b_vac = array();
		}
		return $p_b_vac;
	}
}