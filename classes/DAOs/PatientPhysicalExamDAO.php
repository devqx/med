<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/18/15
 * Time: 5:24 PM
 */

class PatientPhysicalExamDAO {

    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PatientPhysicalExam.php';
            require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function add($review, $pdo=NULL){
//        $review = new PatientPhysicalExam();
        $patient_id = $review->getPatient()->getId();
        $date = date("Y-m-d H:i:s");
        $systems_review_id = $review->getPhysicalExamination()->getId();
        $reviewer_id = $review->getReviewer()->getId();
        $encounter = $review->getEncounter() ? $review->getEncounter()->getId() : "NULL";

        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO patient_physical_examination (patient_id, `date`, physical_examination_id, reviewer_id, encounter_id ) VALUES ($patient_id, '$date', $systems_review_id, $reviewer_id, $encounter)";
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($stmt->rowCount() == 1){
                $review->setId($pdo->lastInsertId());
                return $review;
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }
}