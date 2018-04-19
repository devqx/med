<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/19/15
 * Time: 2:04 PM
 */
class FormPatientQuestionAnswerDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/FormPatientQuestion.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/FormPatientQuestionAnswer.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/FormQuestionOptionDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($hData, $pdo=NULL){
        //$hData = new FormPatientQuestionAnswer();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $canCommit = !$pdo->inTransaction();
            try {
                $pdo->beginTransaction();
            } catch (PDOException $ef){
                //
            }
            $sql = "INSERT INTO form_patient_question_answer (form_patient_question_id, form_question_option_id, `value`) VALUES (".$hData->getPatientQuestion()->getId().", ".$hData->getFormQuestionOption()->getId().", '".$hData->getValue()."')";
            // error_log($sql);
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
            $sql = "SELECT * FROM form_patient_question_answer WHERE id = $id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $data =new FormPatientQuestionAnswer($row['id']);
                //$data->setPatientHistory()this would cause recursion
                $data->setFormQuestionOption( (new FormQuestionOptionDAO())->get($row['form_question_option_id'], $pdo) );
                $data->setValue($row['value']);
                return $data;
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    public function forQuestion($pHistoryId, $pdo=NULL)
    {
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM form_patient_question_answer WHERE form_patient_question_id = $pHistoryId";
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