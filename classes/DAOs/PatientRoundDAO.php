<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PatientRoundDAO
 *
 * @author pauldic
 */
class PatientRoundDAO {
    private $conn = null;
    
    
    function __construct() {      
        try {                
            include $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            include $_SERVER ['DOCUMENT_ROOT'] .'/classes/PatientRound.php';
            require $_SERVER ['DOCUMENT_ROOT'] .'/classes/SubscribedChannel.php';
            require $_SERVER ['DOCUMENT_ROOT'] .'/classes/Channel.php';
            $this->conn=new MyDBConnector();   
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        } 
    }
    
    function addPatientRound($pid, $admiID, $interval){
        try {                
            $pdo=$this->conn->getPDO();
            $sql="INSERT INTO patient_rounds (patient_id, _interval, admission_id, next_round_time) VALUES (".$pid.", ".$interval.", ".$admiID.", UNIX_TIMESTAMP() + (3600*".$interval."))";
//            error_log(print_r("....3....".$sql.".........", TRUE));
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $stmt = null;
        }catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
    }

    function getPatientActiveRound($pid){
        $pr=new PatientRound();
        try {                
            $pdo=$this->conn->getPDO();
            $sql = "SELECT *,  FROM_UNIXTIME(next_round_time) as dueTime FROM patient_rounds WHERE (next_round_time< (UNIX_TIMESTAMP()+((round_count+1)*_interval*3600))) AND patient_id=".$pid." AND status=1 ORDER BY ID DESC LIMIT 1";
//            $sql="SELECT * FROM patient_rounds WHERE patient_id=".$pid." AND status=1 ORDER BY ID DESC LIMIT 1";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $i=0;
            $dTime="";
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $dTime=$row['dueTime'];
            }
            $stmt = null;
        }catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
        if($dTime==NULL){
            return ["none", $dTime];            
        }  else {
            return ["block", $dTime];
        }
    }

}

?>
