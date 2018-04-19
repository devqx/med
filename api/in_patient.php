<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';

if(!isset($_SESSION)){@session_start();}
$full = (bool)isset($_REQUEST['full']);
if (isset($_REQUEST['q'])) {
    $inPatients = (new InPatientDAO())->getInPatientsWithBed($_REQUEST['q'], $full);
} else if (isset($_REQUEST['ipid'])) {
    $inPatient = (new InPatientDAO())->getInPatient($_REQUEST['ipid'], $full);
} else if (isset($_REQUEST['withoutBed'])) {
    $inPatients = (new InPatientDAO())->getInPatientsWithBed(!($_REQUEST['withoutBed']==='true'), $full);
} else if (isset($_REQUEST['ward'])) {
    $inPatients = (new InPatientDAO())->getInPatientsWithBed(!($_REQUEST['withoutBed']==='true'), $full);
} else if (isset($_REQUEST['myPatient'])) {
    $inPatients = (new InPatientDAO())->getMyInPatient($_SESSION['staffID'], $full);
} else if (isset($_REQUEST['inbound'])) {
    $inPatients = (new InPatientDAO())->getInboundInPatients($full);
} else if (isset($_REQUEST['all'])) {
    $inPatients = (new InPatientDAO())->getInPatients("*", $full);
} else if (isset($_REQUEST['history'])) {
    $inPatients = (new InPatientDAO())->getInPatients(FALSE, $full);
} else {
    $inPatients = (new InPatientDAO())->getInPatients($full);
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
    header('Content-Type: application/json');
    if (isset($_REQUEST['ipid'])) {
        $data = json_encode($inPatient);
    } else {
        $data = json_encode($inPatients);
    }
    if (!isset($_GET['suppress'])) {
        echo $data;
    }
} else {
    if (isset($_REQUEST['ipid'])) {
        $data = json_encode($inPatient);
    }else{
        $data = json_encode($inPatients);
    }

    if (!isset($_GET['suppress'])) {
        echo $data;
    }
}
