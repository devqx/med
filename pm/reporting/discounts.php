<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/21/15
 * Time: 12:42 PM
 */

include_once($_SERVER['DOCUMENT_ROOT'].'/protect.php');
$protect = new Protect();
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/StaffDirectoryDAO.php';
$staffId = @$_SESSION['staffID'] || @$_COOKIE['staffID'];
$this_user = (new StaffDirectoryDAO())->getStaff($staffId);
if(!$this_user->hasRole($protect->bill_auditor )){
	exit($protect->ACCESS_DENIED);
}
$title ="Practice Management: Discounts";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/discounts.php";

$script_block = <<<EOF
\$(document).ready(function(){

});
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';