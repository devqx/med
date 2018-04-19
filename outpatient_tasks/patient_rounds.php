<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/17/15
 * Time: 4:45 PM
 */

$pid = $_REQUEST['pid'];
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';


require_once $_SERVER['DOCUMENT_ROOT'] .'/admissions/patientTabs/clinicalTask.php';