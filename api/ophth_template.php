<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . "/protect.php";
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyTemplateDAO.php';

$full=  isset($_REQUEST['full'])? TRUE:FALSE;
if(isset($_REQUEST['id'])){
    exit(json_encode((new OphthalmologyTemplateDAO())->getTemplate($_REQUEST['id'])));
}
$labTemps=(new OphthalmologyTemplateDAO())->getTemplates($full);
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
    header('Content-Type: application/json');
    $data = json_encode($labTemps);
    if(!isset($_GET['suppress'])){
        echo $data;
    }
}
// echo (json_encode($labTemps));
