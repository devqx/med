<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/11/14
 * Time: 6:12 PM
 */
include($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management: Reporting";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/insurancePatients.php";
$extra_style = array("/assets/dataTables/media/css/jquery.dataTables.min.css");
$extra_script = array("/assets/dataTables/media/js/jquery.dataTables.min.js");

$script_block = <<<EOF
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';