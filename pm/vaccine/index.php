<?php
include($_SERVER['DOCUMENT_ROOT'].'/protect.php');

$title ="Practice Management";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/vaccine/index.php";
$script_block = <<<EOF
\$(document).ready(function(){
  \$('.boxy').boxy();
});
function start(){
}
function finished(s){
	//not useful
}
EOF;
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';