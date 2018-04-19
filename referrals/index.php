<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/28/15
 * Time: 9:43 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';

$extra_link = array('title'=>'Referrals','link'=>'/referrals/');
//$title = "Referrals";
$page = "referrals.php";
$script_block = <<<EOF
$(document).ready(function () {
$('table.table.table-striped').dataTable();
});
EOF;
include $_SERVER['DOCUMENT_ROOT']."/template.inc.in.php";