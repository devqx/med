<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/21/15
 * Time: 11:22 AM
 */

include($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management: Referrals";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/referrals.php";
$script_block = <<<EOF
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';
