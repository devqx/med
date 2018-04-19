<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/1/16
 * Time: 2:57 PM
 */
if(!isset($_SESSION)){@session_start();}
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';

$ps = (new PatientScanDAO())->getScan($_POST['id']);
$ps->setRequestedBy( new StaffDirectory($_SESSION['staffID']) );
$ps->setRequestDate(date(MainConfig::$mysqlDateTimeFormat, time()));

$new = (new PatientScanDAO())->addScan($ps, false);
if($new !== null){
	exit("ok");
}
exit("error:Failed to re-order imaging");