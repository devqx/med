<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/20/16
 * Time: 10:07 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';

$scan = (new PatientScanDAO())->getScan($_POST['id']);
$scan->setCapturedBy(( new StaffDirectoryDAO()) ->getStaff($_SESSION['staffID'], FALSE));
$scan->setCapturedDate(date("Y-m-d H:i:s"));
$sc = (new PatientScanDAO())->capturedScan($scan);
