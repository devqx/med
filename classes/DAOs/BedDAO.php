<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Bed
 *
 * @author pauldic
 */
class BedDAO {
    private $conn = null;    
    
    function __construct() {
        if(!isset($_SESSION)){@session_start();}
        try {                
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/Bed.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/Room.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/RoomDAO.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        } 
    }
    
    function addBed($b, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO bed SET name = '".$b->getName()."', room_id = '".$b->getRoom()->getId()."', description='".$b->getDescription()."'";
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            if($stmt->rowCount()>0){
                $b->setId($pdo->lastInsertId());
            }else{
                $b=NULL;
            }

            $stmt = NULL;
        }catch(PDOException $e) {
            $b= $stmt = NULL;
        }catch(Exception $e) {
            $b= $stmt = NULL;
        }

        return $b;
    }

    function getBed($id, $getFull = FALSE, $pdo = NULL)
    {
        $bed = new Bed();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT b.*, c.selling_price FROM bed b LEFT JOIN room r ON b.room_id=r.id LEFT JOIN room_type t ON t.id=r.type_id LEFT JOIN insurance_items_cost c ON c.item_code=t.billing_code WHERE c.insurance_scheme_id=1 AND b.id =" . $id . "";
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $bed->setId($row["id"]);
                $bed->setName($row["name"]);
                if ($getFull) {
                    $room = (new RoomDAO())->getRoom($row['room_id'], TRUE, $pdo);//Allow true for single bed
                } else {
                    $room = new Room();
                    $room->setId($row['room_id']);
                }
                $bed->setRoom($room);
                $bed->setAvailable($row['available']);
                $bed->setDescription($row['description']);
                $bed->setDefaultPrice($row['selling_price']);
            } else {
                $bed = NULL;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $bed = $stmt = NULL;
        }
        return $bed;
    }
    
    
    function getBedByCode($code, $getFull=FALSE, $pdo=NULL){
        $bed=new Bed();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT b.*, t.billing_code FROM bed b LEFT JOIN room r ON b.room_id=r.id LEFT JOIN room_type t ON t.id=r.type_id WHERE t.billing_code='".$code."'";
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $bed->setId($row["id"]);
                $bed->setName($row["name"]);
                if($getFull){
                    $room=(new RoomDAO())->getRoom($row['room_id'], FALSE, $pdo);
                }else{
                    $room=new Room();
                    $room->setId($row['room_id']);
                }
                $bed->setRoom($room);
                $bed->setAvailable($row['available']);
                $bed->setDescription($row['description']);
                $bed->setDefaultPrice($row['selling_price']);
            }else{
                $bed=NULL;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $bed=$stmt = NULL;
        }
        return $bed;
    }
    
    function getDefaultPrice($bid, $pdo=NULL){
        $price=NULL;
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT c.selling_price FROM insurance_items_cost c  LEFT JOIN room_type t ON t.billing_code = c.item_code LEFT JOIN room r ON r.type_id=t.id LEFT JOIN bed b ON b.room_id=r.id WHERE b.id = $bid AND c.insurance_scheme_id = 1";
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $price=$row['selling_price'];
            }
            
            $stmt = NULL;
        }catch(PDOException $e) {
            $price = $stmt = NULL;
        }
        return $price;
    }
    
    function getBedPrice($bid, $pid, $pdo=NULL){
        $price=NULL;
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT c.selling_price FROM insurance_items_cost c  LEFT JOIN room_type t ON t.billing_code = c.item_code LEFT JOIN room r ON r.type_id=t.id LEFT JOIN bed b ON b.room_id=r.id WHERE b.id = $bid AND c.insurance_scheme_id = 1";
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $price=$row['selling_price'];
            }
            
            $stmt = NULL;
        }catch(PDOException $e) {
            $price = $stmt = NULL;
        }
        return $price;
    }
    
    function getFreeBeds($getFull=FALSE, $ward=NULL, $order=NULL, $pdo=NULL){
        $beds=array();
        $wardFilterPart1 = ($ward !== NULL) ? " LEFT JOIN ward w ON w.id=r.ward_id" : "";
        $wardFilterPart2 = ($ward !== NULL) ? " AND w.id=".$ward : "";
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT b.*, c.selling_price FROM bed b LEFT JOIN room r ON b.room_id=r.id LEFT JOIN room_type t ON t.id=r.type_id LEFT JOIN insurance_items_cost c ON c.item_code=t.billing_code $wardFilterPart1 WHERE b.available=1 AND c.insurance_scheme_id=1 $wardFilterPart2 ORDER BY ".(($order===NULL)? "b.name":$order);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $bed=new Bed();                
                $bed->setId($row["id"]);
                $bed->setName($row["name"]);
                if($getFull){
                    $room=(new RoomDAO())->getRoom($row['room_id'], TRUE, $pdo);
                }else{
                    $room=new Room($row['room_id']);
                }
                $bed->setRoom($room);
                $bed->setAvailable($row['available']);
                $bed->setDescription($row['description']);
                $bed->setDefaultPrice($row['selling_price']);
                $beds[]=$bed;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $beds=[];
        }
        return $beds;
    }
    
    function getBeds($getFull=FALSE, $order=NULL, $pdo=NULL){
        $beds=array();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT b.*, c.selling_price FROM bed b LEFT JOIN room r ON b.room_id=r.id LEFT JOIN room_type t ON t.id=r.type_id LEFT JOIN insurance_items_cost c ON c.item_code=t.billing_code WHERE  c.insurance_scheme_id=1 ORDER BY ".(($order===NULL)? "b.name":$order);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $bed=new Bed();                
                $bed->setId($row["id"]);
                $bed->setName($row["name"]);
                if($getFull){
                    $room=(new RoomDAO())->getRoom($row['room_id'], FALSE, $pdo);
                }else{
                    $room=new Room($row['room_id']);
                }
                $bed->setRoom($room);
                $bed->setAvailable($row['available']);
                $bed->setDescription($row['description']);
                $bed->setDefaultPrice($row['selling_price']);
                $beds[]=$bed;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $beds=[];
        }
        return $beds;
    }

    function getBedsByWard($wid, $getFull=FALSE, $pdo=NULL){
        $beds=array();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT b.*, c.selling_price FROM bed b LEFT JOIN room r ON b.room_id=r.id LEFT JOIN room_type t ON t.id=r.type_id LEFT JOIN ward w ON w.id=r.ward_id LEFT JOIN insurance_items_cost c ON c.item_code=t.billing_code WHERE c.insurance_scheme_id=1 AND w.id =$wid ORDER BY w.name,  b.name";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $bed=new Bed();                
                    $bed->setId($row["id"]);
                    $bed->setName($row["name"]);
                    if($getFull){
                        $room=(new RoomDAO())->getRoom($row['room_id'], FALSE, $pdo);
                    }else{
                        $room=new Room();
                            $room->setId($row['room_id']);
                    }
                    $bed->setRoom($room);
                    $bed->setAvailable($row['available']);
                    $bed->setDescription($row['description']);
                    $bed->setDefaultPrice($row['selling_price']);
                $beds[]=$bed;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $stmt=NULL;
            $beds=[];
        }
        return $beds;
    }
        
    function changeStatus($bs, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "UPDATE bed SET available = ".$bs->getAvailable()." WHERE id = ".$bs->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $status=TRUE;
            $stmt = NULL;
        }catch(PDOException $e) {
            $stmt=NULL;
        }
        return $status;
    }
        
    function occupyBed($bid, $pdo=NULL){
        $status=NULL;
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            if($this->isAvailable($bid, $pdo)){
                $sql = "UPDATE bed SET available = 0 WHERE id = ".$bid;
//                error_log($sql);
                $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $stmt->execute();
                $status=TRUE;
            }else{
                $status=FALSE;
            }
            $stmt = NULL;
        }catch(PDOException $e) {
            $stmt=NULL;$status=NULL;
        }
        return $status;
    }
      
        
    function unAssignBed($bid, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            
            $sql = "UPDATE bed SET available = 1 WHERE id = ".$bid;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $status=TRUE;
            
            $stmt = NULL;
        }catch(PDOException $e) {
            $stmt=NULL;            
            $status=FALSE;
        }
        return $status;
    }
      
    function isAvailable($bid, $pdo=NULL){
        $status=FALSE;
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM bed WHERE available IS TRUE AND id =".$bid;
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $status=TRUE;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $stmt=NULL;
            $status=FALSE;
        }
        return $status;
    }
    
    function updateBed($bs, $pdo=NULL){
        try {
        	   $available = $bs->isAvailable() ? $bs->isAvailable() : FALSE;
        	
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "UPDATE bed SET name = '".$bs->getName()."', room_id = '".$bs->getRoom()->getId()."', description = '".$bs->getDescription()."', available=$available  WHERE id = ".$bs->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $status=TRUE;
            $stmt = NULL;
        }catch(PDOException $e) {
            $stmt=NULL;
        }
        return $status;
    }

}
