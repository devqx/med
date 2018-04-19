<?php
include('protect.php');
$extra_link = array("title"=>"Staff ","link"=>"/staff_find.php");
//$title ="Staff Profile";
$page = "pages/staff_profile.php";
$extra_style = ["/style/staff.css"];
$script_block = <<<EOF
EOF;
include 'template.inc.in.php';