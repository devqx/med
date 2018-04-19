<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 6/15/15
 * Time: 5:15 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/NurseReportDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';

/*$date = $_REQUEST['date'];
$scheme = $_REQUEST['scheme'];
$type = $_REQUEST['type'];
$views = array();
if($type=='visits'){
    $views = (new NurseReportDAO())->getVisitsByDate($date, $scheme);
}
if($type=='enrollments'){
    $views = (new NurseReportDAO())->getEnrollmentsByDate($date, $scheme);
}*/
$date = @$_REQUEST['date'];
$scheme = @$_REQUEST['scheme'];
$type = @$_REQUEST['type'];
$views = array();
$meta = @$_REQUEST['meta'];
if ($type == 'visits') {
	$views = (new NurseReportDAO())->getVisitsByMeta($meta);
} else if ($type == 'enrollments') {
	$views = (new NurseReportDAO())->getEnrollmentsByMeta($meta);
}
$all_views = array();
if (sizeof($views) > 0) {
	foreach ($views as $d) {
		$view = array();
		
		$view['Date'] = date(MainConfig::$dateFormat, strtotime($d->getDate())) ;
		$view['Patient'] = $d->getPatient()->getId() . ', ' . $d->getPatient()->getFullname();
		$view['Age'] = $d->getPatient()->getAge();
		$view['Phone'] = $d->getPatient()->getPhoneNumber();
		$view['Insurance Program'] = $d->getScheme()->getName();
		
		$all_views[] = $view;
	}
}

if (isset($_REQUEST['ex_']) && $_REQUEST['ex_'] = 'xsl') {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/json2csv.class.php';
	$JSON2CSV = new JSON2CSVutil;
	$JSON2CSV->readJSON(json_encode($all_views));
	$JSON2CSV->flattenDL( ucwords($type) . "_Report.csv");
	exit;
}