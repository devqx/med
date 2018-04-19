<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LabResultDAO
 *
 * @author pauldic
 */
class LabResultDAO
{

	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/protect.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabResult.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientLabs.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabTemplate.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Alert.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabResultDataDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabTemplateDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AlertDAO.php';
			if (!isset($_SESSION)) session_start();
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function addLabResult($res, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$canCommit = TRUE;
			try {
				$pdo->beginTransaction();
			} catch (PDOException $e) {
				$canCommit = FALSE;
			}
			$sql = "INSERT INTO lab_result (lab_template_id, patient_lab_id)  VALUES ('" . $res->getLabTemplate()->getId() . "', '" . $res->getPatientLab()->getId() . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$data = $res->getData();
			$data[0]->setLabResult(new LabResult($pdo->lastInsertId()));
			if (count((new LabResultDataDAO())->addLabResultData($data, $pdo)) !== count($data)) {
				$pdo->rollBack();
			} else {
				if ($canCommit) {
					$pdo->commit();
				}
			}
			$stmt = null;
		} catch (PDOException $e) {
			if ($pdo != null) {
				$pdo->rollBack();
			}
			error_log("PDO Exception");
			$stmt = null;
			$res = null;
		}
		return $res;
	}

	function getLabResult($rid, $getFull = FALSE, $pdo = null)
	{
		if(is_blank($rid)) return null;
		$result = new LabResult();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM lab_result WHERE id=$rid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				if ($getFull) {
					$req = (new PatientLabDAO())->getLab($row['patient_lab_id'], $pdo);
				} else {
					$req = new PatientLab($row['patient_lab_id']);
				}
				$result->setId($row['id']);
				//error_log("222....".__LINE__.": ".var_export($pdo->inTransaction(), true));
				$result->setLabTemplate((new LabTemplateDAO())->getLabTemplate($row['lab_template_id'], $pdo));    //Obj
				//error_log("222....".__LINE__.": ".var_export($pdo->inTransaction(), true));
				$result->setPatientLab($req);    //Obj
				$result->setAbnormalValue((bool)$row['abnormal_lab_value']);
				$result->setApproved(boolval($row['approved']));
				$result->setApprovedBy((new StaffDirectoryDAO())->getStaff($row['approved_by'], FALSE, $pdo));
				//error_log("222....".__LINE__.": ".var_export($pdo->inTransaction(), true));
				$result->setApprovedDate($row['approved_date']);
				$result->setData((new LabResultDataDAO())->getLabResultData($row['id'], FALSE, $pdo));
				//error_log("222....".__LINE__.": ".var_export($pdo->inTransaction(), true));
				
			} else {
				$result = null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			$result = null;
		}
		return $result;
	}

	function getLabResults($getFull = FALSE, $pdo = null)
	{
		$results = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM lab_result";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$result = new LabResult();
				if ($getFull) {
					$req = (new PatientLabDAO())->getLab($row['patient_lab_id'], $pdo);
				} else {
					$req = new PatientLab($row['patient_lab_id']);
				}
				$result->setId($row['id']);
				$result->setLabTemplate((new LabTemplateDAO())->getLabTemplate($row['lab_template_id'], $pdo));    //Obj
				$result->setPatientLab($req);    //Obj
				$result->setAbnormalValue((bool)$row['abnormal_lab_value']);
				$result->setApproved($row['approved']);
				$result->setApprovedBy($row['approved_by']);
				$result->setApprovedDate($row['approved_date']);
				$result->setData((new LabResultDataDAO())->getLabResultData($row['id'], FALSE, $pdo));
				$results[] = $result;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$results = array();
		}
		return $results;
	}

	/**
	 * @param $page
	 * @param $pageSize
	 * @param null $lab_centre
	 * @param null $lab_category
	 * @param bool|False $getFull
	 * @param null $pdo
	 * @return object
	 */
	function getUnApprovedLabResult($page, $pageSize, $lab_centre = null, $lab_category = null, $getFull = False, $pdo = null)
	{
		$filter = ($lab_centre != null ? " AND lq.service_centre_id=$lab_centre" : "");
		$cat_filter = ($lab_category != null ? " AND ltc.category_id=$lab_category" : "");
		$sql = "SELECT lr.* FROM lab_result lr LEFT JOIN patient_labs pl ON lr.patient_lab_id=pl.id LEFT JOIN lab_requests lq ON pl.lab_group_id=lq.lab_group_id  LEFT JOIN labtests_config ltc ON ltc.id=pl.test_id WHERE lr.approved IS FALSE $filter$cat_filter";
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
		$results = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$result = new LabResult();
				if ($getFull) {
					$req = (new PatientLabDAO())->getLab($row['patient_lab_id'], $pdo);
				} else {
					$req = new PatientLab($row['patient_lab_id']);
				}
				$result->setId($row['id']);
				$result->setLabTemplate((new LabTemplateDAO())->getLabTemplate($row['lab_template_id'], $pdo));    //Obj
				$result->setPatientLab($req);    //Obj
				$result->setAbnormalValue((bool)$row['abnormal_lab_value']);
				$result->setApproved($row['approved']);
				$result->setApprovedBy($row['approved_by']);
				$result->setApprovedDate($row['approved_date']);
				$result->setData((new LabResultDataDAO())->getLabResultData($row['id'], FALSE, $pdo));
				$results[] = $result;
			}
		} catch (PDOException $e) {
			errorLog($e);
		}
		$data = (object)null;
		$data->data = $results;
		$data->total = $total;
		$data->page = $page;

		return $data;
	}
	/**
	 * @param $page
	 * @param $pageSize
	 * @param null $lab_centre
	 * @param null $lab_category
	 * @param bool|False $getFull
	 * @param null $pdo
	 * @return object
	 */
	function getUnApprovedLabResultSlim($page, $pageSize, $lab_centre = null, $lab_category = null, $getFull = False, $is_Admitted=null,  $pdo = null)
	{
		$filter = ($lab_centre != null ? " AND lq.service_centre_id=$lab_centre" : "");
		$cat_filter = ($lab_category != null ? " AND ltc.category_id=$lab_category" : "");
		$isAdmittedFilter = "";
		if ($is_Admitted != null){
			$isAdmittedFilter = " AND IS_ADMITTED(pd.patient_ID)";
			
		}
		$sql = "SELECT lr.*, pl.lab_group_id, lq.time_entered, ltc.name AS testName, CONCAT_WS(' ', pd.fname, pd.mname, pd.lname) AS patientName, pd.patient_ID AS patientId, IS_ADMITTED(pd.patient_ID) AS is_admitted FROM lab_result lr LEFT JOIN patient_labs pl ON lr.patient_lab_id=pl.id LEFT JOIN lab_requests lq ON pl.lab_group_id=lq.lab_group_id LEFT JOIN labtests_config ltc ON ltc.id=pl.test_id LEFT JOIN patient_demograph pd ON pd.patient_ID=lq.patient_id WHERE lr.approved IS FALSE $filter$cat_filter$isAdmittedFilter";
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
		$results = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$results[] = (object)$row;
				//$result = new LabResult();
				//if ($getFull) {
				//	$req = (new PatientLabDAO())->getLab($row['patient_lab_id'], $pdo);
				//} else {
				//	$req = new PatientLab($row['patient_lab_id']);
				//}
				//$result->setId($row['id']);
				//$result->setLabTemplate((new LabTemplateDAO())->getLabTemplate($row['lab_template_id'], $pdo));    //Obj
				//$result->setPatientLab($req);    //Obj
				//$result->setAbnormalValue((bool)$row['abnormal_lab_value']);
				//$result->setApproved($row['approved']);
				//$result->setApprovedBy($row['approved_by']);
				//$result->setApprovedDate($row['approved_date']);
				//$result->setData((new LabResultDataDAO())->getLabResultData($row['id'], FALSE, $pdo));
				//$results[] = $result;
			}
		} catch (PDOException $e) {
			errorLog($e);
		}
		$data = (object)null;
		$data->data = $results;
		$data->total = $total;
		$data->page = $page;

		return $data;
	}

	public function approveResult($result, $pdo = null)
	{
		if (!isset($_SESSION['staffID'])) return null;
		$protect = new Protect();
		$approver = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
		if (!$approver->hasRole($protect->lab_super)) return null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE lab_result SET approved = TRUE, approved_date = NOW(), approved_by = '" . $result->getApprovedBy()->getId() . "' WHERE id=" . $result->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				return TRUE;
			} else {
				return FALSE;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return FALSE;
		}
	}

	public function disApproveResult($result, $pdo = null)
	{
		if (!isset($_SESSION['staffID'])) return null;
		$protect = new Protect();
		$approver = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
		if (!$approver->hasRole($protect->lab_super_user)) return null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE lab_result SET approved = FALSE, approved_date = NULL, approved_by = NULL WHERE id=" . $result->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				return TRUE;
			} else {
				return FALSE;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return FALSE;
		}
	}

	public function rejectResult($result, $pdo = null)
	{
		if (!isset($_SESSION['staffID'])) return null;
		$protect = new Protect();
		$approver = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
		if (!$approver->hasRole($protect->lab_super)) return null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "DELETE FROM lab_result WHERE id=" . $result->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				return TRUE;
			} else {
				return FALSE;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return FALSE;
		}
	}

	public function setAbnormalValue($result, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE lab_result SET abnormal_lab_value = " . $result->getAbnormalValue() . " WHERE id=" . $result->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				if ($result->getAbnormalValue() == 1) {
					$alert = new Alert();
					$alert->setMessage("Lab Test (" . $result->getPatientLab()->getLabGroup()->getGroupName() . ": " . $result->getPatientLab()->getTest()->getName() . ") marked as abnormal");
					$alert->setType($result->getPatientLab()->getTest()->getName());
					$alert->setPatient((new PatientDemographDAO())->getPatient($result->getPatientLab()->getLabGroup()->getPatient()->getId(), FALSE, null, null));

					@(new AlertDAO())->add($alert);
				}

				return TRUE;
			} else {
				return FALSE;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return FALSE;
		}
	}
}
