<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 2:09 PM
 */
class PhysiotherapyItemBatchDAO
{
    private $conn = null;

    function __construct() {
        if(!isset($_SESSION)){@session_start();}
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PhysiotherapyItemBatch.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PhysiotherapyItemBatchDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PhysiotherapyItemDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/ServiceCenterDAO.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function add($batch, $pdo=NULL){
        try {
            $pdo = $pdo==NULL?$this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO physiotherapy_item_batch (`name`, item_id, quantity, service_centre_id) VALUES ('".escape($batch->getName())."', ".$batch->getItem()->getId().",'".$batch->getQuantity()."', ".$batch->getServiceCentre()->getId().")";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($stmt->rowCount()==1){
                $batch->setId($pdo->lastInsertId());
                return $batch;
            }else {
                return null;
            }
        }catch (PDOException $e){
            errorLog($e);
            return null;
        }
    }
    function get($id, $pdo=NULL){
        try {
            $pdo = $pdo==NULL?$this->conn->getPDO() : $pdo;
            if($id === NULL)
                return null;
            $sql = "SELECT * FROM physiotherapy_item_batch WHERE id = " . $id;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $batch = new PhysiotherapyItemBatch($row['id']);
                $batch->setName($row['name']);
                $batch->setItem( (new PhysiotherapyItemDAO())->get($row['item_id'], $pdo) );
                $batch->setQuantity($row['quantity']);
                $batch->setServiceCentre( (new ServiceCenterDAO())->get($row['service_centre_id'], $pdo) );

                return $batch;
            }

            return null;
        }catch (PDOException $e){
            errorLog($e);
            return null;
        }
    }
    function stockUp($batch, $pdo=NULL){
        try {
            $pdo = $pdo==NULL?$this->conn->getPDO() : $pdo;
            $sql = "UPDATE physiotherapy_item_batch SET quantity = (quantity + ".$batch->getQuantity().") WHERE id = ".$batch->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($stmt->rowCount()==1){
                return $batch;
            }else {
                return null;
            }
        }catch (PDOException $e){
            errorLog($e);
            return null;
        }
    }
    function stockAdjust($batch, $pdo=NULL){
        try {
            $pdo = $pdo==NULL?$this->conn->getPDO() : $pdo;
            $sql = "UPDATE physiotherapy_item_batch SET quantity = ".$batch->getQuantity()." WHERE id = ".$batch->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($stmt->rowCount()==1){
                return $batch;
            }else {
                return null;
            }
        }catch (PDOException $e){
            errorLog($e);
            return null;
        }
    }
    function depleteStock($batch, $quantity, $pdo=NULL){
        try {
            $pdo = $pdo==NULL?$this->conn->getPDO() : $pdo;
            $sql = "UPDATE physiotherapy_item_batch SET quantity = (quantity - " .$quantity.") WHERE id = ".$batch->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($stmt->rowCount()==1){
                return $batch;
            }else {
                return null;
            }
        }catch (PDOException $e){
            errorLog($e);
            return null;
        }
    }

    function all($page, $pageSize, $pdo=NULL){
        $sql = "SELECT * FROM physiotherapy_item_batch";
        $total = 0;
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $total = $stmt->rowCount();
        } catch (PDOException $e){
            error_log("ERROR: Failed to return total number of records");
        }
        $page = ($page > 0) ? $page : 0;
        $offset = ($page > 0) ? $pageSize * $page : 0;

        $batches = [];
        try {
            $pdo = $pdo==NULL?$this->conn->getPDO() : $pdo;
            $sql .= " LIMIT $offset, $pageSize";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $batches[] = $this->get($row['id'], $pdo);
            }

        }catch (PDOException $e){
            errorLog($e);
            $batches = [];
        }
        $results = (object)null;
        $results->data = $batches;
        $results->total = $total;
        $results->page = $page;

        return $results;
    }

    function getItemBatches($item, $pdo=NULL){
        $batches = [];
        try {
            $pdo = $pdo==NULL?$this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM physiotherapy_item_batch WHERE item_id = " . $item->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $batches[] = $this->get($row['id'], $pdo);
            }
            return $batches;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }


}