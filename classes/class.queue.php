<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/5/14
 * Time: 10:45 AM
 */

class Queue {
    function __construct(){
        if(!isset($_SESSION)){@session_start();}
    }

    function getQueuedPatients($filter=NULL){
        require_once $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
        if (isset($database_dbconnection, $dbconnection)) {
            mysql_select_db ( $database_dbconnection, $dbconnection );
        }
        $sql = "SELECT * FROM patient_queue /*WHERE DATE(entry_time) = DATE(NOW())*/ ORDER BY entry_time";
        // a patient can be in multiple queues at the same time

        $chk = mysql_query ( $sql );
        $data = [];
        require_once 'class.patient.php';
        $pat = new Manager();
        while ($row_data = mysql_fetch_assoc ( $chk )){
            $p = $pat->getPatientInfo($row_data['patient_id']);
            $row_data['name'] = $p['name'];
            $data[] = $row_data;
        }
        return json_encode($data);
    }

    function getQueueItem($id){
        require_once $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
        if (isset($database_dbconnection, $dbconnection)) {
            mysql_select_db ( $database_dbconnection, $dbconnection );
        }
        $sql = "SELECT * FROM patient_queue WHERE id = $id";
//        error_log($sql);
        $chk = mysql_query ( $sql );
        while ($row_data = mysql_fetch_assoc ( $chk )){
            return json_encode($row_data);
        }
    }

    function removeQueuedPatient($pid, $qid){
        require_once $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
        if (isset($database_dbconnection, $dbconnection)) {
            mysql_select_db ( $database_dbconnection, $dbconnection );
        }
        $sql = "DELETE FROM patient_queue WHERE patient_id = '$pid' AND id = '$qid'";
        $chk = mysql_query ( $sql );
        return $chk;
    }
} 