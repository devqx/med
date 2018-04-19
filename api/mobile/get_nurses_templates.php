<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 5/18/17
 * Time: 9:33 AM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $templates = null;
    header("Access-Control-Allow-Origin:*");
//    if(isset($_POST['patient_id']) && isset($_POST['inpatient_id'])){
        require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/NursingTemplateDAO.php';
        require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
        $templates = (new NursingTemplateDAO())->all();
        if(!is_blank($_POST['temp_id'])){
            $templates = (new NursingTemplateDAO())->get($_POST['temp_id']);
        }
//    }
    echo json_encode($templates, JSON_PARTIAL_OUTPUT_ON_ERROR);
}