<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 21/4/2017
 * Time: 10:46 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EstimatedBillsDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/EstimatedBills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();

$id = $_REQUEST['id'];
$action = $_REQUEST['action'];
$es_bill = (new EstimatedBills());
$es_bill->setStatus($action);
$es_bill->setId($id);

$patient = (new PatientDemographDAO())->getPatient($_REQUEST['pid'], false, $pdo);
$upd = (new EstimatedBillsDAO())->approveEstimatedBill($es_bill,$pdo);
ob_end_clean();
if ($upd!== null) {
    $pdo->commit();
    exit('success');
}
$pdo->rollBack();
exit('error');