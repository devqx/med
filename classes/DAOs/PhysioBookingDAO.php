<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/15/16
 * Time: 4:17 PM
 */
class PhysioBookingDAO
{
    private $conn = null;

    function __construct() {
        if(!isset($_SESSION)){@session_start();}
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PhysioBooking.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PatientDemograph.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/StaffSpecialization.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/StaffDirectory.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/Bill.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/Clinic.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/StaffDirectoryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/StaffSpecializationDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PhysioSessionDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/InsuranceItemsCostDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/BillSourceDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/BillDAO.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function add($booking, $pdo=NULL){
        // $booking = new PhysioBooking();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $patient_id = $booking->getPatient()->getId();
            $type = $booking->getSpecialization()->getId();
            $bookedBy = $booking->getBookedBy()->getId();
            $count = $booking->getCount();

            $pdo->beginTransaction();

            $sql = "INSERT INTO physiotherapy_booking (patient_id, booking_date, specialization_id, `count`, booked_by) VALUES ($patient_id, NOW(), $type, $count, $bookedBy )";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            $specialty = (new StaffSpecializationDAO())->get($type, $pdo);
            $amount = (new InsuranceItemsCostDAO())->getItemPriceByCode($specialty->getCode(), $patient_id, TRUE, $pdo);
            $patient = (new PatientDemographDAO())->getPatient($patient_id, FALSE, $pdo);

            if($stmt->rowCount() == 1){
                $booking->setId($pdo->lastInsertId());

                $bil = new Bill();
                $bil->setPatient($booking->getPatient());
                $bil->setDescription("Physiotherapy charges: ".$specialty->getName());

                $bil->setItem($specialty);
                $bil->setSource( (new BillSourceDAO())->findSourceById(19, $pdo) );
                $bil->setTransactionType("credit");
                $bil->setAmount($amount * $count);
                $bil->setDiscounted(NULL);
                $bil->setDiscountedBy(NULL);
                $bil->setClinic(new Clinic(1));
                $bil->setBilledTo($patient->getScheme());
                $bil->setCostCentre(NULL);
//                $bil->setCostCentre( (is_null($booking->getServiceCentre()))? NULL : $booking->getServiceCentre()->getCostCentre() );
                if ( (new BillDAO())->addBill($bil, $count, $pdo) !== null ){
                    $pdo->commit();
                    return $booking;
                }
            }
            $pdo->rollBack();
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function get($id, $pdo=NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM physiotherapy_booking WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $sessions = (new PhysioSessionDAO())->getForBooking($row['id'], $pdo);
                $available = intval($row['count'])  - count($sessions);

                $booking = (new PhysioBooking($row['id']))
                    ->setActive($row['active'])
                    ->setPatient( (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, NULL) )
                    ->setRequestCode($row['requestCode'])
                    ->setSpecialization( (new StaffSpecializationDAO())->get($row['specialization_id'], $pdo))
                    ->setCount($row['count'])
                    ->setBookedBy((new StaffDirectoryDAO())->getStaff($row['booked_by'], FALSE, $pdo))
                    ->setBookingDate($row['booking_date'])
                    ->setSessions( $sessions )
                    ->setAvailable($available);
                return $booking;
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function all($page=0, $pageSize=10, $pdo=NULL){
        $sql = "SELECT * FROM physiotherapy_booking";
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
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql .= " LIMIT $offset, $pageSize";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $bookings = [];
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $bookings[] = $this->get($row['id'], $pdo);
            }
        }catch (PDOException $e){
            errorLog($e);
            $bookings = [];
        }
        $results = (object)null;
        $results->data = $bookings;
        $results->total = $total;
        $results->page = $page;
        return $results;
    }

    function active($page=0, $pageSize=10, $pdo=NULL){
        $sql = "SELECT b.*, (SELECT COUNT(*) FROM physiotherapy_session s WHERE s.booking_id = b.id) AS `sessionsCount`, (`count`- (SELECT COUNT(*) FROM physiotherapy_session s WHERE s.booking_id = b.id)) AS available FROM physiotherapy_booking b HAVING available > 0 AND b.active IS TRUE";
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
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql .= " LIMIT $offset, $pageSize";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $bookings = [];
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $bookings[] = $this->get($row['id'], $pdo);
            }
        }catch (PDOException $e){
            errorLog($e);
            $bookings = [];
        }
        $results = (object)null;
        $results->data = $bookings;
        $results->total = $total;
        $results->page = $page;
        return $results;
    }

    function forPatient($patientId, $bookingId=NULL, $page=0, $pageSize=10, $pdo=NULL){
        $filter = $bookingId != NULL ? " AND id=$bookingId" : "";
        $sql = "SELECT * FROM physiotherapy_booking WHERE patient_id=".$patientId.$filter." ORDER BY booking_date DESC";
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
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql .= " LIMIT $offset, $pageSize";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $bookings = [];
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $bookings[] = $this->get($row['id'], $pdo);
            }
        }catch (PDOException $e){
            errorLog($e);
            $bookings = [];
        }
        $results = (object)null;
        $results->data = $bookings;
        $results->total = $total;
        $results->page = $page;
        return $results;
    }

    function find($filter, $page=0, $pageSize=10, $pdo=NULL){
        $sql = "SELECT * FROM physiotherapy_booking WHERE requestCode LIKE '%$filter%' OR patient_id LIKE '%".$filter."%'";
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
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql .= " LIMIT $offset, $pageSize";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $bookings = [];
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $bookings[] = $this->get($row['id'], $pdo);
            }
        }catch (PDOException $e){
            errorLog($e);
            $bookings = [];
        }
        $results = (object)null;
        $results->data = $bookings;
        $results->total = $total;
        $results->page = $page;
        return $results;
    }

    public function cancel($booking, $pdo=NULL)
    {
        //$booking = new PhysioBooking();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $pdo->beginTransaction();

            $specialization = (new StaffSpecializationDAO())->get($booking->getSpecialization()->getId(), $pdo);

            $sql = "UPDATE physiotherapy_booking SET active = FALSE WHERE id=".$booking->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount()==1){

                $bil = new Bill();
                $bil->setPatient($booking->getPatient());
                $bil->setDescription("".$specialization->getName());

                $amount = (new InsuranceItemsCostDAO())->getItemPriceByCode($specialization->getCode(), $booking->getPatient()->getId(), TRUE, $pdo);

                $bil->setItem($booking->getSpecialization());
                $bil->setSource( (new BillSourceDAO())->findSourceById(19, $pdo) );
                $bil->setTransactionType("reversal");
                $bil->setDueDate($booking->getBookingDate());
                $bil->setAmount(0-($amount * $booking->getAvailable()));
                $bil->setInPatient(NULL);
                $bil->setDiscounted(NULL);
                $bil->setDiscountedBy(NULL);
                $bil->setClinic(new Clinic(1));
                $bil->setBilledTo($booking->getPatient()->getScheme());
                $bil->setReferral(NULL);
                $bil->setCostCentre( NULL );

                $bill = (new BillDAO())->addBill($bil, 1, $pdo);

                if($bill){
                    $pdo->commit();
                    return true;
                }
                $pdo->rollBack();
                return false;
            }
            return false;
        } catch (PDOException $e) {
            errorLog($e);
            return false;
        }
    }
}