<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/29/16
 * Time: 4:02 PM
 */
include($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management: Mortality";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/mortality.php";
$script_block = <<<EOF
EOF;
include_once $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';
