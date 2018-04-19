<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
$protect = new Protect();
@session_start();

$script_block = <<<EOF
\$(document).ready(function(){
});
EOF;
$page = "pages/appointments/index.php";
include "../template.inc.in.php";
