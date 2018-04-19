<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SubscribedDoctorDAO
 *
 * @author pauldic
 */
class SubscribedDoctorDAO {
    private $conn = null;
    
    function __construct() {      
        try {                
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/ExamRoom.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/StaffDirectory.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/SubscribedDoctor.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/ExamRoomDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/StaffDirectoryDAO.php';
            $this->conn=new MyDBConnector();   
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        } 
    }

    function getSubscribedDoctors($getFull=FALSE, $pdo=NULL){
        $sds=array();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM doctors_subscribed";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $sds[] = $this->getSubscribedDoctor($row['staffID'], $getFull, $pdo);
            }
            $stmt = NULL;
        }catch(PDOException $e) {
            $sds = [];
        }
        return $sds;
    }

//    function addSubscribedDoctor($ds, $pdo=NULL){
//        try {
//            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
//            $sql="INSERT INTO doctors_subscribed (roomID, staffID, specialization) VALUES "
//                    . "(".$ds->getRoom->getId().", '".$ds->getStaff()->getId()."', '".$ds->getSpecialization()."', '".$ds->getOnlineStatus()."')";
//            error_log($sql);
//            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
//            $stmt->execute();
//            $stmt = null;
//        }catch(PDOException $e) {
//            $ds=NULL;
//        }
//        return $ds;
//    }

    function getSubscribedDoctor($sid, $getFull=FALSE, $pdo=NULL){
        $sd=new SubscribedDoctor();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM doctors_subscribed WHERE staffID='".$sid."'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                if($getFull){
                    $er=(new ExamRoomDAO())->getExamRoom($row['roomID'], $pdo);
                    $staff = (new StaffDirectoryDAO())->getStaff($row['staffID'], $getFull, $pdo);
                    $spe = (new StaffSpecializationDAO())->get($row['specialization_id'], $pdo);
                }else{
                    $er=new ExamRoom($row['roomID']);
                    $staff=new StaffDirectory($row['staffID']);
                    $spe = new StaffSpecialization($row['specialization_id']);
                }
                $sd->setRoom($er);
                $sd->getStaff($staff);
                $sd->setSpecialization( $spe);
                $sd->setTime($row['timestamp']);
            }
            $stmt = NULL;
        }catch(PDOException $e) {
            $sd=NULL;
        }
        return $sd;
    }

    function getSubscriptionsByRoom($room, $pdo=NULL){
        $sds = array();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM doctors_subscribed WHERE roomID = '".$room->getId()."'";
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $sds[] = $this->getSubscribedDoctor($row['staffID'], TRUE, $pdo);
            }
        } catch(PDOException $e){
            $sds = [];
        }
        return $sds;
    }

    function getSubscriptionsBySpecialty($sid, $pdo=NULL){
        $sds = array();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM doctors_subscribed WHERE specialization_id = '".$sid."'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $sds[] = $this->getSubscribedDoctor($row['staffID'], TRUE, $pdo);
            }
        } catch(PDOException $e){
            $sds = [];
        }
        return $sds;
    }

//    function getSubscribedRoom($sid, $getFull=FALSE, $pdo=NULL){
//        try {
//            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
//            $sql = "SELECT roomID FROM doctors_subscribed WHERE staffID='".$sid."'";
////            error_log($sql);
//            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
//            $stmt->execute();
//            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
//                if($getFull){
//                    $er=(new ExamRoomDAO())->getExamRoom($row['roomID'], $pdo);
//                }else{
//                    $er=new ExamRoom();
//                        $er->setId($row['roomID']);
//                }
//            }
//            $stmt = NULL;
//        }catch(PDOException $e) {
//            $er=NULL;
//        }
//        return $er;
//    }
            
//    function updateOnlineStatus($sd, $pdo=NULL){
//        $status=FALSE;
//        try {
//            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
//            $sql="UPDATE doctors_subscribed SET online_status='".$sd->getOnlineStatus()."' WHERE staffID='".$sd->getStaff()->getId()."'";
//            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
//            $stmt->execute();
//            $status=TRUE;
//            $stmt = NULL;
//        }catch(PDOException $e) {
//            $status=FALSE;
//        }
//        return $status;
//    }
    
    
//    function deleteSubscribedDoctor($sid, $pdo=NULL){
//        $status=FALSE;
//        try {
//            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
//            $pdo->beginTransaction();
//                $sql="DELETE FROM doctors_subscribed WHERE staffID='".$sid."'";
//                $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
//                $stmt->execute();
//
//                $status=(new ExamRoomDAO())->setAvailable($room, $pdo);
//
//            $pdo->commit();
//            $stmt = NULL;
//        }catch(PDOException $e) {
//            $status=FALSE;
//        }
//        return $status;
//    }
    
    

}
