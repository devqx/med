<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LGADAO
 *
 * @author pauldic
 */
class LGADAO {
    private $conn = null;    
    
    function __construct() {      
        try {                
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/LGA.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/State.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/StateDAO.php';
            $this->conn=new MyDBConnector();   
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        } 
    }
    
    function getLGA($lid, $getFull=FALSE, $pdo=NULL){
        $lga=new LGA();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * from lga WHERE id=".$lid;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
//            error_log(print_r($sql, TRUE));
            
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $lga->setId($row["id"]);
                $lga->setName($row["name"]);
                if($getFull){
                    $dao=new StateDAO();
                    $state=$dao->getState($row['state_id'], $pdo);
                }else{
                    $state=new State();
                    $state->setId($row["state_id"]);
                }
                $lga->setState($state);
            }
            $stmt = null;
        }catch(PDOException $e) {
            $lga=NULL;
        }
        return $lga;
    }
    
    function getLGAs($getFull=FALSE){
        $lgas=array();
        try {                
            $pdo=$this->conn->getPDO();
            $sql = "SELECT * from lga";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
//            error_log(print_r($sql, TRUE));
            
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $lga=new LGA();
                    $lga->setId($row["id"]);
                    $lga->setName($row["name"]);
                    if($getFull){
                        $dao=new StateDAO();
                            $state=$dao->getState($row["state_id"], $pdo);
                    }else{
                        $state=new State();
                            $state->setId($row["state_id"]);
                    }
                    $lga->setState($state);
                $lgas[]=$lga;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $lgas=array();
        }
        return $lgas;
    }
    
    function getLGAsByState($sid, $pdo=NULL){
        $lgas=array();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * from lga WHERE state_id=".$sid;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $lga=new LGA();
                    $lga->setId($row["id"]);
                    $lga->setName($row["name"]);
                $lgas[]=$lga;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $lgas=array();
        }
        return $lgas;
    }
    
}
