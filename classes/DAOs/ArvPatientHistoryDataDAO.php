<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/19/15
 * Time: 2:04 PM
 */
class ArvPatientHistoryDataDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ArvPatientHistory.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ArvPatientHistoryData.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ArvHistoryTemplateDataDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($hData, $pdo=NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $canCommit = !$pdo->inTransaction();
            try {
                $pdo->beginTransaction();
            } catch (PDOException $ef){
                //
            }
            $sql = "INSERT INTO arv_patient_history_data (arv_patient_history_id, arv_history_template_data_id, `value`) VALUES (".$hData->getPatientHistory()->getId().", ".$hData->getHistoryTemplateData()->getId().", '".$hData->getValue()."')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount()==1) {
                $hData->setId($pdo->lastInsertId());
                if ($canCommit) {
                    $pdo->commit();
                }
                return $hData;
            }
            error_log("What is the wahala?");
            $pdo->rollBack();
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function get($id, $pdo=NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM patient_history_data WHERE id = $id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $data =new ArvPatientHistoryData($row['id']);
                //$data->setPatientHistory()this would cause recursion
                $data->setHistoryTemplateData( (new ArvHistoryTemplateDataDAO())->get($row['history_template_data_id'], $pdo) );
                $data->setValue($row['value']);
                return $data;
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    public function forHistory($pHistoryId, $pdo=NULL)
    {
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM arv_patient_history_data WHERE arv_patient_history_id = $pHistoryId";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $data = [];
            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $data[] = $this->get($row['id'], $pdo);
            }
            return $data;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }

}