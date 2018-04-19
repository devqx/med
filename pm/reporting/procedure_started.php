<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 5/2/17
 * Time: 11:10 AM
 */
include($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management: Procedures";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/procedure_started.php";
$script_block = <<<EOF
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';