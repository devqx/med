<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 5/15/17
 * Time: 1:51 PM
 */

if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array)json_decode(trim(file_get_contents('php://input')), true));
    $tasks = null;
    header("Access-Control-Allow-Origin:*");
    if (isset($_POST['patient_id']) && isset($_POST['inpatient_id'])) {
        require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskChartDAO.php';
        require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
        require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTaskChart.php';
        require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InPatient.php';
        $ctc = new ClinicalTaskChart();
        $ctc->setPatient(new PatientDemograph($_POST['patient_id']));
        $ctc->setInPatient((!isset($_POST['inpatient_id'])) ? null : new InPatient($_POST['inpatient_id']));

        $tasks = (new ClinicalTaskChartDAO())->all($ctc, $page=0, $pageSize=99999999, $task_type=null);
    }

    echo json_encode($tasks, JSON_PARTIAL_OUTPUT_ON_ERROR);
}