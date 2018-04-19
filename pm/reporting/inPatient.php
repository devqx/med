<?php
include($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management: In Patient";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/inPatient.php";
$script_block = <<<EOF
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';
