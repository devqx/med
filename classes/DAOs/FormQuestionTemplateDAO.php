<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/29/16
 * Time: 10:27 AM
 */
class FormQuestionTemplateDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/FormQuestionTemplate.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/FormQuestionOptionDAO.php';
            require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($temp, $pdo=NULL){
        // $temp = new FormQuestionTemplate();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO form_question_template (label) VALUES ('".escape($temp->getLabel())."')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount() > 0){
                $temp->setId($pdo->lastInsertId());
                return $temp;
            }
            return NULL;
        }
        catch(PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function get($id, $pdo=NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM form_question_template WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $temp = new FormQuestionTemplate($row['id']);
                $temp->setLabel($row['label']);
                return $temp;
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function all($pdo=NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM form_question_template";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            $temps = [];
            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $temps[] = $this->get($row['id'], $pdo);
            }
            return $temps;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }


}