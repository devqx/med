<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/6/16
 * Time: 8:21 AM
 */

include($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management: Consultant";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/report.consultant.php";
$script_block = <<<EOF
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';
