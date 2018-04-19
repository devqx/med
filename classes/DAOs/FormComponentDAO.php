<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/3/16
 * Time: 10:14 AM
 */
class FormComponentDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Form.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/FormComponent.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/FormDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/FormQuestionDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function get($id, $pdo=NULL){
        try {
            $pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
            $sql = "SELECT * FROM form_component WHERE id=".$id;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                return (new FormComponent())
                    ->setId($row['id'])
                    ->setForm( (new FormDAO())->get($row['form_id'], $pdo) )
                    ->setFormQuestion( (new FormQuestionDAO())->get($row['form_question_id'], $pdo) );
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return  NULL;
        }
    }

    function all($pdo=NULL)
    {
        $pres = [];
        try {
            $pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
            $sql = "SELECT * FROM form_component";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $pres[] = $this->get($row['id'], $pdo);
            }
            return $pres;
        }catch (PDOException $e){
            errorLog($e);
            return  [];
        }
    }

    function forForm($id, $pdo)
    {
        $components = [];
        try {
            $pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
            $sql = "SELECT * FROM form_component WHERE form_id=".$id;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $components[] = (new FormQuestionDAO())->get($row['form_question_id'], $pdo);
            }
            return $components;
        }catch (PDOException $e){
            errorLog($e);
            return  $components;
        }
    }
}