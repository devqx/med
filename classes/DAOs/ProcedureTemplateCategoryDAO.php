<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/3/15
 * Time: 11:56 AM
 */
class ProcedureTemplateCategoryDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ProcedureTemplateCategory.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function all($pdo=NULL){
        $cats = [];
        try {
            $pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
            $sql = "SELECT * FROM procedure_template_category";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $cat = new ProcedureTemplateCategory($row['id']);
                $cat->setName($row['name']);

                $cats[] = $cat;
            }
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }

        return $cats;
    }
    function get($id, $pdo=NULL){
        $cat = new ProcedureTemplateCategory();
        try {
            $pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
            $sql = "SELECT * FROM procedure_template_category WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $cat->setName($row['name']);
                $cat->setId($row['id']);
            } else {
                $cat = NULL;
            }
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }

        return $cat;
    }

    function add($category, $pdo=NULL){
//        $category = new ProcedureTemplateCategory();
        try {
            $pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
            $sql = "INSERT INTO procedure_template_category (`name`) VALUES ('".escape($category->getName())."')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount() == 1){
                $category->setId($pdo->lastInsertId());
            } else {
                $category = NULL;
            }
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }

        return $category;
    }
}