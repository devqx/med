<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . "/protect.php";
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabTemplateDataDAO.php';

    $labTempData=(new LabTemplateDataDAO())->getLabTemplateData($_REQUEST['ltid'], TRUE);
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
    header('Content-Type: application/json');
    $data = json_encode($labTempData);
    if(!isset($_GET['suppress'])){
        echo $data;
    }
}
 echo (json_encode($labTempData));
    ?>
