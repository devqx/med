<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/18/15
 * Time: 3:20 PM
 */

class PhysicalExaminationCategoryDAO {
    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PhysicalExaminationCategory.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function get($id, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * from physical_examination_category WHERE id=".$id;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $sys_cat = new PhysicalExaminationCategory($row["id"]);
                $sys_cat->setName($row["name"]);

                return $sys_cat;
            } else {
                return NULL;
            }
        }catch(PDOException $e) {
            errorLog($e);
            return NULL;
        }
    }

    function all($pdo=NULL){
        $sys_cat = array();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * from physical_examination_category ORDER BY `id`";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $sys_cat[] = $this->get($row['id']);
            }
        }catch(PDOException $e) {
            errorLog($e);
            return $sys_cat;
        }
        return $sys_cat;
    }

}