<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 3/10/15
 * Time: 12:10 PM
 */

include($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management: Drug Expiration Report";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/drugreport.php";
$script_block = <<<EOF
\$(document).ready(function(){
});
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';