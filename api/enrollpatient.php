<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/24/14
 * Time: 5:36 PM
 */


if(isset($_POST['type']) && $_POST['type'] == "labour"){
    if(!isset($_SESSION)){session_start();}
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabourEnrollment.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabourEnrollmentDAO.php';

    $en = new LabourEnrollment();
    $en->setPatient( new PatientDemograph($_POST['pid']) );
    $en->setEnrolledAt( new Clinic(1) );
    $en->setEnrolledBy( new StaffDirectory($_SESSION['staffID']) );
    $en->setEnrolledOn( date("Y-m-d H:i:s", time()) );

    exit(json_encode( (new LabourEnrollmentDAO())->add($en) ));
}

sleep(1);
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.patient.php';
$manager = new Manager();
$ret = '';
foreach($_POST['patients'] as $patient){
    $ret .= $manager->enrollPatientToProgram($patient, 'hiv');
}

if($ret != ''){
    exit("error:Enrollment failed");
}else {
    exit("ok");
}
