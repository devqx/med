<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/5/16
 * Time: 6:44 PM
 */

include($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management: Procedures";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/procedures.php";
$script_block = <<<EOF
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';