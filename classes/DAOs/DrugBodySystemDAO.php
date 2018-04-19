<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/28/14
 * Time: 10:37 AM
 */

class DrugBodySystemDAO {

    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DrugBodySystem.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function getBodySystem($id, $pdo=NULL){
        $bds = new DrugBodySystem();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM drug_body_systems WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $bds->setId($row['id']);
                $bds->setName($row['name']);
                $bds->setDescription($row['description']);
            }
            $stmt = NULL;
        }catch (PDOException $e){
            $bds = NULL;
        }
        return $bds;
    }

    function getBodySystems($pdo=NULL){
        $bdss = array();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM drug_body_systems";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $bd = $this->getBodySystem($row['id'], $pdo);
                $bdss[] = $bd;
            }
            $stmt = NULL;
        }catch (PDOException $e){
            $bdss = NULL;
        }
        return $bdss;
    }

    //TODO: add body systems
}