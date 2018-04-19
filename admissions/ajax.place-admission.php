<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/CareTeam.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientCareMember.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Ward.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_staff.php';

$round = null;
if(is_blank($_POST['ward_id'])){
    exit("error:Please select a ward");
}

if (!isset($_POST['reason']) || trim($_POST['reason']) === "") {
    exit('error: Please enter the reason for admission');
}

if (!isset($_POST['careTeam'])) {
    exit('error: You need to select at least one or more Care Teams for this patient');
}

$pat = new PatientDemograph($_POST['pid']);

$pcms = array();
$cts = [];
$cms = [];
foreach ($_POST['careTeam'] as $c) {
    $pcm = new PatientCareMember();
    $pcm->setCreateBy($staff);
    $team = explode("_", $c);
    if ($team[0] === 'staff') {
        $pcm->setCareMember(new StaffDirectory($team[1]));
        $pcm->setType("Member");
    } else {
        $pcm->setCareTeam(new CareTeam($team[1]));
        $pcm->setType("Team");
    }
    
    if (($team[1] === $_POST['primary'])) {
        $pcm->setPrimaryCareType($pcm->getType());
        $priid=explode("_", $_POST['primary']);
        $pcm->setPrimaryCare($priid[0] === 'staff'? new StaffDirectory($priid[1]):new CareTeam($priid[1]));
    }
    
    $pcms[]=$pcm;
}
//$staff = (new StaffDirectoryDAO)->getStaff($_SESSION['staffID'], true);
$adm = new InPatient();
$adm->setPatient($pat);
$adm->setAdmittedBy($staff);
$adm->setReason($_POST['reason']);
$adm->setPatientCareMembers($pcms);
$adm->setAnticipatedDischargeDate(strlen(trim($_POST['anticipatedDischarge'])) === 0 ? NULL : $_POST['anticipatedDischarge']);
$adm->setClinic($staff->getClinic());
$adm->setWard( new Ward($_POST['ward_id']) );

$adm = (new InPatientDAO())->addInPatient($adm);
if ($adm === NULL) {
    exit("error:Admission failed");
} else {
    exit("ok:" . $adm->getId());
}