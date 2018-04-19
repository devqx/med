<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/14/16
 * Time: 1:21 PM
 */
include($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management: Reporting";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/dispensedDrugs.php";
$script_block = <<<EOF
\$(document).ready(function(){

});
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';