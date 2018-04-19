<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 2/23/15
 * Time: 10:27 AM
 */

class DistributionListDAO {
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DistributionList.php';
            if (!isset($_SESSION)) session_start();
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    public function add($dist, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $pdo->beginTransaction();
            $sql = "INSERT INTO distribution_list (`name`, sql_query, date_added) VALUES ('".$dist->getName()."', '".escape($dist->getSqlQuery())."', NOW())";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($stmt->rowCount()>0){
                $dist->setId($pdo->lastInsertId());
                $pdo->commit();
                return $dist;
            }

            $pdo->rollBack();
            return NULL;
        }catch(PDOException $e) {
            errorLog($e);
            $pdo->rollBack();
            return NULL;
        }
    }

    public function getDistributionList($id, $pdo=NULL){
        $distList = new DistributionList();
        try{
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM distribution_list WHERE id=".$id;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $distList->setId($row['id']);
                $distList->setDateAdded($row['date_added']);
                $distList->setName($row['name']);
                $distList->setSqlQuery($row['sql_query']);
            }else{
                $distList=NULL;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $distList = NULL;
        }
        return $distList;
    }

    public function getDistributionLists($pdo=NULL){
        $distributionLists = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM distribution_list";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $distList= new DistributionList();
                $distList->setId($row['id']);
                $distList->setDateAdded($row['date_added']);
                $distList->setName($row['name']);
                $distList->setSqlQuery($row['sql_query']);
                $distributionLists[]=$distList;
            }
            $stmt = null;

        }catch (PDOException $e){
            $distributionLists = [];
        }
        return $distributionLists;
    }

    public function update($dist, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $pdo->beginTransaction();
            $sql = "UPDATE distribution_list SET name='".$dist->getName()."', sql_query='".escape($dist->getSqlQuery())."' WHERE id =".$dist->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($stmt->rowCount()>=0){
                $pdo->commit();
                return $dist;
            }

            $pdo->rollBack();
            return NULL;
        }catch(PDOException $e) {
            $pdo->rollBack();
            return NULL;
        }
    }

    public function delete($dist, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $pdo->beginTransaction();
            $sql = "DELETE FROM distribution_list WHERE id =".$dist->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($stmt->rowCount()>=0){
                $pdo->commit();
                return TRUE;
            }

            $pdo->rollBack();
            return FALSE;
        }catch(PDOException $e) {
            $pdo->rollBack();
            return FALSE;
        }
    }

    public function showNumberOfPatients($sql=""){
        require_once $_SERVER['DOCUMENT_ROOT'] .'/Connections/dbconnection.php';
        if (isset($database_dbconnection, $dbconnection)) {
            mysql_select_db ( $database_dbconnection, $dbconnection );
        }
        $data = array();
        $rst = mysql_query($sql);
        while ($row = mysql_fetch_assoc($rst)){
            $data[] = $row;
        }
        return count($data);
    }
}