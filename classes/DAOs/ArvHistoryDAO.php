<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/16/15
 * Time: 10:45 AM
 */
class ArvHistoryDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ArvHistoryTemplate.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ArvHistory.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ArvHistoryTemplateDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($history, $pdo=NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO arv_history (arv_template_id) VALUES (".$history->getTemplate()->getId().")";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount()>0){
                $history->setId($pdo->lastInsertId());
                return $history;
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function get($id, $pdo=NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM arv_history WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $temp = new ArvHistory($row['id']);
                $temp->setTemplate( (new ArvHistoryTemplateDAO())->get($row['arv_template_id'], $pdo) );
                return $temp;
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function all($pdo=NULL){
        $temps = [];
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM arv_history";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $temp = new ArvHistory($row['id']);
                $temp->setTemplate( (new ArvHistoryTemplateDAO())->get($row['arv_template_id'], $pdo) );

                $temps[] = $temp;
            }
            return $temps;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }

    function byTemplate($templateId, $pdo=NULL){
        $temps = [];
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM arv_history WHERE arv_template_id = $templateId";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $temp = new ArvHistory($row['id']);
                $temp->setTemplate( (new ArvHistoryTemplateDAO())->get($row['arv_template_id'], $pdo) );

                $temps[] = $temp;
            }
            return $temps;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }
}