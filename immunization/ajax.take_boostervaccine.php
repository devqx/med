<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/5/15
 * Time: 5:21 PM
 */

@session_start();
$return = array();

require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/PatientVaccineBooster.php';
require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/VaccineBoosterHistory.php';
require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/DAOs/VaccineBoosterDAO.php';
require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/DAOs/PatientVaccineBoosterDAO.php';
require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/DAOs/VaccineBoosterHistoryDAO.php';

$patient_vb_id =  isset($_POST['b']) ? $_POST['b'] : '';
if($patient_vb_id == ''){
    $return['status'] = "error";
    $return['message'] = "No vaccine selected";
} else {
    $getBoosterId = (new PatientVaccineBoosterDAO())->getPatientVaccineBooster($patient_vb_id)->getVaccineBooster()->getId();
    $getBooster = (new VaccineBoosterDAO())->getVaccineBooster($getBoosterId);
    $interval = $getBooster->getInterval();
    $interval_scale = $getBooster->getIntervalScale();
    $date = date('Y-m-d');
    $next_date = date('Y-m-d', strtotime( '+'.$interval.' '.strtolower($interval_scale.'S'), strtotime($date)));

    $pvb = new PatientVaccineBooster();
    $pvb->setId($patient_vb_id);
    $pvb->setLastTaken(date('Y-m-d'));
    $pvb->setNextDueDate($next_date);
    $takeNow = (new PatientVaccineBoosterDAO())->addLastTaken($pvb);
    if($takeNow){
        $taken_by = $_SESSION['staffID'];
        $date_taken = date('Y-m-d');

        $history = new VaccineBoosterHistory();
        $history->setTakenBy($taken_by);
        $history->setDateTaken($date_taken);
        $history->setPatientVaccineBooster($patient_vb_id);
        $save_history = (new VaccineBoosterHistoryDAO())->addDateTaken($history);

        if($save_history){
            $return['status'] = "success";
            $return['message'] = "vaccine(s) successfully administered";
        }
        else {
            $return['status'] = "error";
            $return['message'] = "No vaccine selected";
        }
    }
    else {
        $return['status'] = "error";
        $return['message'] = "No vaccine selected";
    }
}
exit(json_encode($return));