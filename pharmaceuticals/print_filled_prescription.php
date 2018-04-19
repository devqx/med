<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 6/28/17
 * Time: 4:14 PM
 */
@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.printer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Prescription.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PrescriptionData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';

$code = (isset($_GET['pCode'])) ? $_GET['pCode'] : '';
$printer = (isset($_GET['printer'])) ? $_GET['printer'] : '';
$count = (isset($_REQUEST['count'])) ? $_REQUEST['count'] : '1';
$clinic = (new ClinicDAO())->getClinic(1, true);
$msg = "";
if (date('m') == '12' && date('j') >= '25') {
	$msg .= "<br /><h4 align=\"center\">Merry Christmas</h4>";
}
if (date('n-j') == '1-1') {
	$msg .= "<br /><h4 align=\"center\">Happy New Year</h4>";
}

$data = (new PrescriptionDAO())->getPrescriptionByCode($_GET['pCode'], true);
$pres_description = $pres_quantity = $pres_line_id = $generic = $dose = $filledDate = array();
foreach ($data->getData() as $line) {
	if ($line->getStatus() == 'filled') {
		$pres_description[] = $line->getDrug()->getName();
		$generic[] = $line->getGeneric()->getName() . ' ' . ($line->getGeneric()->getWeight());
		$pres_quantity[] = $line->getQuantity() . ' ' . pluralize($line->getDrug()->getStockUOM(), $line->getQuantity());
		$pres_line_id[] = $line->getId();
		$filledDate[] = $line->getFilledOn();
		$dose[] = $line->getDose() . " "; // comment only for euracare . $line->getGeneric()->getForm() . (($line->getDose() != 1) ? 's' : '') . " " . $line->getFrequency() . " for " .  $line->getDuration() . " days";
	}
	
}

$pressInfo = "<h4 align='center'>" . (htmlspecialchars(strtoupper(str_replace("&", "and", $clinic->getName())))) . "</h4><h5 align='center'>" . str_replace("&", "and", $clinic->getAddress()) . ",</h5><h5 align='center'>" . $clinic->getLGA()->getName() . ", " . $clinic->getLGA()->getState()->getName() . "</h5><br /><h4 align=\"center\">Prescription Packing Slip</h4><br />";
$pressInfo .= "<hr width='40' />PATIENT: <span align='center'> " . $data->getPatient()->getFullname() . "</span><div>ID: <span align='right'>" . $data->getCode() . "</span></div><div>Prescribed By: <span align='right'>" . $data->getRequestedBy() . "</span></div><div>Date: <span align='right'> " . date("Y M, d", strtotime($data->getWhen())) . "</span></div><hr width='40' /><br />";

for ($i = 0; $i < count($pres_description); $i++) {
	$pressInfo .= "<p>Drug: " . str_replace("&", "$", $pres_description[$i]) . "</p>";
	$pressInfo .= "<p>Generic: " . str_replace("&", "$", $generic[$i]) . "</p>"; // comment this out for euracare lagos
	$pressInfo .= "<p>Quantity: " . str_replace("&", "$", $pres_quantity[$i]) . "</p>";
	$pressInfo .= "<p>Dose: " . str_replace("&", "$", $dose[$i]) . "</p><br />";
	
}
if (count($pres_description) == 0) {
	$pressInfo .= 'No filled prescription to print';
}

//error_log("-----------------pressInfo-----------------");
$url = 'http://' . $printer . '/receipt/printnow/';
//error_log($url);
$pressInfo = "<receipt>" . $pressInfo . "<cashdraw /></receipt>";
//error_log($pressInfo);
$fields = array('q' => $pressInfo);
$postData = '';
foreach ($fields as $k => $v) {
	$postData .= $k . '=' . $v . '&';
}
rtrim($postData, '&');
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, count($fields));
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
ob_end_clean();

//execute post
if (curl_exec($ch) === false) {
	error_log('Curl error: ' . curl_error($ch));
}
//close connection
curl_close($ch);