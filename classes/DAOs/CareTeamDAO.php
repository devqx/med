<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CareTeamDAO
 *
 * @author pauldic
 */
class CareTeamDAO {
    private $conn = null;    
    
    function __construct() {
        if(!isset($_SESSION)){@session_start();}
        try {                
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/CareTeam.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        } 
    }
    
    function addCareTeam($ct, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO care_team SET name = '".$ct->getName()."', description='".$ct->getDescription()."'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            if($stmt->rowCount()>0){
                $ct->setId($pdo->lastInsertId());
            }else{
                $ct=NULL;
            }

            $stmt = NULL;
        }catch(PDOException $e) {
            $ct= $stmt = NULL;
        }catch(Exception $e) {
            $ct= $stmt = NULL;
        }

        return $ct;
    }
    
    function getCareTeam($id, $pdo=NULL){
        $care_team=new CareTeam();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM care_team WHERE id = ".$id;
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $care_team->setId($row["id"]);
                $care_team->setName($row["name"]);
                $care_team->setDescription($row["description"]);
            }else{
                $care_team=NULL;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $care_team=$stmt = NULL;
        }
        return $care_team;
    }
    
    function getCareTeams($pdo=NULL){
        $care_teams=array();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM care_team ORDER BY name";
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $care_team=new CareTeam();                
                    $care_team->setId($row["id"]);
                    $care_team->setName($row["name"]);
                    $care_team->setDescription($row["description"]);
                $care_teams[]=$care_team;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $care_teams=[];
        }
        return $care_teams;
    }
    
    function getCareTeamsByIds($ids, $pdo=NULL){
        $care_teams=array();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM care_team WHERE id IN (". (is_array($ids)? implode(",", $ids) : $ids) .") ORDER BY name";
//            error_log("::::::::::::::::: ".$sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $care_team=new CareTeam();                
                    $care_team->setId($row["id"]);
                    $care_team->setName($row["name"]);
                    $care_team->setDescription($row["description"]);
                $care_teams[]=$care_team;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $care_teams=[];
        }
        return $care_teams;
    }

    
    
    function getPatientCareMembers($ipid, $pdo=NULL){
        $care_teams=array();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql="SELECT c.*, pc.id as pct_id, (SELECT  pc.care_team_ids like CONCAT('%', c.id ,'%')) as is_part_of FROM care_team c LEFT JOIN patient_care_team pc ON TRUE WHERE pc.in_patient_id=$ipid AND pc.status='Active' ";
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $care_team=new CareTeam();                
                    $care_team->setId($row["id"]);
                    $care_team->setName($row["name"]);
                    $care_team->setDescription($row["description"]);
                    $care_team->setAnId($row['is_part_of']==0? NULL:$row['pct_id']);
                $care_teams[]=$care_team;
            }
            if(count($care_teams)===0){
                $care_teams=$this->getCareTeams($pdo);
            }
            $stmt = null;
        }catch(PDOException $e) {
            $stmt = null;
            $care_teams=[];
        }
        return $care_teams;
    }
    
    
    function getPatientCareMembersList($ipid, $pdo=NULL){
        $care_teams=array();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql="SELECT t.*, s.staffId, cm.id as pct_id FROM care_team t LEFT JOIN staff_care_team st ON st.team_id = t.id  AND st.staff_id = '00000000001'  LEFT JOIN staff_directory s ON s.staffId = st.staff_id LEFT JOIN patient_care_member cm ON cm.care_team_id=t.id AND cm.in_patient_id=2 AND cm.status='Active'";
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $care_team=new CareTeam();                
                    $care_team->setId($row["id"]);
                    $care_team->setName($row["name"]);
                    $care_team->setDescription($row["description"]);
                    $care_team->setAnId($row['pct_id']);
//                    error_log($row['pct_id']);
                $care_teams[]=$care_team;
            }
            if(count($care_teams)===0){
                $care_teams=$this->getCareTeams($pdo);
            }
            $stmt = null;
        }catch(PDOException $e) {
            $stmt = null;
            $care_teams=[];
        }
        return $care_teams;
    }
    
    
    function getPatientCareTeamList($ipid, $pdo=NULL){
        $care_teams=array();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql="SELECT t.*, cm.id as pct_id FROM care_team t LEFT JOIN patient_care_member cm ON cm.care_team_id=t.id AND cm.in_patient_id=$ipid AND cm.status='Active'";
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $care_team=new CareTeam();                
                    $care_team->setId($row["id"]);
                    $care_team->setName($row["name"]);
                    $care_team->setDescription($row["description"]);
                    $care_team->setAnId($row['pct_id']);
                $care_teams[]=$care_team;
            }
            if(count($care_teams)===0){
                $care_teams=$this->getCareTeams($pdo);
            }
            $stmt = null;
        }catch(PDOException $e) {
            $stmt = null;
            $care_teams=[];
        }
        return $care_teams;
    }
    
    
    
    function getCareTeamsByStaffMembership($sid, $pdo=NULL){
        $care_teams=array();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT t.*, s.staffId FROM care_team t LEFT JOIN staff_care_team st ON st.team_id = t.id  AND st.staff_id = '$sid'  LEFT JOIN staff_directory s ON s.staffId = st.staff_id";
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $care_team=new CareTeam();                
                    $care_team->setId($row["id"]);
                    $care_team->setName($row["name"]);
                    $care_team->setDescription($row["description"]);
                    $care_team->setAnId($row['staffId']);
                $care_teams[]=$care_team;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $stmt = null;
            $care_teams=[];
        }
        return $care_teams;
    }
    
    
   
    
    function getStaffCareTeamsAsArray($sid, $pdo=NULL){
        $care_teams=array();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT c.* FROM care_team c LEFT JOIN staff_care_team s ON s.team_id=c.id WHERE s.staff_id='$sid' ORDER BY c.name";
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $care_teams[]=$row["name"];
            }
            $stmt = null;
        }catch(PDOException $e) {
            $stmt = null;
            $care_teams=[];
        }
        return $care_teams;
    }

    
    function updateCareTeam($ct, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "UPDATE care_team SET name = '".$ct->getName()."', description='".$ct->getDescription()."' WHERE id = ".$ct->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $status=TRUE;
            $stmt = NULL;
        }catch(PDOException $e) {
            $stmt=NULL;
        }
        return $status;
    }

}
