<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/13/18
 * Time: 1:31 PM
 */

if (!isset($_SESSION)) {
	@session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BedDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/StaffDirectory.php";

if (isset($_POST['action']) && $_POST['action'] == "update") {
	$BedDAO = new BedDAO();
	$bed = $BedDAO->getBed($_POST['id']);
	 $bed->setAvailable(TRUE);
	$status = $BedDAO->updateBed($bed);
	ob_clean();
	
	if ($status === true) {
		exit("ok");
	} else {
		//exit(json_encode($status));
		exit('error');
	}//shouldn't we return the result from cancelLab?
}
exit("error");