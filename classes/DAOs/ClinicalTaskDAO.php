<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ClinicalTaskDAO
 *
 * @author pauldic
 */
class ClinicalTaskDAO
{

	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTask.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InPatient.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function addClinicalTask($ct, $pdo = null)
	{
		//$ct = new ClinicalTask();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$inPatientId = $ct->getInPatient() === null ? "NULL" : $ct->getInPatient()->getId();
			$source = !is_blank($ct->getSource()) ? quote_esc_str($ct->getSource()) : 'NULL';
			$sourceInstance = $ct->getSourceInstance() ? $ct->getSourceInstance()->getId() : 'NULL';

			$ctid = $ct->getInPatient() !== null ? $this->hasClinicalTask($ct->getInPatient()->getId(), $pdo) : FALSE;
			if ($ctid === FALSE) {
				$sql = "INSERT INTO clinical_task (patient_id, in_patient_id, objective, `source`, source_instance_id) VALUES ('" . $ct->getPatient()->getId() . "', " . $inPatientId . ", '" . $ct->getObjective() . "', $source, $sourceInstance)";
				//error_log($sql);
				$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					$ct->setId($pdo->lastInsertId());
				} else {
					$pdo->rollBack();
					$ct = null;
				}
			} else {
				$ct->setId($ctid);
			}

			if (count($ct->getClinicalTaskData()) === 0) {//If there is no data
				$pdo->commit();
			} else {
				$ct->getClinicalTaskData()[0]->setClinicalTask(new ClinicalTask($ct->getId()));
				$ctd = $ct->getClinicalTaskData();
				if (count($ctd) === count((new ClinicalTaskDataDAO())->addClinicalTaskData($ctd, $pdo))) {
					$pdo->commit();
				} else {
					$pdo->rollBack();
					$ct = null;
				}
			}

