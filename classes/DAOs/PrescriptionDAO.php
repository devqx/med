<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PrescriptionDAO
 *
 * @author pauldic
 */
class PrescriptionDAO
{

	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InPatient.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Prescription.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PrescriptionData.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BodyPartDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/BodyPart.php';

			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function generateRegimenCode($pdo)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT LPAD( COUNT(*)+1 , 7, 0) AS val FROM `patient_regimens` WHERE MONTH(`when`) = MONTH(NOW())";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return 'PR' . date("y/m/") . $row['val'];
			}
			return 'PR' . date("y/m/") . strtoupper(substr(str_shuffle(md5(microtime())), 0, 4));
		} catch (PDOException $e) {
			errorLog($e);
			return 'PR' . date("y/m/") . strtoupper(substr(str_shuffle(md5(microtime())), 0, 4));
		}
	}
	

	function addPrescription($p, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$canCommit = !$pdo->inTransaction();
			try {
				$pdo->beginTransaction();
			} catch (PDOException $e) {
			}
			//wait();
			//wait();
			$p->setCode($this->generateRegimenCode($pdo));
			$encounter = $p->getEncounter() ? $p->getEncounter()->getId() : "NULL";
			$prescribedBy = quote_esc_str($p->getPrescribedBy());
			$sql = "INSERT INTO patient_regimens (patient_id, group_code, requested_by, in_patient_id, note, hospid, service_centre_id, refill_off, encounter_id, prescribed_by) VALUES ('" . $p->getPatient()->getId() . "', '" . $p->getCode() . "', '" . $p->getRequestedBy()->getId() . "', " . (is_null($p->getInPatient()) || $p->getInPatient()->getId() == '' ? "NULL" : $p->getInPatient()->getId()) . ", '" . escape($p->getNote()) . "', '" . $p->getHospital()->getId() . "', " . (is_null($p->getServiceCentre()) ? "NULL" : $p->getServiceCentre()->getId()) . ",NULL, $encounter,".$prescribedBy.")";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$p->setId($pdo->lastInsertId());
				if ($p->getData() == null) {
					if ($canCommit) {
						$pdo->rollBack();
					}
					$stmt = null;
					return null;
				}
				$pd = $p->getData();
				$pd[0]->setCode($p->getCode());
				$pd_ = (is_null($p->getInPatient())) ? (new PrescriptionDataDAO())->addPrescriptionData($pd, $pdo) : (new PrescriptionDataDAO())->addInPatientPrescriptionData($pd,  $pdo);
				if (sizeof($pd_) === 0) {
					if ($canCommit) {
						$pdo->rollBack();
					}
					$stmt = null;
					return null;
				}
			} else {
				if ($canCommit) {
					$pdo->rollBack();
				}
				$stmt = null;
				return null;
			}
			if ($canCommit) {
				$pdo->commit();
			}

			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$pdo->rollBack();
			$stmt = null;
			$p = null;
		}
		return $p;
	}

	function markExternal($pres, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE patient_regimens SET external=" . var_export((bool)$pres->getExternal(), true) . " WHERE id=" . $pres->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return TRUE;
			}
			return FALSE;
		} catch (PDOException $e) {
			errorLog($e);
			return FALSE;
		}
	}

	function updateServiceCenter($presc, $pdo = null)
	{
		$p = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE patient_regimens SET service_centre_id=" . $presc->getServiceCentre()->getId() . " WHERE id=" . $presc->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$p = $presc;
			}

			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$stmt = null;
			$p = null;
		}
		return $p;
	}

	function getPrescription($pid, $getFull = FALSE, $pdo = null)
	{
		$pres = new Prescription();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_regimens WHERE id=" . $pid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pres->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$req = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
					$inpatient = (new InPatientDAO())->getInPatient($row['in_patient_id'], false, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$req = new StaffDirectory($row['requested_by']);
					$hosp = new Clinic($row['hospid']);
					$inpatient = new InPatient($row['in_patient_id']);
				}
				$pres->setExternal((bool)$row['external']);
				$pres->setPatient($pat);
				$pres->setWhen(date("c", strtotime($row['when'])));
				$pres->setCode($row['group_code']);
				$pres->setRequestedBy($req);
				$pres->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				$pres->setInPatient($inpatient);
				$pres->setNote($row['note']);
				$pres->setHospital($hosp);
				$pres->setPrescribedBy($row['prescribed_by']);

				$pres->setData((new PrescriptionDataDAO())->getPrescriptionDataByCode($row['group_code'], TRUE, $pdo));
			} else {
				$pres = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pres = null;
		}
		return $pres;
	}

	function getPrescriptionByCode($code, $getFull = FALSE, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_regimens WHERE group_code='" . $code . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pres = $this->getPrescription($row['id'], $getFull, $pdo);
			} else {
				$pres = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pres = null;
		}
		return $pres;
	}

	function getPatientPrescriptionByCode($code, $getFull = FALSE, $pdo = null)
	{
		$pres = new Prescription();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_regimens WHERE group_code='" . $code . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pres->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, TRUE);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
				}
				$pres->setPatient($pat);
				$pres->setWhen(date("c", strtotime($row['when'])));
			} else {
				$pres = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$pres = null;
		}
		return $pres;
	}

	function getRefillPrescriptionByCode($code, $getFull = FALSE, $pdo = null)
	{
		$pres = new Prescription();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_regimens WHERE group_code='" . $code . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pres->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$req = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$req = new StaffDirectory($row['requested_by']);
					$hosp = new Clinic($row['hospid']);
				}
				$pres->setExternal((bool)$row['external']);
				$pres->setPatient($pat);
				$pres->setWhen(date("c", strtotime($row['when'])));
				$pres->setCode($row['group_code']);
				$pres->setRequestedBy($req);
				$pres->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				$pres->setInPatient(($row['in_patient_id'] == null) ? null : new InPatient($row['in_patient_id']));
				$pres->setNote($row['note']);
				$pres->setHospital($hosp);

				$pres->setData((new PrescriptionDataDAO())->getRefillPrescriptionDataByCode($row['group_code'], TRUE, $pdo));
			} else {
				$pres = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pres = null;
		}
		return $pres;
	}

	
	function getRefillablePrescriptionStatus($ref_id, $pdo = null)
	{
		$today = date('y-m-d');
		$pres = new Prescription();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_regimens_data WHERE id='$ref_id'  AND refill_date <= '". $today . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$pres->setCode($row['group_code']);
				$pres->setRefillDate($row['refill_date']);
				$pres->setRefillNumber($row['refill_number']);
			}else{
				$pres = null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			$pres = null;
		}
   return $pres;
	}

	function getPrescriptionByAdmission($aid, $getFull = FALSE, $pdo = null)
	{
		$pres = new Prescription();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_regimens WHERE in_patient_id='" . $aid . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pres->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$req = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$req = new StaffDirectory($row['requested_by']);
					$hosp = new Clinic($row['hospid']);
				}
				$pres->setExternal((bool)$row['external']);
				$pres->setPatient($pat);
				$pres->setWhen($row['when']);
				$pres->setCode($row['group_code']);
				$pres->setRequestedBy($req);
				$pres->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				$pres->setInPatient((new InPatientDAO())->getInPatient($row['in_patient_id'], false, $pdo));
				//(new InPatientDAO())->getInPatient($row['in_patient_id'], false, $pdo)
				$pres->setNote($row['note']);
				$pres->setHospital($hosp);
				$pres->setData((new PrescriptionDataDAO())->getPrescriptionDataByCode($row['group_code'], TRUE, $pdo));
			} else {
				$pres = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pres = null;
		}
		return $pres;
	}

	function getPrescriptions($getFull = FALSE, $pdo = null)
	{
		$press = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_regimens";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pres = new Prescription();
				$pres->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$req = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$req = new StaffDirectory($row['requested_by']);
					$hosp = new Clinic($row['hospid']);
				}
				$pres->setExternal((bool)$row['external']);
				$pres->setPatient($pat);
				$pres->setWhen($row['when']);
				$pres->setCode($row['group_code']);
				$pres->setRequestedBy($req);
				$pres->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				$pres->setInPatient((new InPatientDAO())->getInPatient($row['in_patient_id'], false, $pdo));
				$pres->setNote($row['note']);
				$pres->setHospital($hosp);
				$pres->setData((new PrescriptionDataDAO())->getPrescriptionDataByCode($row['group_code'], TRUE, $pdo));
				$press[] = $pres;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$press = [];
		}
		return $press;
	}

	function getPrescriptionsByDateRange($startdate = null, $enddate = null, $getFull = FALSE, $pdo = null)
	{
		$press = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$startdate = '2015-01-01';
			$enddate = date('Y-m-d H:i:s');
			$sql = "SELECT * FROM patient_regimens WHERE DATE(`when`) between DATE('$startdate') and DATE('$enddate')";
//            return $sql;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pres = new Prescription();
				$pres->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$req = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$req = new StaffDirectory($row['requested_by']);
					$hosp = new Clinic($row['hospid']);
				}
				$pres->setExternal((bool)$row['external']);
				$pres->setPatient($pat);
				$pres->setWhen($row['when']);
				$pres->setCode($row['group_code']);
				$pres->setRequestedBy($req);
				$pres->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				$pres->setInPatient((new InPatientDAO())->getInPatient($row['in_patient_id'], false, $pdo));
				$pres->setNote($row['note']);
				$pres->setHospital($hosp);
				$pres->setData((new PrescriptionDataDAO())->getPrescriptionDataByCode($row['group_code'], TRUE, $pdo));
				$press[] = $pres;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$press = [];
		}
		return $press;
	}

	function getOpenPrescriptions($page, $pageSize, $pharmacy = null, $getFull = FALSE, $patientId = null, $is_Admitted=null, $pdo = null)
	{
		$filter = ($pharmacy != null ? " AND r.service_centre_id=$pharmacy" : "");
		$extraFilter = "AND";
		@session_start();
		$patient = (isset($_SESSION['pid']) ? $_SESSION['pid'] : (!is_null($patientId) ? $patientId : null));
		if ($patient !== null) {
			$extraFilter = "AND r.patient_id = " . $patient . " AND ";
		}
		
		$isAdmittedFilter = "";
		if ($is_Admitted != null){
			$isAdmittedFilter = " AND IS_ADMITTED(pd.patient_ID)";
			
		}
		
		$sql = "SELECT r.* FROM patient_regimens r LEFT JOIN patient_regimens_data d ON r.group_code=d.group_code LEFT JOIN patient_demograph pd ON pd.patient_ID=r.patient_id WHERE pd.active IS TRUE $extraFilter d.status IN ('open') $filter$isAdmittedFilter GROUP BY r.group_code ORDER BY r.when DESC";

//		$sql = "SELECT d.* FROM patient_regimens_data d LEFT JOIN patient_regimens r ON r.group_code=d.group_code WHERE d.status IN ('open') ". ($pharmacy != NULL? " AND r.service_centre_id=$pharmacy":"")." GROUP BY d.group_code";
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
		$press = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pres = new Prescription($row['id']);

				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$req = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$req = new StaffDirectory($row['requested_by']);
					$hosp = new Clinic($row['hospid']);
				}
				$pres->setExternal((bool)$row['external']);
				$pres->setPatient($pat);
				$pres->setWhen(date("c", strtotime($row['when'])));
				$pres->setCode($row['group_code']);
				$pres->setRequestedBy($req);
				$pres->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));

				$pres->setInPatient((new InPatientDAO())->getInPatient($row['in_patient_id'], false, $pdo));
				$pres->setNote($row['note']);
				//$pres->setHospital($hosp);
				$pres->setData((new PrescriptionDataDAO())->getPrescriptionDataByCode($row['group_code'], TRUE, $pdo));
				$press[] = $pres;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$press = [];
		}
		$results = (object)null;
		$results->data = $press;
		$results->total = $total;
		$results->page = $page;
		unset($_SESSION['pid']);
		return $results;
	}

	function getOpenPrescriptionsByDateRange($startdate = null, $enddate = null, $getFull = FALSE, $pdo = null)
	{
		$press = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$startdate = ($startdate === null || $startdate == '') ? date('Y-m-d H:i:s') : $startdate;
			$enddate = ($enddate === null || $enddate == '') ? date('Y-m-d H:i:s') : $enddate;
			$sql = "SELECT r.* FROM patient_regimens r LEFT JOIN patient_regimens_data d ON r.group_code=d.group_code WHERE d.status IN ('open') AND DATE(r.when) between DATE('$startdate') and DATE('$enddate') GROUP BY r.group_code ORDER BY r.when ASC";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pres = new Prescription();
				$pres->setId($row['id']);

				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatientMedicalMin($row['patient_id'], $pdo);
					$req = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$req = new StaffDirectory($row['requested_by']);
					$hosp = new Clinic($row['hospid']);
				}
				$pres->setExternal((bool)$row['external']);
				$pres->setPatient($pat);
				$pres->setWhen(date("c", strtotime($row['when'])));
				$pres->setCode($row['group_code']);
				$pres->setRequestedBy($req);
				$pres->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));

				$pres->setInPatient((new InPatientDAO())->getInPatient($row['in_patient_id'], false, $pdo));
				$pres->setNote($row['note']);
				$pres->setHospital($hosp);
				$pres->setData((new PrescriptionDataDAO())->getPrescriptionDataByCode($row['group_code'], TRUE, $pdo));
				$press[] = $pres;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$press = [];
		}
		return $press;
	}

	function getFilledPrescriptions($page, $pageSize, $pharmacy, $getFull = FALSE, $patient=null, $is_Admitted=null, $pdo = null)
	{
		$filter = ($pharmacy != null ? " AND r.service_centre_id=$pharmacy" : "");
		$patientId = $patient != null ? ' AND r.patient_id='.$patient : '';
		
		$isAdmittedFilter1 = "";
		$isAdmittedFilter2 = "";
		if ($is_Admitted != null){
			$isAdmittedFilter1 = " LEFT JOIN patient_demograph p ON r.patient_id=p.patient_ID";
			$isAdmittedFilter2 = "AND IS_ADMITTED(p.patient_ID)";
			
		}
		
		$sql = "SELECT r.* FROM patient_regimens_data d LEFT JOIN patient_regimens r ON d.group_code=r.group_code $isAdmittedFilter1 WHERE d.status IN ('filled') $filter$patientId$isAdmittedFilter2 GROUP BY d.group_code";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			errorLog($e);
			error_log("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$press = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " ORDER BY r.when DESC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pres = new Prescription();
				$pres->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$req = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$req = new StaffDirectory($row['requested_by']);
					$hosp = new Clinic($row['hospid']);
				}
				$pres->setExternal((bool)$row['external']);
				$pres->setPatient($pat);
				$pres->setWhen(date("c", strtotime($row['when'])));
				$pres->setCode($row['group_code']);
				$pres->setRequestedBy($req);
				$pres->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				$pres->setNote($row['note']);
				$pres->setHospital($hosp);
				$pres->setData((new PrescriptionDataDAO())->getPrescriptionDataByCode($row['group_code'], TRUE, $pdo));
				$press[] = $pres;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$press = [];
		}
		$results = (object)null;
		$results->data = $press;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function getCompletedPrescriptionsByDateRange($page, $pageSize, $pharmacy, $getFull = FALSE, $startdate = null, $enddate = null, $pdo = null)
	{
		$filter = ($pharmacy != null ? " AND r.service_centre_id=$pharmacy" : "");
		$startdate = ($startdate === null || $startdate == '') ? date('Y-m-d H:i:s') : $startdate;
		$enddate = ($enddate === null || $enddate == '') ? date('Y-m-d H:i:s') : $enddate;
		$sql = "SELECT * FROM patient_regimens_data d LEFT JOIN patient_regimens r ON d.group_code=r.group_code WHERE d.status IN ('completed') AND DATE(r.when) BETWEEN DATE('$startdate') and DATE('$enddate') $filter GROUP BY d.group_code";
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
		$press = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT r.* FROM patient_regimens r LEFT JOIN patient_regimens_data d ON r.group_code=d.group_code WHERE d.status IN ('completed') AND DATE(r.when) between DATE('$startdate') and DATE('$enddate') $filter ORDER BY r.when ASC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pres = new Prescription();
				$pres->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatientMedicalMin($row['patient_id'], $pdo);
					$req = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$req = new StaffDirectory($row['requested_by']);
					$hosp = new Clinic($row['hospid']);
				}
				$pres->setExternal((bool)$row['external']);
				$pres->setPatient($pat);
				$pres->setWhen(date("c", strtotime($row['when'])));
				$pres->setCode($row['group_code']);
				$pres->setRequestedBy($req);
				$pres->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				$pres->setNote($row['note']);
				$pres->setHospital($hosp);
				$pres->setData((new PrescriptionDataDAO())->getPrescriptionDataByCode($row['group_code'], TRUE, $pdo));
				$press[] = $pres;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$press = [];
		}
		$results = (object)null;
		$results->data = $press;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function findPrescriptions($filter, $start = null, $stop = null, $page, $pageSize, $getFull = FALSE, $patientId = null, $pdo = null)
	{
		$filter = escape($filter);

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
		$filter1 = !is_blank($filter) ? ' AND r.group_code = "' . $filter . '"' : '';
		$filter2 = !is_blank($patientId) ? " AND r.patient_id=" . escape($patientId) : '';
		$sql = "SELECT r.* FROM patient_regimens r LEFT JOIN patient_regimens_data d ON r.group_code=d.group_code LEFT JOIN patient_demograph dm ON r.patient_id = dm.patient_ID WHERE DATE(r.when) BETWEEN '$dateStart' AND '$dateStop'{$filter2}{$filter1} GROUP BY r.group_code";

		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			errorLog($e);
			error_log("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$press = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " ORDER BY r.when DESC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pres = new Prescription();
				$pres->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$req = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$req = new StaffDirectory($row['requested_by']);
					$hosp = new Clinic($row['hospid']);
				}
				$pres->setExternal((bool)$row['external']);
				$pres->setPatient($pat);
				$pres->setWhen(date("c", strtotime($row['when'])));
				$pres->setCode($row['group_code']);
				$pres->setRequestedBy($req);
				$pres->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				$pres->setInPatient((new InPatientDAO())->getInPatient($row['in_patient_id'], false, $pdo));
				$pres->setNote($row['note']);
				$pres->setHospital($hosp);
				$pres->setData((new PrescriptionDataDAO())->getPrescriptionDataByCode($row['group_code'], TRUE, $pdo));
				$press[] = $pres;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$press = [];
		}
		$results = (object)null;
		$results->data = $press;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function getPatientPrescriptions($pid, $page = 0, $pageSize = 10, $getFull = FALSE, $pdo = null)
	{
		//$sql = "SELECT pr.*, sd.username FROM patient_regimens pr LEFT JOIN patient_regimens_data pd ON pr.group_code=pd.group_code LEFT JOIN staff_directory sd ON sd.staffId=pr.requested_by WHERE pr.patient_id=" . $pid . " ORDER BY `when` DESC";
		$sql = "SELECT pr.note, pr.when, pr.external, pr.service_centre_id, pd.*, sd.username, d.name AS drug_name, g.name AS generic, g.weight FROM patient_regimens pr LEFT JOIN patient_regimens_data pd ON pr.group_code=pd.group_code LEFT JOIN staff_directory sd ON sd.staffId=pr.requested_by LEFT JOIN drugs d ON d.id=pd.drug_id LEFT JOIN drug_generics g ON g.id=pd.drug_generic_id WHERE pd.group_code IS NOT NULL AND pr.patient_id=" . $pid . " ORDER BY `when` DESC";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			error_log("slq:;".json_encode($sql));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$press = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				/*$pres = new Prescription();
				$pres->setId($row['id']);
				if ($getFull) {
						$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
						$req = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
						$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
						$pat = new PatientDemograph();
						$pat->setId($row['patient_id']);
						$req = new StaffDirectory();
						$req->setId($row['requested_by']);
						$hosp = new Clinic();
						$hosp->setId($row['hospid']);
				}
				$pres->setExternal((bool)$row['external']);
				$pres->setPatient($pat);
				$pres->setWhen(date("c", strtotime($row['when'])));
				$pres->setCode($row['group_code']);
				$pres->setRequestedBy($req);
				$pres->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				$pres->setInPatient((new InPatientDAO())->getInPatient($row['in_patient_id'], false, $pdo));
				$pres->setNote($row['note']);
				$pres->setHospital($hosp);
				$pres->setData((new PrescriptionDataDAO())->getPrescriptionDataByCode($row['group_code'], TRUE, $pdo));*/
//                $press[] = $pres;
				$press[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$press = [];
		}
		$results = (object)null;
		$results->data = $press;
		$results->total = $total;
		$results->page = $page;
		return $results;
	}

	function getUncomputedInPatientsPrescription($getFull = TRUE, $pdo = null)
	{
		$press = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT pr.* FROM patient_regimens_data prd LEFT JOIN patient_regimens pr ON pr.group_code=prd.group_code LEFT JOIN in_patient ip ON ip.id=pr.in_patient_id WHERE prd.status = 'completed' AND ip.bill_status = 'Uncomputed' GROUP BY prd.drug_id #and ip.date_discharged is not null";

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pres = new Prescription();
				$pres->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$req = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph();
					$pat->setId($row['patient_id']);
					$req = new StaffDirectory();
					$req->setId($row['requested_by']);
					$hosp = new Clinic();
					$hosp->setId($row['hospid']);
				}
				$pres->setExternal((bool)$row['external']);
				$pres->setPatient($pat);
				$pres->setWhen(date("c", strtotime($row['when'])));
				$pres->setCode($row['group_code']);
				$pres->setRequestedBy($req);
				$pres->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				$pres->setInPatient((new InPatientDAO())->getInPatient($row['in_patient_id'], false, $pdo));
				$pres->setNote($row['note']);
				$pres->setHospital($hosp);
//                $pres->setData((new PrescriptionDataDAO())->getUncomptedPrescriptionDataByCode($row['group_code'], TRUE, $pdo));
				$pres->setData((new PrescriptionDataDAO())->getPrescriptionDataByCode($row['group_code'], TRUE, $pdo));
				$press[] = $pres;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$press = [];
		}
		return $press;
	}

	public function getEncounterPrescriptions($id, $pdo)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_regimens WHERE encounter_id=" . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->getPrescription($row['id'], FALSE, $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$data = [];
		}
		return $data;
	}

}
