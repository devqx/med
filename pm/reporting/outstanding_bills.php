<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/21/16
 * Time: 9:06 PM
 */
include_once($_SERVER['DOCUMENT_ROOT'].'/protect.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/StaffDirectoryDAO.php');
$protect = new Protect();
$staffId = @$_SESSION['staffID'] || @$_COOKIE['staffID'];
$this_user = (new StaffDirectoryDAO())->getStaff($staffId);
if(!$this_user->hasRole($protect->bill_auditor )){
	exit($protect->ACCESS_DENIED);
}
$title ="Practice Management: Outstanding Bills Report";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/outstanding_bills.php";
$script_block = <<<EOF
\$(document).ready(function(){
});
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';