<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/18/15
 * Time: 3:20 PM
 */

class SystemsReviewCategoryDAO {
    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/SystemsReviewCategory.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function get($id, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * from systems_review_category WHERE id=".$id;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $sys_cat = new SystemsReviewCategory($row["id"]);
                $sys_cat->setName($row["name"]);
                $sys_cat->setType($row["type"]);

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
            $sql = "SELECT * from systems_review_category ORDER BY `name`";
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

    function allByType($type=NULL, $pdo=NULL){
            $sys_cat = array();
            try {
                $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
                if($type==NULL){
                    $sql = "SELECT * from systems_review_category WHERE type IS NULL ORDER BY `name`";
                } else {
                    $sql = "SELECT * from systems_review_category WHERE type = '$type' ORDER BY `name`";
                }

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