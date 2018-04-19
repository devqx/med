<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LabCategoryDAO
 *
 * @author pauldic
 */
class OphthalmologyCategoryDAO {
    private $conn = null;
    
    function __construct() {      
        try {                
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/OphthalmologyCategory.php';
            $this->conn=new MyDBConnector();   
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        } 
    }

    function add($cat, $pdo=NULL) {
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
                $sql = "INSERT INTO ophthalmology_category (`name`) VALUES ('".$cat->getName()."')";
                $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $stmt->execute();
                if($stmt->rowCount()>0){
                    $cat->setId($pdo->lastInsertId());
                }else{
                    $cat=NULL;
                }
                
            $stmt = null;
        }catch(PDOException $e) {
            $stmt=NULL;
            $cat=NULL;
        }
        return $cat;
    }
    
    function get($cid, $getFull=FALSE, $pdo=NULL){
        return $this->getCategory($cid,$pdo);
    }

    function all($getFull=FALSE, $pdo=NULL){
        $cats=array();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_category";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $cat=new OphthalmologyCategory();
                $cat->setId($row['id']);
                $cat->setName($row['name']);
                $cats[]=$cat;
            }
            $stmt = NULL;
        }catch(PDOException $e) {
            $cats=array();
        }
        return $cats;
    }

    function getCategory($id, $pdo=NULL){

        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_category WHERE id='$id'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $cat=new OphthalmologyCategory();
                $cat->setId($row['id']);
                $cat->setName($row['name']);
            }else {
                $cat = NULL;
            }
            $stmt = NULL;
        }catch(PDOException $e) {
            $cat=NULL;
            $stmt = NULL;
        }
        return $cat;
    }

}
