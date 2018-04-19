<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/29/16
 * Time: 10:56 AM
 */
class FormQuestionOptionDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/FormQuestionOption.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/FormQuestionTemplateDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($tData, $pdo=NULL){
//        $tData = new FormQuestionOption();
        try {
            $pdo = $pdo == NULL?$this->conn->getPDO(): $pdo;
            $sql = "INSERT INTO form_question_option (form_question_template_id, label, datatype) VALUES (".$tData->getQuestionTemplate()->getId().", '".$tData->getLabel()."', '".$tData->getDataType()."')";
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
            $sql = "SELECT * FROM form_question_option WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                return (new FormQuestionOption($row['id']))
                    ->setQuestionTemplate( (new FormQuestionTemplateDAO())->get($row['form_question_template_id'], $pdo) )
                    ->setLabel($row['label'])
                    ->setDataType($row['datatype'])
                    ->setRelation($row['relation']);
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
            $sql = "SELECT * FROM form_question_option";
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
            $sql = "SELECT * FROM form_question_option WHERE form_question_template_id = $tpl_id";
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