<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/3/15
 * Time: 11:18 AM
 */
class PatientProcedureMedicalReportDAO
{
    private $conn = null;
    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PatientProcedureMedicalReport.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/StaffDirectoryDAO.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function getProcedureReports($p_procedure, $pdo=NULL){
        $reports = array();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM patient_procedure_report WHERE patient_procedure_id = ".$p_procedure->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $report = new PatientProcedureMedicalReport($row['id']);
//                $note->setPatientProcedure( (new PatientProcedureDAO())->getProcedure($row['patient_procedure_id'], $pdo) );
                $report->setRequestTime($row['request_time']);
                $report->setCreateUser( (new StaffDirectoryDAO())->getStaff($row['report_user_id'], FALSE, $pdo) );
                $report->setContent($row['content']);

                $reports[] = $report;
            }
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
        return $reports;
    }

    function add($report, $pdo = NULL)
    {
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;

            $patient_procedure_id = $report->getPatientProcedure()->getId();
            $report_user_id = $report->getCreateUser()->getId();
            $content = escape( $report->getContent() );
            $sql = "INSERT INTO patient_procedure_report (patient_procedure_id, request_time, report_user_id, content) VALUES ( $patient_procedure_id, NOW(), $report_user_id, '$content' )";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount() == 1) {
                $report->setId($pdo->lastInsertId());
                return $report;
            }
            return NULL;
        } catch (PDOException $e) {
            errorLog($e);
            return NULL;
        }
    }

    function all($pdo=NULL){
        $reports = array();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM patient_procedure_report";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $report = new PatientProcedureMedicalReport($row['id']);
//                $note->setPatientProcedure( (new PatientProcedureDAO())->getProcedure($row['patient_procedure_id'], $pdo) );
                $report->setRequestTime($row['request_time']);
                $report->setCreateUser( (new StaffDirectoryDAO())->getStaff($row['report_user_id'], FALSE, $pdo) );
                $report->setContent($row['content']);

                $reports[] = $report;
            }
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
        return $reports;
    }

    function get($id, $pdo=NULL){
        if( is_null($id))return NULL;
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM patient_procedure_report WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $report = new PatientProcedureMedicalReport($row['id']);
//                $note->setPatientProcedure( (new PatientProcedureDAO())->getProcedure($row['patient_procedure_id'], $pdo) );
                $report->setRequestTime($row['request_time']);
                $report->setCreateUser( (new StaffDirectoryDAO())->getStaff($row['report_user_id'], FALSE, $pdo) );
                $report->setContent($row['content']);

                return $report;
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }
}