<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/3/15
 * Time: 3:23 PM
 */
class BadgeDAO
{
    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/Badge.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function all($pdo=NULL){
        $data = [];
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM badge";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $badge = new Badge($row['id']);
                $badge->setName($row['name']);
                $badge->setIcon(htmlentities($row['icon']));

                $data[] = $badge;
            }
            return $data;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }

    function get($id, $pdo=NULL){
        if( is_null($id))return NULL;
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM badge WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $badge = new Badge($row['id']);
                $badge->setName($row['name']);
                $badge->setIcon(htmlentities($row['icon']));

                return $badge;
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

}