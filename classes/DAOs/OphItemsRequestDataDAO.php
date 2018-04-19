<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 4:34 PM
 */
class OphItemsRequestDataDAO
{
    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/OphItemsRequestData.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/OphthalmologyItemDAO.php';

            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function get($id, $pdo=NULL)
    {
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_items_request_data WHERE id=".$id;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $itemsRequestData = (new OphItemsRequestData())
                    ->setId($row["id"])
                    ->setItem( (new OphthalmologyItemDAO())->get($row['item_id'], $pdo) );
                return $itemsRequestData;
            }
            return NULL;
        }catch(PDOException $e) {
            errorLog($e);
            return NULL;
        }
    }

    function add($requestData, $pdo=NULL){
        //$requestData = new OphItemsRequestData();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO ophthalmology_items_request_data (request_id, item_id) VALUES (".$requestData->getRequest()->getId().", ".$requestData->getItem()->getId().")";
//            error_log("..".$sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($stmt->rowCount() == 1){
                $requestData->setId($pdo->lastInsertId());
            }
            return $requestData;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function getRequestItems($requestId, $pdo)
    {
        $itemsRequestData = [];
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * from ophthalmology_items_request_data WHERE request_id=".$requestId;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $itemsRequestData[]  = $this->get($row['id'], $pdo);
            }
            return $itemsRequestData;
        }catch(PDOException $e) {
            return [];
        }
    }
}