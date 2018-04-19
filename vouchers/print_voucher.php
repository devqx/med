<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/28/15
 * Time: 2:10 PM
 */

@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VoucherDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';

$printer = (isset($_GET['printer']))? $_GET['printer'] : '';
$voucherId = isset($_GET['id'])? $_GET['id'] : '';
$voucher = (new VoucherDAO())->get($voucherId);
$clinic = (new ClinicDAO())->getClinic(1, TRUE);
$voucher_ = $voucher->getBatch()->getGenerator();
$printInfo = "<receipt><h4 align='center'>".strtoupper(str_replace("&","and",$clinic->getName()))."</h4><h5 align='center'>".$clinic->getAddress().",</h5><h5 align='center'>".$clinic->getLGA()->getName().", ".$clinic->getLGA()->getState()->getName()."</h5><br/><p>".ucwords($voucher->getBatch()->getType())." Voucher</p><p>Voucher Code: ".$voucher->getCode()."</p><p>Date Generated: ".date('Y M, d', strtotime($voucher->getBatch()->getDateGenerated()))."</p><p>Valid till: ".date('Y M, d', strtotime($voucher->getBatch()->getExpirationDate()))."</p><br /><br /><barcode encoding='EAN13'>".$voucher->getCode()."</barcode></receipt>";

$url = 'http://'.$printer.'/receipt/printnow/';
$fields = array('q'=> $printInfo);
$postData = '';
foreach($fields as $k => $v) {
    $postData .= $k . '='.$v.'&';
}
rtrim($postData, '&');
//open connection
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $postData);
ob_end_clean();
//execute post
if(curl_exec($ch) === false) {
    error_log('Curl error: ' . curl_error($ch));
}

//close connection
curl_close($ch);