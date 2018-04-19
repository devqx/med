<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/12/16
 * Time: 10:27 AM
 */

header("Access-Control-Allow-Origin: *");
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
$patients = [];
if (isset($_REQUEST['q'])) {
    $patients = (new AppointmentDAO())->getPatientsInAppointments(date("Y-m-d"), date("Y-m-d"), ['scheduled', 'active'], [], FALSE, $page=0, $pageSize=9999999, $_REQUEST['q']);
    exit(json_encode($patients));
}