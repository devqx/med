<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/23/16
 * Time: 11:43 AM
 */
include($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management: Reporting";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/refills.php";
$script_block = <<<EOF
\$(document).ready(function(){

});
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';