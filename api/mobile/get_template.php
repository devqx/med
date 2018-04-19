<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 5/12/17
 * Time: 12:55 PM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $templates = null;
    header("Access-Control-Allow-Origin:*");
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ExamTemplateDAO.php';
    if (isset($_POST['inpatient_id']) && !isset($_POST['temp_id'])) {
        $templates = (new ExamTemplateDAO())->all();
    }elseif (isset($_POST['inpatient_id']) && isset($_POST['temp_id'])){
        $templates = (new ExamTemplateDAO())->getTemplate($_POST['temp_id']);
    }

    echo  json_encode($templates, JSON_PARTIAL_OUTPUT_ON_ERROR);
}


