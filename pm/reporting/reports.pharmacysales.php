<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 11/17/15
 * Time: 12:30 PM
 */
include($_SERVER['DOCUMENT_ROOT'].'/protect.php');
if(!isset($_SESSION)){session_start();}

$title ="Practice Management: Pharmacy Sales Report";
$script_block = <<<EOF
\$(document).ready(function(){
});
EOF;
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/pharmacysales.php";
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';

