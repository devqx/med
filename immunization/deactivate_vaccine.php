<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/2/16
 * Time: 5:15 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.pshp';

function deactivateDueVaccineForPatient($pid,  $id){
	$protect = new Protect();
	$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID']);
	if (!$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->records)) {
		return $protect->ACCESS_DENIED;
	}
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	$pdo = (new MyDBConnector())->getPDO();
	$sql = "UPDATE `patient_vaccine`  SET `status` = 0  WHERE `patient_id` = " . $pid . " AND id = $id";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
}

?>