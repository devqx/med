<?php

@session_start();

if(!isset($_SESSION['staffID'])){
	//the session id = $_SERVER['HTTP_COOKIE'], which returns something like `PHPSESSID=s59tfgs206a9e068osdts1ab10`
	$session_id = explode('=', $_SERVER['HTTP_COOKIE'])[1];
	//remove this from the database then
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->prepare("DELETE FROM onlinestatus WHERE session_id = '$session_id'", array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL))->execute();
	unset($_SESSION['staffID']);
	return json_encode(null);
}

$count = new stdClass();
$count->mail = 0;
$count->notification = 0;
$count->queue = 0;
$count->aqueue = 0;
$count->appointment = 0;
$count->referral = 0;
exit(json_encode($count, JSON_PARTIAL_OUTPUT_ON_ERROR));


require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
$counts = (new PatientQueueDAO())->counter();
exit(json_encode($counts, JSON_PARTIAL_OUTPUT_ON_ERROR));





require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ApprovedQueueDAO.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
$counter = [];
if(isset($_SESSION['staffID'])){
$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], FALSE);
$department_id = ($staff->getDepartment()!==NULL)?$staff->getDepartment()->getId():'null';
    $appCounter = (new AppointmentDAO())->countAppointmentByDate(date("Y-m-d"), date("Y-m-d"), ["active", "scheduled"], $department_id);
    $counter['mail'] = 0;
    $counter['notification'] = 0;
    $counter['queue'] = (int)(new PatientQueueDAO())->countQueueByDate(date("Y-m-d"), date("Y-m-d"), ['active', 'blocked'], $department_id);
    $counter['aqueue'] = (int)(new ApprovedQueueDAO())->countQueue();
    $counter['appointment'] = (int)$appCounter;
    $counter['referral'] = 0;
}else {
    $counter['mail'] = 0;
    $counter['notification'] = 0;
    $counter['queue'] = 0;
    $counter['aqueue'] = 0;
    $counter['appointment'] = 0;
    $counter['referral'] = 0;
}

echo json_encode($counter);

