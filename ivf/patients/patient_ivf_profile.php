<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/17/16
 * Time: 2:08 PM
 */



include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if (isset($_GET['view']) && $_GET['view'] == "treatments") {
	include '../profile/tabs/treatments.php';
} else if (isset($_GET['view']) && $_GET['view'] == "labs") {

} else if (isset($_GET['view']) && $_GET['view'] == "labs") {

}
$script_block = <<<EOF
$(document).ready(function(){

});
EOF;

$page = $_SERVER['DOCUMENT_ROOT'] . '/ivf/profile/patient_profile_inc.php';
$extra_link = array("title" => "IVF Patients", "link" => ".");
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';