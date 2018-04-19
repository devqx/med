<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/16/15
 * Time: 2:59 PM
 */

header("Access-Control-Allow-Origin: *");
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
sleep(0);
$patient = (new PatientDemographDAO())->getPatientMin($_REQUEST['pid'], NULL, NULL);
exit(json_encode($patient, JSON_PARTIAL_OUTPUT_ON_ERROR));