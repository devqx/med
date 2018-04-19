<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';

$dao = new ClinicDAO();
$clinics = $dao->getClinics();
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {  //for Ajax
    header('Content-Type: application/json');
    $data = json_encode($clinics);
    echo $data;
    exit;
}
