<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentGroupDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';

    $appointableTypes=getTypeOptions('type', 'appointment_group');
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
    header('Content-Type: application/json');
    $data = json_encode($appointableTypes);
    if(!isset($_GET['suppress'])){
        echo $data;
    }
}
    ?>
