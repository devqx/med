<?php
include($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management: Visit/Enrollment Report";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/report.visit.php";
$script_block = <<<EOF
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';