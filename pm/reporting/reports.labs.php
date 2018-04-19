<?php
include($_SERVER['DOCUMENT_ROOT'].'/protect.php');
if(!isset($_SESSION)){session_start();}

$title ="Practice Management: Labs";
$script_block = <<<EOF
\$(document).ready(function(){
});
EOF;
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/reports.labs.php";
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';