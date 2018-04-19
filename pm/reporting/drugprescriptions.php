<?php
/**
 * Created by PhpStorm.
 * User: pauldic
 * Date: 1/19/15
 * Time: 2:03 PM
 */

include($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management: Drug Prescriptions";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/drugPrescriptions.php";

$script_block = <<<EOF
\$(document).ready(function(){

});
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';