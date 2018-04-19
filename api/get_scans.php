<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/17/14
 * Time: 1:18 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';


if (isset($_GET['search'])) {
	$scans = (new ScanDAO())->findScans($_GET['search']);
} else if (isset($_REQUEST['ids'])) {
	$scans = (new ScanDAO())->getScansByIds($_REQUEST['ids']);
} else {
	$scans = (new ScanDAO())->getScans();
}
exit(json_encode($scans, JSON_PARTIAL_OUTPUT_ON_ERROR));