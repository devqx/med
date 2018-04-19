<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RoomDAO
 *
 * @author pauldic
 */
class RoomDAO {
    private $conn = null;    
    
    function __construct() {
        if(!isset($_SESSION)){@session_start();}
        try {                
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/Ward.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/Room.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/WardDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/RoomTypeDAO.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        } 
    }
    
    function addRoom($r, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO room SET name = '".$r->getName()."', ward_id = '".$r->getWard()->getId()."', type_id=".$r->getRoomType()->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            if($stmt->rowCount()>0){
                $r->setId($pdo->lastInsertId());
            }else{
                $r=NULL;
            }

            $stmt = NULL;
        }catch(PDOException $e) {
            $r= $stmt = NULL;
        }catch(Exception $e) {
            $r= $stmt = NULL;
        }

        return $r;
    }
    
    function getRoom($id, $getFull=FALSE, $pdo=NULL){
        $room=new Room();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM room WHERE id = ".$id;
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $room->setId($row["id"]);
                $room->setName($row["name"]);
                if($getFull){
                    $ward=(new WardDAO())->getWard($row['ward_id'], FALSE, $pdo);
                }else{
                    $ward=new Ward();
                        $ward->setId($row['ward_id']);
                }
                $room->setWard($ward);
                //Allow this for the sake of getting Bed Cost
                $room->setRoomType((new RoomTypeDAO())->getRoomType($row['type_id'], $getFull, $pdo));
                
            }else{
                $room=NULL;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $room=$stmt = NULL;
        }
        return $room;
    }
    
    function getRooms($getFull=FALSE, $pdo=NULL){
        $rooms=array();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM room ORDER BY name";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $room=new Room();                
                    $room->setId($row["id"]);
                    $room->setName($row["name"]);
                    if($getFull){
                        $ward=(new WardDAO())->getWard($row['ward_id'], FALSE, $pdo);
                    }else{
                        $ward=new Ward();
                            $ward->setId($row['ward_id']);
                    }
                    $room->setWard($ward);
                    //Allow this for the sake of Bed
                    $room->setRoomType((new RoomTypeDAO())->getRoomType($row['type_id'], $getFull, $pdo));
                $rooms[]=$room;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $rooms=[];
        }
        return $rooms;
    }

    function getRoomsByWard($wid, $getFull=FALSE, $pdo=NULL){
        $rooms=array();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM room  WHERE ward_id = $wid ORDER BY name";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $room=new Room();                
                    $room->setId($row["id"]);
                    $room->setName($row["name"]);
                    if($getFull){
                        $ward=(new WardDAO())->getWard($row['ward_id'], FALSE, $pdo);
                    }else{
                        $ward=new Ward();
                            $ward->setId($row['ward_id']);
                    }
                    $room->setWard($ward);
                    //Allow this for the sake of Bed
                    $room->setRoomType((new RoomTypeDAO())->getRoomType($row['type_id'], $getFull, $pdo));
                $rooms[]=$room;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $stmt=NULL;
            $rooms=[];
        }
        return $rooms;
    }
        
    function updateRoom($room, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "UPDATE room SET name = '".$room->getName()."', type_id='".$room->getRoomType()->getId()."', ward_id = '".$room->getWard()->getId()."' WHERE id = ".$room->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $status=TRUE;
            $stmt = NULL;
        }catch(PDOException $e) {
            $status = FALSE;
            $stmt=NULL;
        }
        return $status;
    }

}
