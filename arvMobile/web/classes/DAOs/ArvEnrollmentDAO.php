<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/15/16
 * Time: 4:11 PM
 */
class ArvEnrollmentDAO
{
    private $conn = null;

    public function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/arvMobile/web/classes/PriorART.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/arvMobile/web/classes/ArvEnrollment.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/PriorARTDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/arvMobile/web/classes/ModeOfTest.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/ModeOfTestDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/arvMobile/web/classes/CareEntryPoint.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/CareEntryPointDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
            if (!isset($_SESSION)) session_start();
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($enrollment, $pdo=NULL){
        $active = $enrollment->getActive();
        $patientId = $enrollment->getPatient()->getId();
        $uniqueId = $enrollment->getUniqueId();
        $careEntryPoint = $enrollment->getCareEntryPoint()->getId();
        $dateHivConfirmed = $enrollment->getDateHivConfirmed();
        $modeOfTest = $enrollment->getModeOfTest()->getId();
        $locationOfTest = escape($enrollment->getLocationOfTest());
        $priorArt = $enrollment->getPriorART()->getId();
        $enrolledOn = $enrollment->getEnrolledOn();
        $enrolledAt = $enrollment->getEnrolledAt()->getId();
        $enrolledBy = $enrollment->getEnrolledBy()->getId();
        $createDate = $enrollment->getCreateDate();

        $sql = "INSERT INTO `enrollments_sti`(`active`, `patient_id`, `unique_id`, `care_entry_point_id`, `date_hiv_confirmed`, `mode_of_test_id`, `location_of_test`, `prior_art_id`, `enrolled_on`, `enrolled_at`, `enrolled_by_id`, `create_date`) VALUES ($active,$patientId,'$uniqueId',$careEntryPoint,'$dateHivConfirmed',$modeOfTest,'$locationOfTest',$priorArt,'$enrolledOn',$enrolledAt,$enrolledBy,'$createDate')";

        try {
            $pdo = $pdo==NULL? $this->conn->getPDO(): $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount()==1){
                $enrollment->setId($pdo->lastInsertId());
                return $enrollment;
            }
            return null;
        } catch(PDOException $e){
            errorLog($e);
            return null;
        }
    }

    function get($id, $pdo=null){
        try {
            $sql = "SELECT * FROM enrollments_sti WHERE id=$id";
            $pdo = $pdo==NULL? $this->conn->getPDO(): $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $enrollment = (new ArvEnrollment($row['id']))
                    ->setActive((bool)$row['active'])
                    ->setPatient( (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo) )
                    ->setUniqueId($row['unique_id'])
                    ->setCareEntryPoint( (new CareEntryPointDAO())->get($row['care_entry_point_id'], $pdo) )
                    ->setDateHivConfirmed($row['date_hiv_confirmed'])
                    ->setModeOfTest( (new ModeOfTestDAO())->get($row['mode_of_test_id'], $pdo) )
                    ->setLocationOfTest($row['location_of_test'])
                    ->setPriorART( (new PriorARTDAO())->get($row['prior_art_id'], $pdo) )
                    ->setEnrolledOn($row['enrolled_on'])
                    ->setEnrolledAt($row['enrolled_at'])
                    ->setEnrolledBy( (new StaffDirectoryDAO())->getStaff($row['enrolled_by_id'], FALSE, $pdo) )
                    ->setCreateDate($row['create_date']);

                return $enrollment;
            }
            return null;
        }catch (PDOException $e){
            errorLog($e);
            return null;
        }
    }

    function all($pdo=null){
        $data = [];
        try {
            $sql = "SELECT * FROM enrollments_sti";
            $pdo = $pdo==NULL? $this->conn->getPDO(): $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $data[] =  $this->get($row['id'], $pdo);
            }
            return $data;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }

    function allActive($pdo=null){
        $data = [];
        try {
            $sql = "SELECT * FROM enrollments_sti WHERE active IS TRUE";
            $pdo = $pdo==NULL? $this->conn->getPDO(): $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $data[] =  $this->get($row['id'], $pdo);
            }
            return $data;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }

    function isEnrolled($pid, $pdo=NULL){
        try {
            $sql = "SELECT * FROM enrollments_sti WHERE patient_id=$pid";
            $pdo = $pdo==NULL? $this->conn->getPDO(): $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount()==1) {
                return TRUE;
            }
            return FALSE;
        }catch (PDOException $e){
            errorLog($e);
            return FALSE;
        }
    }
}