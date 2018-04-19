<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ListStyleDAO
 *
 * @author pauldic
 */
class LifeStyleDAO {
    private $conn = null;
    
    function __construct() {      
        try {                
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/LifeStyle.php';
            $this->conn=new MyDBConnector();   
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        } 
    }

    function getLifeStyle($lid, $pdo=NULL){
        $ls=new LifeStyle();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM life_style WHERE id=".$lid;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $ls->setId($row['id']);
                $ls->setTitle($row['title']);
                $ls->setDescription($row['description']);
            }else {
                $ls = NULL;
            }
            $stmt = NULL;
        }catch(PDOException $e) {
            $ls=NULL;
        }
        return $ls;
    }

    function getLifeStyles($pdo=NULL){
        $lss=array();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM life_style";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $lss[]=$this->getLifeStyle($row['id'], $pdo);
            }
            $stmt = NULL;
        }catch(PDOException $e) {
            $lss=array();
        }
        return $lss;
    }

}
