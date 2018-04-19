<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/3/15
 * Time: 12:27 PM
 */
class ProcedureTemplateDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ProcedureTemplate.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureTemplateCategoryDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function all($pdo=NULL){
        $templates = [];
        try {
            $pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
            $sql = "SELECT * FROM procedure_template";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $template = new ProcedureTemplate($row['id']);
                $template->setCategory((new ProcedureTemplateCategoryDAO())->get($row['category_id'], $pdo) );
                $template->setContent(htmlentities($row['content']));

                $templates[] = $template;
            }
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }

        return $templates;
    }
    function get($id, $pdo=NULL){
        $template = new ProcedureTemplate();
        try {
            $pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
            $sql = "SELECT * FROM procedure_template WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $template->setId($row['id']);
                $template->setCategory((new ProcedureTemplateCategoryDAO())->get($row['category_id'], $pdo) );
                $template->setContent(htmlentities($row['content']));
            } else {
                $template = NULL;
            }
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }

        return $template;
    }

    function add($template, $pdo=NULL){
        try {
            $pdo = $pdo===NULL?$this->conn->getPDO(): $pdo;
            $category_id = $template->getCategory()->getId();
            $content = escape($template->getContent());
            $sql = "INSERT INTO procedure_template (category_id, content) VALUES ($category_id, '$content')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount() == 1){
                $template->setId($pdo->lastInsertId());
                return $template;
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }
}