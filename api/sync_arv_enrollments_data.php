<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/24/16
 * Time: 9:41 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/PriorARTDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/ModeOfTestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/CareEntryPointDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/ArvEnrollmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/arvMobile/web/classes/ArvEnrollment.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';

$data = $_POST['formData'];
$enrollment = (new ArvEnrollment())
    ->setPatient( new PatientDemograph($data['patientId']) )
    ->setUniqueId($data['uniqueID'])
    ->setEnrolledOn($data['dateEnrolledIntoCare'])
    ->setDateHivConfirmed($data['dateHIVConfirmedTest'])
    ->setLocationOfTest($data['locationOfTest'])
    ->setModeOfTest( (new ModeOfTestDAO())->getByName($data['modeOfTest']) )
    ->setPriorART( (new PriorARTDAO())->getByName($data['priorART']) )
    ->setCreateDate($data['dateEnrolled'])
    ->setActive($data['active'])
    ->setCareEntryPoint((new CareEntryPointDAO())->get($data['careEntryPoint']))
    ->setEnrolledAt( new Clinic(1) )
    ->setEnrolledBy(new StaffDirectory($_SESSION['staffID']));
$response = (object)null;
if((new ArvEnrollmentDAO())->add($enrollment)){
    $response->status = "success";
    $response->message = "Enrollment synced successfully";
} else {
    $response->status = "error";
    $response->message = "Failed to create enrollment record on server";
}
exit(json_encode($response));