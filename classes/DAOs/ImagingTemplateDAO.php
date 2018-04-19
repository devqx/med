<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/29/15
 * Time: 12:59 PM
 */

class ImagingTemplateDAO {
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ImagingTemplate.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ScanCategoryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function addTemplate($template, $pdo=NULL){
//        $template = new ImagingTemplate();
        try {
            $pdo = $pdo==NULL?$this->conn->getPDO():$pdo;
            $pdo->beginTransaction();
            $sql = "INSERT INTO imaging_template (category_id, title, body_part) VALUES (".$template->getCategory().", '".escape($template->getTitle())."', '".escape($template->getBodyPart())."')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount() == 1){
                $template->setId($pdo->lastInsertId());
                $pdo->commit();
            } else {
                $template = NULL;
                $pdo->rollBack();
            }
            $stmt = NULL;
        } catch (PDOException $e){
            errorLog($e);
            $template = NULL;
        }
        return $template;
    }

    function editTemplate($template, $pdo=NULL){
        try {
            $pdo = $pdo==NULL?$this->conn->getPDO():$pdo;
            $sql = "UPDATE imaging_template SET category_id=".$template->getCategory().", title='".escape($template->getTitle())."', body_part='".escape($template->getBodyPart())."' WHERE id=".$template->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            $stmt = NULL;
            return $template;
        } catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function getTemplates($pdo=NULL){
        $templates = array();
        try {
            $pdo = $pdo==NULL?$this->conn->getPDO():$pdo;
            $sql = "SELECT * FROM imaging_template";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $template = new ImagingTemplate();
                $template->setId($row['id']);
                $template->setCategory((new ScanCategoryDAO())->getCategory($row['category_id']));
                $template->setBodyPart($row['body_part']);
                $template->setTitle($row['title']);

                $templates[] = $template;
            }
            $stmt = null;
        } catch (PDOException $e){
            errorLog($e);
            $templates = [];
        }
        return $templates;
    }

    function getTemplate($id, $pdo=NULL){
        $template = new ImagingTemplate();
        try {
            $pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
            $sql = "SELECT * FROM imaging_template WHERE id=".$id;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $template->setId($row['id']);
                $template->setCategory((new ScanCategoryDAO())->getCategory($row['category_id']));
                $template->setBodyPart($row['body_part']);
                $template->setTitle($row['title']);
            }
            else {
                $template = null;
            }
        } catch (PDOException $e){
            $template = null;
        }
        return $template;
    }

    function getTemplateByCategory($cid, $pdo=NULL){
        $template = new ImagingTemplate();
        try {
            $pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
            $sql = "SELECT * FROM imaging_template WHERE category_id=".$cid;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $template->setId($row['id']);
                $template->setCategory((new ScanCategoryDAO())->getCategory($row['category_id']));
                $template->setBodyPart($row['body_part']);
                $template->setTitle($row['title']);
            }
            else {
                $template = null;
            }
        } catch (PDOException $e){
            $template = null;
        }
        return $template;
    }

}