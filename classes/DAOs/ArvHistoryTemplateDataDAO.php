<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/16/15
 * Time: 10:56 AM
 */
class ArvHistoryTemplateDataDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ArvHistoryTemplateData.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ArvHistoryTemplateDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($tData, $pdo=NULL){
        try {
            $pdo = $pdo == NULL?$this->conn->getPDO(): $pdo;
            $sql = "INSERT INTO arv_history_template_data (arv_history_template_id, label, datatype) VALUES (".$tData->getHistoryTemplate()->getId().", '".$tData->getLabel()."', '".$tData->getDataType()."')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount()>0){
                $tData->setId($pdo->lastInsertId());
                return $tData;
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function get($id, $pdo=NULL){

        try {
            $pdo = $pdo == NULL?$this->conn->getPDO(): $pdo;
            $sql = "SELECT * FROM arv_history_template_data WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $tData = new ArvHistoryTemplateData($row['id']);
                $tData->setHistoryTemplate( (new ArvHistoryTemplateDAO())->get($row['arv_history_template_id'], $pdo) );
                $tData->setLabel($row['label']);
                $tData->setDataType($row['datatype']);
                $tData->setRelation($row['relation']);

                return $tData;
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function all($pdo=NULL){
        try {
            $pdo = $pdo == NULL?$this->conn->getPDO(): $pdo;
            $sql = "SELECT * FROM arv_history_template_data";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            $tDatas = [];
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $tDatas[] = $this->get($row['id'], $pdo);
            }
            return $tDatas;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }

    function byTemplate($tpl_id, $pdo=NULL){
        try {
            $pdo = $pdo == NULL?$this->conn->getPDO(): $pdo;
            $sql = "SELECT * FROM arv_history_template_data WHERE arv_history_template_id = $tpl_id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            $tDatas = [];
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $tDatas[] = $this->get($row['id'], $pdo);
            }
            return $tDatas;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }


}