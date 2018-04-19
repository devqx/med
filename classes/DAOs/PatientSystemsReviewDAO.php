<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/18/15
 * Time: 5:24 PM
 */

class PatientSystemsReviewDAO {
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PatientSystemsReview.php';
            require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function add($review, $pdo=NULL){
//        $review = new PatientSystemsReview();
        $patient_id = $review->getPatient()->getId();
        $date = date("Y-m-d H:i:s");
        $systems_review_id = $review->getSystemsReview()->getId();
        $reviewer_id = $review->getReviewer()->getId();
        $assessmentInstanceId = $review->getAssessmentInstance() != NULL ? $review->getAssessmentInstance()->getId() : "NULL";
        $antenatalInstanceId = $review->getAssessmentInstance() != NULL ? $review->getAssessmentInstance()->getId() : "NULL";
        $type = ($review->getType() != NULL && !is_blank($review->getType()) ? "'".$review->getType()."'": "NULL");
        $encounter = $review->getEncounter() ? $review->getEncounter()->getId() : "NULL";
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $canCommit = !$pdo->inTransaction();
            try {
                $pdo->beginTransaction();
            } catch (PDOException $ef){
                //
            }
            $sql = "INSERT INTO patient_systems_review (patient_id, `date`, systems_review_id, reviewer_id, assessment_id, antenatal_instance_id, type, encounter_id ) VALUES ($patient_id, '$date', $systems_review_id, $reviewer_id, $assessmentInstanceId,$antenatalInstanceId,  $type, $encounter)";

            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($stmt->rowCount() == 1){
                $review->setId($pdo->lastInsertId());
                if($canCommit){
                    $pdo->commit();
                }
                return $review;
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }
}