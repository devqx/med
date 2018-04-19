<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/24/14
 * Time: 3:24 PM
 */

class ReligionDAO {
    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/Religion.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function getReligion($id, $pdo=NULL){
        $rel = new Religion();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM religion WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $rel->setId($row['id']);
                $rel->setName($row['name']);
            }else {
                $rel = NULL;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $stmt=NULL;
            $rel=NULL;
        }
        return $rel;
    }
    function getReligions($pdo=NULL){
        $rels = array();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM religion";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $rel = new Religion();
                $rel->setId($row['id']);
                $rel->setName($row['name']);
                $rels[] = $rel;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $stmt=NULL;
            $rels=[];
        }
        return $rels;
    }
} 