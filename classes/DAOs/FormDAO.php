<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/3/16
 * Time: 9:47 AM
 */
class FormDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Form.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/FormComponent.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/FormComponentDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function get($id, $pdo=NULL)
    {
        if ($id == NULL)
            return NULL;
        try {
            $pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
            $sql = "SELECT * FROM form WHERE id=".$id;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                return (new Form())
                    ->setId($row['id'])
                    ->setName($row['name'])
                    ->setComponents( (new FormComponentDAO())->forForm($row['id'], $pdo) );
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
            $sql = "SELECT * FROM form";
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
}