			$stmt = null;
		} catch (PDOException $e) {
			$pdo->rollBack();
			$stmt = null;
			$ct = null;
			errorLog($e);
		}
		return $ct;
	}

	function hasClinicalTask($aid, $pdo = null)
	{
		$ct = FALSE;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT id FROM clinical_task WHERE in_patient_id=" . $aid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ct = $row['id'];
			} else {
				$ct = FALSE;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ct = FALSE;
		}
		return $ct;
	}


	function getClinicalTask($ctid, $getFull = FALSE, $pdo = null)
	{
		$ct = new ClinicalTask();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM clinical_task WHERE id=$ctid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ct->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$adm = (new InPatientDAO())->getInPatient($row['in_patient_id'], TRUE, $pdo);
				} else {
					$pat = (new PatientDemographDAO())->getPatientMin($row['patient_id'], $pdo);
					$adm = new InPatient($row['in_patient_id']);
				}
				$ct->setPatient($pat);
				$ct->setInPatient($adm);
				$ct->setObjective($row['objective']);
				$ct->setStatus($row['status']);
				$ct->setSource($row['source']);
				$ct->setSourceInstance( $row['source'] == 'ivf' ? (new IVFEnrollment($row['source_instance_id'])) : null );
				$ct->setClinicalTaskData((new ClinicalTaskDataDAO())->getClinicalTaskData($row['id'], FALSE, $pdo));
			} else {
				$ct = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ct = null;
		}
		return $ct;
	}

	function getPatientClinicalTasks($pid, $getFull = FALSE, $pdo = null)
	{
		$cts = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM clinical_task WHERE patient_id=" . $pid . " ORDER BY id DESC";
//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ct = new ClinicalTask();
				$ct->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$adm = (new InPatientDAO())->getInPatients($row['in_patient_id'], FALSE, $pdo);
				} else {
					$pat = (new PatientDemographDAO())->getPatientMin($row['patient_id'], $pdo);
					$adm = new InPatient($row['in_patient_id']);
				}
				$ct->setPatient($pat);
				$ct->setInPatient($adm);
				$ct->setObjective($row['objective']);
				$ct->setStatus($row['status']);
				$ct->setSource($row['source']);
				$ct->setSourceInstance( $row['source'] == 'ivf' ? (new IVFEnrollment($row['source_instance_id'])) : null );
				$ct->setClinicalTaskData((new ClinicalTaskDataDAO())->getClinicalTaskData($row['id'], FALSE, $pdo));
				$cts[] = $ct;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$cts = [];
		}
		return $cts;
	}

	function getInPatientClinicalTask($ipid, $getFull = FALSE, $pdo = null)
	{
		$ct = new ClinicalTask();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM clinical_task WHERE in_patient_id=" . $ipid . " ORDER BY id DESC";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ct->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$adm = (new InPatientDAO())->getInPatient($row['in_patient_id'], FALSE, $pdo);
				} else {
					$pat = (new PatientDemographDAO())->getPatientMin($row['patient_id'], $pdo);
					$adm = new InPatient($row['in_patient_id']);
				}
				$ct->setPatient($pat);
				$ct->setInPatient($adm);
				$ct->setObjective($row['objective']);
				$ct->setStatus($row['status']);
				$ct->setSource($row['source']);
				$ct->setSourceInstance( $row['source'] == 'ivf' ? (new IVFEnrollment($row['source_instance_id'])) : null );

				$ct->setClinicalTaskData((new ClinicalTaskDataDAO())->getClinicalTaskData($row['id'], $getFull, $pdo));
			} else {
				$ct = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ct = null;
		}
		return $ct;
	}

	function getPatientCurrentClinicalTask($pid, $aid, $getFull = FALSE, $pdo = null)
	{
		$cts = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM clinical_task WHERE in_patient_id " . ($aid !== null ? " =" . $aid : " IS NULL") . " AND patient_id=$pid ORDER BY id DESC #LIMIT 1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ct = new ClinicalTask();
				$ct->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$adm = (new InPatientDAO())->getInPatient($row['in_patient_id'], FALSE, $pdo);
					$ctTaskData = (new ClinicalTaskDataDAO())->getClinicalTaskData($row['id'], TRUE, $pdo);
				} else {
					$pat = (new PatientDemographDAO())->getPatientMin($row['patient_id'], $pdo);
					$adm = new InPatient($row['in_patient_id']);
					$ctTaskData = (new ClinicalTaskDataDAO())->getClinicalTaskData($row['id'], FALSE, $pdo);
				}
				$ct->setPatient($pat);
				$ct->setInPatient($adm);
				$ct->setObjective($row['objective']);
				$ct->setStatus($row['status']);
				$ct->setSource($row['source']);
				$ct->setSourceInstance( $row['source'] == 'ivf' ? (new IVFEnrollment($row['source_instance_id'])) : null );

				$ct->setClinicalTaskData($ctTaskData);

				$cts[] = $ct;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$cts = [];
		}

		return $cts;
	}


	function getPatientCurrentIVFClinicalTask($pid, $aid, $getFull = FALSE, $pdo = null)
	{
		$cts = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM clinical_task WHERE source_instance_id " . ($aid !== null ? " =" . $aid : " IS NULL") . " AND patient_id=$pid AND `source`='ivf' ORDER BY id DESC #LIMIT 1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ct = new ClinicalTask();
				$ct->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$adm = (new InPatientDAO())->getInPatient($row['in_patient_id'], FALSE, $pdo);
					$ctTaskData = (new ClinicalTaskDataDAO())->getIVFClinicalTaskData($row['id'], TRUE, $pdo);
				} else {
					$pat = (new PatientDemographDAO())->getPatientMin($row['patient_id'], $pdo);
					$adm = new InPatient($row['in_patient_id']);
					$ctTaskData = (new ClinicalTaskDataDAO())->getIVFClinicalTaskData($row['id'], FALSE, $pdo);
				}
				$ct->setPatient($pat);
				$ct->setInPatient($adm);
				$ct->setObjective($row['objective']);
				$ct->setStatus($row['status']);
				$ct->setSource($row['source']);
				$ct->setSourceInstance( $row['source'] == 'ivf' ? (new IVFEnrollment($row['source_instance_id'])) : null );

				$ct->setClinicalTaskData($ctTaskData);

				$cts[] = $ct;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$cts = [];
		}

		return $cts;
	}


	function getPatientCurrentClinicalTaskSlim($pid, $aid, $getFull = FALSE, $pdo = null)
	{
		$cts = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT ct.*, CONCAT_WS(' ', pd.fname, pd.mname, pd.lname) AS patientName, ctd.* FROM clinical_task ct LEFT JOIN clinical_task_data ctd ON ct.id=ctd.clinical_task_id LEFT JOIN patient_demograph pd ON ct.patient_id=pd.patient_ID WHERE in_patient_id " . ($aid !== null ? " =" . $aid : " IS NULL") . " AND patient_id=$pid ORDER BY id DESC #LIMIT 1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				/*$ct = new ClinicalTask();
				$ct->setId($row['id']);
				if ($getFull) {
						$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
						$adm = (new InPatientDAO())->getInPatient($row['in_patient_id'], FALSE, $pdo);
						$ctTaskData = (new ClinicalTaskDataDAO())->getClinicalTaskData($row['id'], TRUE, $pdo);
				} else {
						$pat = (new PatientDemographDAO())->getPatientMin($row['patient_id'], $pdo);
						$adm = new InPatient($row['in_patient_id']);
						$ctTaskData = (new ClinicalTaskDataDAO())->getClinicalTaskData($row['id'], FALSE, $pdo);
				}
				$ct->setPatient($pat);
				$ct->setInPatient($adm);
				$ct->setObjective($row['objective']);
				$ct->setStatus($row['status']);
				$ct->setClinicalTaskData($ctTaskData);

				$cts[] = $ct;*/
				$cts[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$cts = [];
		}
		return $cts;
	}

	function getInPatientClinicalTasks($pid, $aid, $getFull = FALSE, $pdo = null)
	{
		$ct = new ClinicalTask();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM clinical_task WHERE in_patient_id = $aid AND patient_id=" . $pid . " ORDER BY id DESC LIMIT 1";
//            error_log(">>>: ".$sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ct->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$adm = (new InPatientDAO())->getInPatient($row['in_patient_id'], FALSE, $pdo);
					$ctTaskData = (new ClinicalTaskDataDAO())->getClinicalTaskData($row['id'], TRUE, $pdo);
				} else {
					$pat = (new PatientDemographDAO())->getPatientMin($row['patient_id'], $pdo);
					$adm = new InPatient($row['in_patient_id']);
					$ctTaskData = (new ClinicalTaskDataDAO())->getClinicalTaskData($row['id'], FALSE, $pdo);
				}
				$ct->setPatient($pat);
				$ct->setInPatient($adm);
				$ct->setObjective($row['objective']);
				$ct->setStatus($row['status']);
				$ct->setSource($row['source']);
				$ct->setSourceInstance( $row['source'] == 'ivf' ? (new IVFEnrollment($row['source_instance_id'])) : null );

				$ct->setClinicalTaskData($ctTaskData);
			} else {
				$ct = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = $ct = null;
		}
		return $ct;
	}

	function getClinicalTasks($status = ['Active'], $getFull = FALSE, $pdo = null)
	{
		$cts = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM clinical_task WHERE `status` IN ('" . implode("', '", $status) . "') ORDER BY patient_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ct = new ClinicalTask();
				$ct->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$adm = (new InPatientDAO())->getInPatient($row['in_patient_id'], TRUE, $pdo);
				} else {
					$pat = (new PatientDemographDAO())->getPatientMin($row['patient_id'], $pdo);
					$adm = new InPatient($row['in_patient_id']);
				}
				$ct->setPatient($pat);
				$ct->setInPatient($row['in_patient_id'] !== null ? $adm : null);
				$ct->setObjective($row['objective']);
				$ct->setStatus($row['status']);
				$ct->setSource($row['source']);
				$ct->setSourceInstance( $row['source'] == 'ivf' ? (new IVFEnrollment($row['source_instance_id'])) : null );

				$ct->setClinicalTaskData((new ClinicalTaskDataDAO())->getClinicalTaskData($row['id'], FALSE, $pdo));
				$cts[] = $ct;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$cts = [];
		}
		return $cts;
	}
function getIVFClinicalTasks($ivfInstanceId, $status = ['Active'], $getFull = FALSE, $pdo = null)
	{
		$cts = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM clinical_task WHERE `status` IN ('" . implode("', '", $status) . "') AND `source`='ivf' AND source_instance_id=$ivfInstanceId ORDER BY patient_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ct = new ClinicalTask();
				$ct->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$adm = (new InPatientDAO())->getInPatient($row['in_patient_id'], TRUE, $pdo);
				} else {
					$pat = (new PatientDemographDAO())->getPatientMin($row['patient_id'], $pdo);
					$adm = new InPatient($row['in_patient_id']);
				}
				$ct->setPatient($pat);
				$ct->setInPatient($row['in_patient_id'] !== null ? $adm : null);
				$ct->setObjective($row['objective']);
				$ct->setStatus($row['status']);
				$ct->setSource($row['source']);
				$ct->setSourceInstance( $row['source'] == 'ivf' ? (new IVFEnrollment($row['source_instance_id'])) : null );

				$ct->setClinicalTaskData((new ClinicalTaskDataDAO())->getIVFClinicalTaskData($row['id'], FALSE, $pdo));
				$cts[] = $ct;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$cts = [];
		}
		return $cts;
	}

}
