<?php
include_once "protect.php";
if(!isset($_SESSION)){
    session_start();}

$page = "pages/specialty_modules.php";
include_once "template.inc.in.php";
