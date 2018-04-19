<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/17/16
 * Time: 3:31 PM
 */
@session_start();
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Content-Type: application/json');

$_SESSION['staffID'] = (isset($_SESSION['staffID']))? $_SESSION['staffID'] : '';
include $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
$patient = new Manager();
$search = isset($_POST['q']) && $_POST['q']!="" ? $_POST['q']: "";
$patients = $patient->doFindPatient($search, 'labour');
exit(json_encode($patients));