<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/29/16
 * Time: 9:31 AM
 */

include ($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management: Reports";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/unfulfilledPrescriptions.php";
$script_block = <<<EOF
\$(document).ready(function(){
});
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';