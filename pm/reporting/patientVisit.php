<?php
include($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management: Reporting";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/patientVisit.php";
$script_block = <<<EOF
\$(document).ready(function(){

});
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';
