<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/21/16
 * Time: 10:38 AM
 */
class ArvConsultingDAO
{
    private $conn = null;

    public function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/arvMobile/web/classes/ArvConsulting.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/arvMobile/web/classes/ArvConsultingData.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/PriorARTDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
            if (!isset($_SESSION)) session_start();
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($data, $pdo=NULL){
        // $data = new ArvConsulting();
        $patientId = $data->getPatient()->getId();
        $comment = escape($data->getComment());
        $staff = $data->getCreateUser()->getId();
        $nextAppointment = $data->getNextAppointment();

        $sql = "INSERT INTO arv_consulting (patient_id, `comment`, create_user_id, create_time, next_appointment) VALUES ($patientId, '$comment', $staff, NOW(), '$nextAppointment')";
        try {
            $pdo = $pdo==NULL? $this->conn->getPDO(): $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount()==1){
                $data->setId($pdo->lastInsertId());
                
                foreach ($data->getData() as $datum){
                    // $datum  = new ArvConsultingData();
                    $datum->setArvConsulting( $data );
                    (new ArvConsultingDataDAO())->add($datum, $pdo);
                }
                return $data;
            }
            return null;
        }
        catch (PDOException $e){
            errorLog($e);
            return null;
        }
    }
}