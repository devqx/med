<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 6/21/17
 * Time: 10:03 PM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $cats = null;
    header("Access-Control-Allow-Origin:*");
    if(isset($_POST['staffId']) && isset($_POST['system_id'])){
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/SystemsReviewCategoryDAO.php";
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/SystemsReviewDAO.php";
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PatientSystemsReviewDAO.php";
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/ProgressNoteDAO.php";
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/PatientSystemsReview.php";
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/PatientDemograph.php";
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/StaffDirectory.php";
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/ProgressNote.php";
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/InPatient.php";
        require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/func.php";
        require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";
        require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
        $pdo = (new MyDBConnector())->getPDO();
        $pdo->beginTransaction();
        if(!is_blank(@$_POST['system_review'])){
            $review_systems = array();
            foreach (@$_POST['system_review'] as $system_review){
             $review_systems[]  = (new SystemsReviewDAO())->get($system_review, $pdo);
                $review = new PatientSystemsReview();
                $review->setDate(date("Y-m-d H:i:s"));
                $review->setPatient(new PatientDemograph($_POST['patient_id']));
                $review->setReviewer(new StaffDirectory($_POST['staffId']));
                $review->setSystemsReview((new SystemsReviewDAO())->get($system_review, $pdo));
                if ((new PatientSystemsReviewDAO())->add($review, $pdo) == null) {
                    $pdo->rollBack();
                    ob_end_clean();
                    exit("error:Couldn't save the systems review");
                }
            }

        if (sizeof($review_systems) > 0) {
            $sort_systems_reviews = array();
            foreach ($review_systems as $s) {
                $sort_systems_reviews[$s->getCategory()->getId()][] = $s;
            }
            unset($s);

            foreach ($sort_systems_reviews as $sort_sr) {
                $system_review = array();
                for ($i = 0; $i < count($sort_sr); $i++) {
                    $system_review[] = $sort_sr[$i]->getName() . " (" . $sort_sr[$i]->getCategory()->getName() . ")";
                }
                $pNote = new ProgressNote();
                $pNote->setInPatient(new InPatient($_POST['inpatient_id']));
                $pNote->setValue(null);
                $pNote->setNote(implode(', ', $system_review));
                $pNote->setNoteType('revw');
                $pNote->setNotedBy(new StaffDirectory($_POST['staffId']));
                if ((new ProgressNoteDAO())->add($pNote, $pdo) == null) {
                    $pdo->rollBack();
                    ob_end_clean();
                    exit("error:Failed to save systems review summary");
                }
            }
            unset($sort_sr);
        }
        }
        $pdo->commit();
        exit("success:Saved successfully");

    }
}