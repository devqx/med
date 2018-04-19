<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PatientInRoomDAO
 *
 * @author pauldic
 */
class PatientInRoomDAO {
    private $conn = null;
    
    
    function __construct() {      
        try {                
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PatientInRoom.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/Patient.php';
            $this->conn=new MyDBConnector();   
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        } 
    }
    
    function getPatientsInRoom(){
        $patientsInRoom=array();
        try {                
            $pdo=$this->conn->getPDO();
            $sql='SELECT pir.*, concat(p.lname, " ", p.fname, " ", mname) as patName FROM patient_in_room pir, patient_demograph p WHERE pir.patientID=p.patient_id';
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $i=0;
//            error_log(print_r($sql, TRUE));
            
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $patInRoom=new PatientInRoom();
                    $pat=new Patient();
                        $pat->setPid($row["patientID"]);
                        $pat->setPNames($row["patName"]);
                    $patInRoom->setPatient($pat);
                    $patInRoom->setId($row["roomID"]);
                    $patInRoom->setQueueFor($row["queue_for"]);
                    $patInRoom->setTimeIn($row["time_in"]);
                $patientsInRoom[]=$patInRoom;
            }
            $stmt = null;
        }catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
        return $patientsInRoom;
        
    }

//    function getPatientActiveRound($pid){
//        $pr=new PatientRound();
//        try {                
//            $pdo=$this->conn->getDB();
//            $sql = "SELECT *,  FROM_UNIXTIME(next_round_time) as dueTime FROM patient_rounds WHERE (next_round_time< (UNIX_TIMESTAMP()+((round_count+1)*_interval*3600))) AND patient_id=".$pid." AND status=1 ORDER BY ID DESC LIMIT 1";
////            $sql="SELECT * FROM patient_rounds WHERE patient_id=".$pid." AND status=1 ORDER BY ID DESC LIMIT 1";
//            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
//            $stmt->execute();
//            $i=0;
//            $dTime="";
//            error_log(print_r($sql, TRUE));
//            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
//                $dTime=$row['dueTime'];
//            }
//            $stmt = null;
//        }catch(PDOException $e) {
//            echo 'ERROR: ' . $e->getMessage();
//        }
//        if($dTime==NULL){
//            return ["none", $dTime];            
//        }  else {
//            return ["block", $dTime];
//        }
//    }
}

?>
