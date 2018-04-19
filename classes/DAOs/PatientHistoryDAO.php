<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/19/15
 * Time: 12:14 PM
 */
class PatientHistoryDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientHistory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientHistoryDataDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/HistoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function add($patientHistory, $assessment = null, $type = null, $pdo = null)
	{
//        $patientHistory = new PatientHistory();
		$instance = $assessment ? $assessment->getAntenatalInstance() : null;
		$instanceId = !is_null($instance) ? $instance->getId() : "NULL";
		$assessmentId = $assessment ? $assessment->getId() : "NULL";
		$typeStr = !is_null($type) ? "'" . $type . "'" : "NULL";
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$canCommit = !$pdo->inTransaction();
			try {
				$pdo->beginTransaction();
			} catch (PDOException $ef) {
				//
			}

			$sql = "INSERT INTO patient_history (assessment_id, patient_id, history_id, create_uid, create_date, instance_id, type) VALUES (" . $assessmentId . ", '" . $patientHistory->getPatient()->getId() . "','" . $patientHistory->getHistory()->getId() . "','" . $patientHistory->getCreator()->getId() . "',NOW(), $instanceId, $typeStr)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$_data = [];

			if ($stmt->rowCount() > 0) {
				$patientHistory->setId($pdo->lastInsertId());
				foreach ($patientHistory->getData() as $d) {
					$d->setPatientHistory($patientHistory);
					$_data[] = (new PatientHistoryDataDAO())->add($d, $pdo);
				}

				if (count($_data) == count($patientHistory->getData())) {
					if ($canCommit) {
						$pdo->commit();
					}
					$patientHistory->setId($pdo->lastInsertId());
					return $patientHistory;
				} else {
					error_log("error: Data inconsistency");
				}
			}
			$pdo->rollBack();
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function get($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_history WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$history = new PatientHistory($row['id']);
				$history->setCreator((new StaffDirectoryDAO())->getStaff($row['create_uid'], FALSE, $pdo));
				$history->setHistory((new HistoryDAO())->get($row['history_id'], $pdo));
				$history->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo));
				$history->setDate($row['create_date']);
				$history->setData((new PatientHistoryDataDAO())->forHistory($row['id'], $pdo));

				return $history;
			}
			return null;

		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function forPatient($pid, $pdo = null)
	{
		$array = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_history WHERE patient_id=$pid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$history = $this->get($row['id'], $pdo);
				$array[] = $history;
			}
			return $array;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}

	public function getForInstance($instanceId, $type, $pdo = null)
	{
		$array = [];


		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_history WHERE instance_id=$instanceId AND type='$type'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$history = $this->get($row['id'], $pdo);
				$array[] = $history;
			}
			return $array;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
}