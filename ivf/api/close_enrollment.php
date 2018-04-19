<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/18/16
 * Time: 3:19 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IVFEnrollmentDAO.php';
@session_start();
$enrolment = (new IVFEnrollmentDAO())->get($_POST['id'], FALSE)->setClosedDate(date(MainConfig::$mysqlDateTimeFormat))->setClosedBy(new StaffDirectory($_SESSION['staffID']))->update();
if($enrolment!=null){
	exit(json_encode(true));
}
exit(json_encode(false));
