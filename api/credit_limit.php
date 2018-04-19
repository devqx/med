<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/30/14
 * Time: 10:08 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/CreditLimit.php';

$value = ($_POST['value']);
$lim = new CreditLimit();
$lim->setPatient( (new PatientDemographDAO())->getPatient($_POST['name'], FALSE, NULL, NULL) );

if(!true){
    $response = (object)null;
    $response->status = "error";
    $response->msg = "Failed to update";
    exit(json_encode($response));
} else {
    $lim->setAmount($value['amount']);
}

$lim->setExpiration($value['expiration']);
$lim->setId($_POST['pk']);

$newLimit = (new CreditLimitDAO())->setPatientLimit($lim);
//exit(json_encode(   $newLimit));
if($newLimit != NULL){
    exit(json_encode($newLimit));
} else {
    $response = (object)null;
    $response->status = "error";
    $response->msg = "Failed to update";
    exit(json_encode($response));
}
