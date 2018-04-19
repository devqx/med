<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/7/16
 * Time: 12:11 PM
 */
header("Access-Control-Allow-Origin: *");
header('Content-type: application/json');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT');
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Signature.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

if (is_blank($_POST['patient_id'])) {
	exit(json_encode(false));
}

if (is_blank($_FILES['signature'])) {
	exit(json_encode(false));
}

$refinedBlob = (file_get_contents($_FILES['signature']['tmp_name']));

$patient = (new PatientDemographDAO())->getPatient($_POST['patient_id']);

$signature = (new Signature())->setPatient($patient)->setBlob($refinedBlob)->setActive(true)->setDate(date(MainConfig::$mysqlDateTimeFormat))->add();

//exit(json_encode(true));

if ($signature) {
	exit(json_encode(true));
}
exit(json_encode(false));