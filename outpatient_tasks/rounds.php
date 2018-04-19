<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/17/15
 * Time: 4:13 PM
 */
@session_start();
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/func.php';

$_SESSION['op_tasks_page'] = 1;
$_GET['outpatient']="true";
include_once $_SERVER['DOCUMENT_ROOT'] . '/admissions/homeTabs/wardRounds.php';