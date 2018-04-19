<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 2/25/15
 * Time: 2:02 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/EstimatedBillLineDAO.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/EstimatedBillsDAO.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/EstimatedBills.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/EstimatedBillLine.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';


$pdo = (new MyDBConnector())->getPDO();
//$pdo->beginTransaction();


$id = $_REQUEST['id'];
$bill_id = $_REQUEST['bill_id'];
$price = $_REQUEST['price'];
$line = new EstimatedBillLine();
$line->setId($id);

$del = (new EstimatedBillLineDAO())->delete($line,$pdo);
if ($del === null){
    //$pdo->commit();
    exit('error');
}
$estimated_total = (new EstimatedBillsDAO())->getEstimatedBillById($bill_id,$pdo)->getTotalEstimate();
$newEsTotal = $estimated_total - $price;
$es_bill = new EstimatedBills();
$es_bill->setId($bill_id);
$es_bill->setTotalEstimate($newEsTotal);
$updes_total = (new EstimatedBillsDAO())->updateTotalEstimate($es_bill,$pdo);
if ($updes_total === null){
    exit('error: Failed');
}

 exit('success');


