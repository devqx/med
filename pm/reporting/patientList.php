<?php
include($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management: Patient List";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/patientList.php";
$script_block = <<<EOF
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';
