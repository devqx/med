<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/3/15
 * Time: 4:54 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
$appointmentLimit = AppointmentDAO::$maxAppointmentsCount;
$appointmentCount = (new AppointmentDAO())->getNumAppointments($_GET['staff_id'], date("Y-m-d"));
if( $appointmentCount > $appointmentLimit ){
    $return = new stdClass();
    $return->message = "Selected staff has $appointmentCount appointments already";
    exit(json_encode($return));
}