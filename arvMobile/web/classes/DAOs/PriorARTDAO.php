<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/15/16
 * Time: 3:12 PM
 */
class PriorARTDAO
{
    private $conn = null;

    public function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/arvMobile/web/classes/PriorART.php';
            if (!isset($_SESSION)) session_start();
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($mode, $pdo=NULL){
        $sql = "INSERT INTO sti_prior_art (`code`, `name`) VALUES ('".$mode->getCode()."',  '".$mode->getName()."')";
        try {
            $pdo = $pdo==NULL? $this->conn->getPDO(): $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount()==1){
                $mode->setId($pdo->lastInsertId());
                return $mode;
            }
            return null;
        } catch(PDOException $e){
            errorLog($e);
            return null;
        }
    }

    function get($id, $pdo=null){
        try {
            $sql = "SELECT * FROM sti_prior_art WHERE id=$id";
            $pdo = $pdo==NULL? $this->conn->getPDO(): $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                return (new PriorART($row['id']))->setCode($row['code'])->setName($row['name']);
            }
            return null;
        }catch (PDOException $e){
            errorLog($e);
            return null;
        }
    }

    function getByName($name, $pdo = null)
    {
        try {
            $sql = "SELECT * FROM sti_prior_art WHERE `name`='$name'";
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                return (new PriorART($row['id']))->setCode($row['code'])->setName($row['name']);
            }
            return null;
        } catch (PDOException $e) {
            errorLog($e);
            return null;
        }
    }

    function all($pdo=null){
        $data = [];
        try {
            $sql = "SELECT * FROM sti_prior_art";
            $pdo = $pdo==NULL? $this->conn->getPDO(): $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $data[] =  $this->get($row['id'], $pdo);
            }
            return $data;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }
}