<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 5/1/17
 * Time: 4:04 AM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $progressNote = null;
    header("Access-Control-Allow-Origin:*");
    if (isset($_POST['inPatient_id'])) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProgressNoteDAO.php';
        $progressNote = (new ProgressNoteDAO())->getProgressNoteMobile($_POST['inPatient_id'], true);

    }
    echo  json_encode($progressNote, JSON_PARTIAL_OUTPUT_ON_ERROR);
}