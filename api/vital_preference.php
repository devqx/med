<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/2/17
 * Time: 12:18 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientVitalPreferenceDAO.php';
if((new PatientVitalPreferenceDAO())->forPatientType($_POST['pid'], $_POST['type'])->delete()){
	exit(json_encode(true));
}
exit(json_encode(false));