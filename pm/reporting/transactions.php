<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/14/15
 * Time: 11:47 AM
 */

include_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/StaffDirectoryDAO.php';
$protect = new Protect();
$staffId = @$_SESSION['staffID'] || @$_COOKIE['staffID'];
$this_user = (new StaffDirectoryDAO())->getStaff($staffId);
if(!$this_user->hasRole($protect->cashier) && !$this_user->hasRole($protect->bill_auditor)){
	exit($protect->ACCESS_DENIED);
}
$title ="Practice Management: Transactions";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/transactions.php";

$script_block = <<<EOF
\$(document).ready(function(){

});
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';