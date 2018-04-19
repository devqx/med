<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/29/15
 * Time: 12:59 PM
 */

class DentistryTemplateCategoryDAO {
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ImagingTemplateCategory.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function all($pdo=NULL){
        $cats = [];
        try {
            $pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
            $sql = "SELECT * FROM imaging_template_category";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $cats[] = $this->get($row['id'], $pdo);
            }
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }

        return $cats;
    }

    function get($id, $pdo=NULL){
        try {
            $pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
            $sql = "SELECT * FROM imaging_template_category WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $cat = new ImagingTemplateCategory($row['id']);
                $cat->setName($row['name']);

                return $cat;
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function add(){}

    function update(){}
}