<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VaccineDAO.php';
$vaccines=(new VaccineDAO())->getVaccines(FALSE);
//if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
//    header('Content-Type: application/json');
    $data = json_encode($vaccines);
    echo $data;
    exit();
//}