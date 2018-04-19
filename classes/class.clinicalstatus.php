<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/28/14
 * Time: 11:01 AM
 */

class ClinicalStatus {
    function savePatientClinicalStatus($data){
        foreach ($data as $key => $value) {
            if(empty($value)){
                exit("error:Invalid data received at $key");
            }
        }
        $data = (object)$data;
        require_once $_SERVER['DOCUMENT_ROOT'] .'/Connections/dbconnection.php';
        if (isset($database_dbconnection, $dbconnection)) {
            mysql_select_db ( $database_dbconnection, $dbconnection );
        }
        $patient_id = $data->pid;
        $fnctal_status = $data->functional_status_id;
        $who_stage = $data->who_stage;
        $tb_status = $data->tb_status_id;
        $date1 = $data->date1;
        $sql = "INSERT INTO hiv_clinical_status SET check_date = '$date1', functional_status_id = $fnctal_status, patient_id = '$patient_id', tb_status_id = $tb_status, who_stage = $who_stage";
        $chk = mysql_query($sql);
        if($chk){
            return 'ok';
        }
        return "error:Failed to save Data";
    }

    function getPatientClinicalStatuses($pid){
        require_once $_SERVER['DOCUMENT_ROOT'] .'/Connections/dbconnection.php';
        if (isset($database_dbconnection, $dbconnection)) {
            mysql_select_db ( $database_dbconnection, $dbconnection );
        }
        $sql = "SELECT a.check_date, a.who_stage, b.description AS functional_status, c.description AS tb_status FROM hiv_clinical_status a LEFT JOIN functional_statuses b ON a.functional_status_id = b.id LEFT JOIN tb_statuses c ON c.id = a.tb_status_id WHERE a.patient_id = '$pid'";

        $rst = mysql_query($sql);
        $data = array();
        while($row = mysql_fetch_assoc($rst)){
            $data[] = $row;
        }
        return $data;
    }
} 