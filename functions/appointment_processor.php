<?php
require_once $_SERVER ['DOCUMENT_ROOT'] . "/protect.php";
if (!isset($_SESSION)) {
	@session_start();
}

if (isset($_REQUEST['createAppointment'])) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = !isset($pdo) ? (new MyDBConnector())->getPDO() : $pdo;
	//$canCommitHere = !$pdo->inTransaction();
	//
	//try {$pdo->beginTransaction();}catch (PDOException $e){}
	
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Resource.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AptClinic.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Appointment.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AppointmentInvitee.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AppointmentGroup.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AppointmentResource.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentGroupDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	$_GET['suppress'] = TRUE;
	//require_once $_SERVER ['DOCUMENT_ROOT'] . '/api/get_staff.php';
	if(isset($_SESSION['staffID'])){
		$staff = (new StaffDirectoryDAO())->getStaff( $_SESSION['staffID'], TRUE, $pdo);
	} else if(isset($_POST['staff_id'])){
		$staff = (new StaffDirectoryDAO())->getStaff( $_POST['staff_id'], TRUE, $pdo);
	} else {
		exit('warn:Sorry, access is denied');
	}
	
	$p = json_decode($_REQUEST['createAppointment']);
	$resources = [];
	
	if(count(@array_filter($p->resource))>0){
		foreach ($p->resource as $r){
			$resources[] =(new AppointmentResource())->setResource(new Resource($r));
		}
	}
	
	$ag = (new AppointmentGroup())
		->setCreator($staff)
		->setDepartment($staff->getDepartment())
		->setClinic($p->clinic ? new AptClinic($p->clinic) : null)
		->setIsAllDay($p->allDay)
		->setResource($resources)
		->setDescription($p->description)
		->setPatient(new PatientDemograph($p->patient));
	$apps = $appInvs = [];
	foreach ($p->sdates as $key => $d) {
		$app = new Appointment();
		$app->setEditor($staff);
		$app->setStartTime($d);
		$app->setEndTime(($p->edates[$key] === null || trim($p->edates[$key]) === "") ? null : $p->edates[$key]);
		$unlimited = (new AppointmentGroupDAO())->checkAppointByClinic($d, $p->clinic, $pdo);
		if(!$unlimited && $p->forced !== true){
			exit('warn:Sorry, Clinic has reached the daily appointment limit');
		}
		
		$apps[] = $app;
	}
	$staffs = count($p->staffs) > 0 ? $p->staffs : [];

	if (count($staffs) > 0 && is_array($staffs)) {
		foreach ($staffs as $s) {
			$ai = new AppointmentInvitee();
			$ai->setStaff(new StaffDirectory($s->id));
			$appInvs[] = $ai;
		}
	}

	$ag->setAppointments($apps);
	$ag->setInvitees($appInvs);

	$appGroup = (new AppointmentGroupDAO())->add($ag, $pdo);
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AppointmentExist.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ResourceUnavailable.php';
	if($appGroup instanceof AppointmentExist){
		exit("error:There's an appointment that is already set for the requested time(s) for one or more of the selected staffs");
	} else if($appGroup instanceof ResourceUnavailable){
		$res = escape($appGroup->getMessage());
		exit("error:$res is not available for the requested time(s)");
	} else if ($appGroup == null) {
		exit ('error:Unable to book appointment.<br>(Maybe another active appointment exists)');
	} else {
		ob_end_clean();
		exit ("success:Appointment booked successfully:" . $appGroup->getId());
	}
}
else if (isset($_REQUEST['cancelAppointment'])) {
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
	if (!isset($_GET['aid'])) {
		exit("error:Something went wrong...");
	}
	$status = (new AppointmentDAO())->setStatus($_GET['aid'], "Cancelled", $_SESSION['staffID']);
	if ($status) {
		echo json_encode("success:Appointment successfully cancelled");
		exit;
	} else {
		echo json_encode("error:Sorry we are unable to cancel this appointment");
		exit;
	}
}
else if (isset($_REQUEST['editAppointment'])) {
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Appointment.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
	$app = new Appointment();
	$app->setId($_GET['aid']);
	$app->setStartTime($_GET['start']);
	if (isset($_GET['end'])) {
		$app->setEndTime(($_GET['end'] === null || trim($_GET['end']) === "") ? null : $_GET['end']);
	}
	$app->setEditor((new StaffDirectoryDAO())->getStaff($_SESSION['staffID']));
	$status = (new AppointmentDAO())->updateAppointment($app);
	if ($status) {
		echo(json_encode("success:Updated successfully"));
		exit;
	} else {
		echo(json_encode("error:Update failed due to some unknown error"));
		exit;
	}
} else if (isset($_REQUEST['getAppointments'])) {
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
	if (isset($_REQUEST['pid'])) {
		$apps = (new AppointmentDAO())->getAppointmentByPatientSlim($_GET['pid'], null, null,['Missed', 'Completed', 'Scheduled', 'Active'], TRUE, $_GET['clinic_id']);
		$events = convertAppointmentToEvent($apps);
		echo json_encode($events);
	} else if (isset($_REQUEST['grouped'])) {
		$apps = (new AppointmentDAO())->getAppointmentByDateGroupedSlim($_GET['start'], $_GET['end'], ['Missed', 'Completed', 'Scheduled', 'Active'], TRUE, $_GET['clinic_id']);
		$events = convertAppointmentGroupToEvent($apps);
		echo json_encode($events);
	} else {
		$apps = (new AppointmentDAO())->getAppointmentByDateSlim($_GET['start'], $_GET['end'], ['Missed', 'Completed', 'Scheduled', 'Active'], [], TRUE);
		$events = convertAppointmentGroupToEvent($apps);
		echo json_encode($events);
	}
} else if (isset($_REQUEST['getStaffAppointments'])) {
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
	$apps = (new AppointmentDAO())->getAppointmentByStaffSlim(@$_REQUEST['staff_id'], null, null, ['Missed', 'Completed', 'Scheduled', 'Active'], TRUE);
	$events = convertAppointmentGroupToEvent($apps);
	//$events = convertAppointmentToEvent($apps);
	echo json_encode($events);
}  else if (isset($_REQUEST['getResourcesAppointments'])) {
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
	$apps = (new AppointmentDAO())->getAppointmentByResourceSlim(@$_REQUEST['resource_id'], null, null, ['Missed', 'Completed', 'Scheduled', 'Active'], TRUE);
	$events = convertAppointmentGroupToEvent($apps);
	//$events = convertAppointmentToEvent($apps);
	echo json_encode($events);
} else {
	echo "Request not supported";
	exit;
}
    

