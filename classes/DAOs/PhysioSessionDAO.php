<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/17/16
 * Time: 12:20 PM
 */
class PhysioSessionDAO
{
    private $conn = null;

    function __construct() {
        if(!isset($_SESSION)){@session_start();}
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PhysioSession.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PatientDemograph.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/StaffSpecialization.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/StaffDirectory.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/StaffDirectoryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/StaffSpecializationDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PhysioBookingDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }


    function add($session, $pdo=NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $booking_id = $session->getBooking()->getId();
            $noteBy = $session->getNotedBy()->getId();
            $note = escape($session->getNote());

            $sql = "INSERT INTO physiotherapy_session (booking_id, session_date, note, noted_by) VALUES ($booking_id, NOW(), '$note', $noteBy )";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($stmt->rowCount() == 1){
                $session->setId($pdo->lastInsertId());
                return $session;
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function get($id, $full, $pdo=NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM physiotherapy_session WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                if($full) {
                    return (new PhysioSession($row['id']))
                        ->setBooking( (new PhysioBookingDAO())->get($row['booking'], $pdo) )
                        ->setDate($row['session_date'])
                        ->setNote($row['note'])
                        ->setNotedBy( (new StaffDirectoryDAO())->getStaff($row['noted_by'], FALSE, $pdo)  );
                } else {
                    return (new PhysioSession($row['id']))
                        //->setBooking( (new PhysioBookingDAO())->get($row['booking'], $pdo) )
                        ->setDate($row['session_date'])
                        ->setNote($row['note'])
                        ->setNotedBy( (new StaffDirectoryDAO())->getStaff($row['noted_by'], FALSE, $pdo)  );
                }
            }
            return null;
        }catch (PDOException $e){
            errorLog($e);
            return null;
        }
    }

    function getForBooking($bookingId, $pdo){
        $bookings = [];
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM physiotherapy_session WHERE booking_id=$bookingId ORDER BY session_date";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $bookings[] = $this->get($row['id'], FALSE, $pdo);
            }
            return $bookings;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }
}