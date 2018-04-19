<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/28/16
 * Time: 3:23 PM
 */

include ($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management: Reports";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/unfulfilledLabs.php";
$script_block = <<<EOF
\$(document).ready(function(){
});
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';