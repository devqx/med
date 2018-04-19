<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/18/15
 * Time: 3:37 PM
 */

class PhysicalExaminationDAO {
    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PhysicalExaminationCategory.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PhysicalExamination.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PhysicalExaminationCategoryDAO.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function get($id, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * from physical_examination WHERE id=".$id;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {

                $sys = new PhysicalExamination($row["id"]);
                $sys->setName($row["name"]);
                $sys->setCategory( (new PhysicalExaminationCategoryDAO())->get($row['category_id'], $pdo) );

                return $sys;
            } else {
                return NULL;
            }
        }catch(PDOException $e) {
            errorLog($e);
            return NULL;
        }
    }

    function all($pdo=NULL){
        $sys = array();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * from physical_examination ORDER BY category_id, `name`";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $sys[] = $this->get($row['id']);
            }
        }catch(PDOException $e) {
            errorLog($e);
            return $sys;
        }
        return $sys;
    }

    function byCat($cat_id, $pdo=NULL){
        $sys = array();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * from physical_examination WHERE category_id = $cat_id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $sys[] = $this->get($row['id']);
            }
        }catch(PDOException $e) {
            errorLog($e);
            return $sys;
        }
        return $sys;
    }
}