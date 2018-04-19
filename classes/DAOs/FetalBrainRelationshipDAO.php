<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/9/16
 * Time: 5:36 PM
 */
class FetalBrainRelationshipDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/FetalBrainRelationship.php';
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
            $sql = "SELECT * FROM fetal_brain_relationship WHERE id=".$id;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                return (new FetalBrainRelationship())
                    ->setId($row['id'])
                    ->setName($row['name']);
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
            $sql = "SELECT * FROM fetal_brain_relationship";
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