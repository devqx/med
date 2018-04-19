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
class OphthalmologyResultDAO {

    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/protect.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/OphthalmologyResult.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientOphthalmology.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/OphthalmologyTemplate.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Alert.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyResultDataDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyTemplateDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientOphthalmologyDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AlertDAO.php';
            if (!isset($_SESSION))
                session_start();
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function addResult($res, $pdo = NULL) {
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $canCommit = TRUE;
            try {
                $pdo->beginTransaction();
            } catch (PDOException $e) {
                $canCommit=FALSE;
            }
            $sql = "INSERT INTO ophthalmology_result (ophthalmology_template_id, patient_ophthalmology_id)  VALUES ('" . $res->getOphthalmologyTemplate()->getId() . "', '" . $res->getPatientOphthalmology()->getId() . "')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            $data=$res->getData();


            foreach($data as $datum){
                $datum->setOphthalmologyResult(new OphthalmologyResult($pdo->lastInsertId()));
            }

            if (count((new OphthalmologyResultDataDAO())->add($data, $pdo)) !== count($data)) {
                error_log("what is happening?");
                $pdo->rollBack();
            } else {
                if ($canCommit) {
                    $pdo->commit();
                }
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            if ($pdo != null) {
                $pdo->rollBack();
            }
            errorLog($e);
            $stmt = NULL;
            $res = null;
        }
        return $res;
    }

    function get($ophResId, $getFull = FALSE, $pdo = NULL) {
        $result = new OphthalmologyResult();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_result WHERE id=" . $ophResId;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                if ($getFull) {
                    $req = (new PatientOphthalmologyDAO())->get($row['patient_ophthalmology_id'], $pdo);
                } else {
                    $req = new PatientOphthalmology($row['patient_ophthalmology_id']);
                }
                $result->setId($row['id']);
                $result->setOphthalmologyTemplate((new OphthalmologyTemplateDAO())->getTemplate($row['ophthalmology_template_id'], $pdo));    //Obj
                $result->setPatientOphthalmology($req);    //Obj
                $result->setAbnormalValue((bool) $row['abnormal_ophthalmology_value']);
                $result->setApproved(boolval($row['approved']));
                $result->setApprovedBy((new StaffDirectoryDAO())->getStaff($row['approved_by'], FALSE, $pdo));
                $result->setApprovedDate($row['approved_date']);
                $result->setData((new OphthalmologyResultDataDAO())->getResultData($row['id'], FALSE, $pdo));
                
            } else {
                $result = NULL;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $result = NULL;
        }
        return $result;
    }

    function getResults($getFull = FALSE, $pdo = NULL) {
        $results = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_result";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $result = new OphthalmologyResult();
                if ($getFull) {
                    $req = (new PatientOphthalmologyDAO())->get($row['patient_ophthalmology_id'], $pdo);
                } else {
                    $req = new PatientOphthalmology($row['patient_ophthalmology_id']);
                }
                $result->setId($row['id']);
                $result->setOphthalmologyTemplate((new OphthalmologyTemplateDAO())->getTemplate($row['ophthalmology_template_id'], $pdo));    //Obj
                $result->setPatientOphthalmology($req);    //Obj
                $result->setAbnormalValue((bool) $row['abnormal_ophthalmology_value']);
                $result->setApproved($row['approved']);
                $result->setApprovedBy($row['approved_by']);
                $result->setApprovedDate($row['approved_date']);
                $result->setData((new OphthalmologyResultDataDAO())->getResultData($row['id'], FALSE, $pdo));
                $results[] = $result;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $results = array();
        }
        return $results;
    }

    /**
     * @param $page
     * @param $pageSize
     * @param null $ophthalmology_centre
     * @param bool|False $getFull
     * @param null $pdo
     * @return object
     */
    function getUnApprovedResult($page, $pageSize, $ophthalmology_centre = NULL, $getFull=False, $pdo = NULL) {
        $filter = ($ophthalmology_centre != NULL ? " AND lq.service_centre_id=$ophthalmology_centre":"");
        $sql = "SELECT lr.* FROM ophthalmology_result lr LEFT JOIN patient_ophthalmology pl ON lr.patient_ophthalmology_id=pl.id LEFT JOIN ophthalmology_requests lq ON pl.ophthalmology_group_code=lq.group_code WHERE lr.approved IS FALSE $filter";
        $total = 0;
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $total = $stmt->rowCount();
        } catch (PDOException $e){
            error_log("ERROR: Failed to return total number of records");
        }

        $page = ($page > 0) ? $page : 0;
        $offset = ($page > 0) ? $pageSize * $page : 0;
        $results = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql ="SELECT lr.* FROM ophthalmology_result lr LEFT JOIN patient_ophthalmology pl ON lr.patient_ophthalmology_id=pl.id LEFT JOIN ophthalmology_requests lq ON pl.ophthalmology_group_code=lq.group_code WHERE lr.approved IS FALSE $filter LIMIT $offset, $pageSize";
//            $sql = "SELECT * FROM lab_result WHERE approved IS FALSE  LIMIT $offset, $pageSize";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $result = new OphthalmologyResult();
                if ($getFull) {
                    $req = (new PatientOphthalmologyDAO())->get($row['patient_ophthalmology_id'], $pdo);
                } else {
                    $req = new PatientOphthalmology($row['patient_ophthalmology_id']);
                }
                $result->setId($row['id']);
                $result->setOphthalmologyTemplate((new OphthalmologyTemplateDAO())->getTemplate($row['ophthalmology_template_id'], $pdo));    //Obj
                $result->setPatientOphthalmology($req);    //Obj
                $result->setAbnormalValue((bool) $row['abnormal_ophthalmology_value']);
                $result->setApproved($row['approved']);
                $result->setApprovedBy($row['approved_by']);
                $result->setApprovedDate($row['approved_date']);
                $result->setData((new OphthalmologyResultDataDAO())->getResultData($row['id'], FALSE, $pdo));
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

    public function approveResult($result, $pdo = NULL) {
        if (!isset($_SESSION['staffID']))
            return NULL;
        $protect = new Protect();
        $approver = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
        if (!$approver->hasRole($protect->lab_super))
            return NULL;
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "UPDATE ophthalmology_result SET approved = TRUE, approved_date = NOW(), approved_by = '" . $result->getApprovedBy()->getId() . "' WHERE id=".$result->getId();
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
    public function rejectResult($result, $pdo = NULL) {
        if (!isset($_SESSION['staffID']))
            return NULL;
        $protect = new Protect();
        $approver = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
        if (!$approver->hasRole($protect->lab_super))
            return NULL;
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "DELETE FROM ophthalmology_result WHERE id=".$result->getId();
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

    public function setAbnormalValue($result, $pdo = NULL){
//        $result = new OphthalmologyResult();

        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "UPDATE ophthalmology_result SET abnormal_ophthalmology_value = ".$result->getAbnormalValue()." WHERE id=".$result->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                if($result->getAbnormalValue() == 1){
                    $alert = new Alert();
                    $alert->setMessage("ophthalmology (".$result->getPatientOphthalmology()->getOphthalmologyGroup()->getGroupName().": ".$result->getPatientOphthalmology()->getOphthalmology()->getName().") marked as abnormal");
                    $alert->setType($result->getPatientOphthalmology()->getOphthalmology()->getName());
                    $alert->setPatient( (new PatientDemographDAO())->getPatient($result->getPatientOphthalmology()->getOphthalmologyGroup()->getPatient()->getId(), FALSE, NULL, NULL) );

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
