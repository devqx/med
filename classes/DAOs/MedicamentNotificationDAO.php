<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MedicamentNotificationDAO
 *
 * @author pauldic
 */
class MedicamentNotificationDAO {
    private $conn = null;
    
    function __construct() {
        try {                
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/MedicamentNotification.php';
            $this->conn=new MyDBConnector();   
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function getNotifications($mid, $c=NULL){
        $notifications=array();
        try { 
            $pdo=NULL;
            if($c==NULL){
                $pdo=$this->conn->getPDO();
            }else{
                $pdo=$c->getPDO();
            }          
            $sql="SELECT * FROM medicament_notification WHERE medicament_id=".$mid. " ORDER BY status ASC, due_time DESC";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $mNotification=new MedicamentNotification();
                    $mNotification->setId($row["id"]);
                    $mNotification->setAdmissionId($row["admission_id"]);
                    $mNotification->setMedicamentId($mid);
                    $mNotification->setDueTime($row["due_time"]);
                    $mNotification->setAttendedTime($row["attended_time"]);
                    $mNotification->setStatus($row["status"]);
                $notifications[]=$mNotification;
            }
            $stmt = null;
        }catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
            $notifications=array();
        }
        return $notifications;
    }
    
    function despenseDrug($mNId){
        $status=TRUE;
        try {                
            $pdo=$this->conn->getPDO();
            $sql = "UPDATE medicament_notification SET status='Done', attended_time=NOW() WHERE id=".$mNId;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
//            error_log(print_r($sql, TRUE));
            
            $stmt = null;
        }catch(PDOException $e) {
            $status=FALSE;
        }
        return $status;
    }
    
}

?>
