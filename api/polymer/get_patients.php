<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/24/16
 * Time: 6:33 PM
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Content-Type: application/json');

include_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';

$patients = array();
$case = isset($_GET['q'])? $_GET['q'] : 'demograph';
switch($case){
    case 'demograph':
        $patients_ = (new PatientDemographDAO())->getAllFemalePatientsMin(NULL, TRUE);
        foreach($patients_ as $patient){
            $pat = (object)null;

            $pat->PatientId = (int)$patient->getId();
            $pat->EMRId = $patient->getId();
            $pat->FirstName = $patient->getFname();
            $pat->MiddleName =$patient->getMname();
            $pat->LastName = $patient->getLname();
            $pat->FullName = $patient->getFullname();
            $pat->DateOfBirth = $patient->getDateOfBirth();
            $pat->Age = $patient->getAge();
            $pat->Sex = $patient->getSex();
            $pat->PhoneNumber = $patient->getPhoneNumber();
            $pat->BloodGroup = $patient->getBloodGroup();

            $patients[] = $pat;
        }
        break;

    case 'antenatal':
        $patients = (new AntenatalEnrollmentDAO())->allMin();
        //error_log(json_encode($patients));
        break;
}

exit(json_encode($patients));