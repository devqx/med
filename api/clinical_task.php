<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';

if(!isset($_SESSION)){@session_start();}
//error_log(json_encode($_REQUEST));
$full = (bool)isset($_REQUEST['full']);
if (isset($_REQUEST['q'])) {
//    $ctData = (new ClinicalTaskDataDAO())->getInPatientsWithBed($_REQUEST['q'], $full);
} else if (isset($_REQUEST['ctdid'])) {
    $ctDatum = (new ClinicalTaskDataDAO())->getClinicalTaskDatum($_REQUEST['ctdid'], $full);
} else if (isset($_REQUEST['active'])) {
    $ctData = (new ClinicalTaskDataDAO())->getAllClinicalTaskData([$_REQUEST['active']], $full);
} else if (isset($_REQUEST['ward'])) {
    $ctData = (new ClinicalTaskDataDAO())->getInPatientsWithBed(!($_REQUEST['withoutBed']==='true'), $full);
} else if (isset($_REQUEST['myPatient'])) {
    $ctData = (new ClinicalTaskDataDAO())->getMyInPatient($_SESSION['staffID'], $full);
} else if (isset($_REQUEST['inbound'])) {
    $ctData = (new ClinicalTaskDataDAO())->getInboundInPatients($full);
} else if (isset($_REQUEST['all'])) {
    $ctData = (new ClinicalTaskDataDAO())->getInPatients("*", $full);
} else if (isset($_REQUEST['history'])) {
    $ctData = (new ClinicalTaskDataDAO())->getClinicalTaskData(FALSE, $full);
} else {
    $ctData = (new ClinicalTaskDataDAO())->getInPatients($full);
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
    header('Content-Type: application/json');
    if (isset($_REQUEST['ctdid'])) {
        $data = json_encode($ctDatum);
    } else {
        $data = json_encode($ctData);
    }
    if (!isset($_GET['suppress'])) {
        echo $data;
    }
} else {
    if (isset($_REQUEST['ctdid'])) {
        $data = json_encode($ctDatum);
    }else{
        $data = json_encode($ctData);
    }

    if (!isset($_GET['suppress'])) {
        echo $data;
    }
}
?>
