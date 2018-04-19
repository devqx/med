<?php
include($_SERVER['DOCUMENT_ROOT'].'/protect.php');
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
$title ="Practice Management: Transactions Report";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/bill.php";
if(!$this_user->hasRole($protect->bill_auditor )){
	exit($protect->ACCESS_DENIED);
}
$script_block = <<<EOF
\$(document).ready(function(){
});
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';
