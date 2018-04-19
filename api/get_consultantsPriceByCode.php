<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/14/17
 * Time: 10:55 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
$data = null;
if (isset($_GET['sid'])){
    $consult = (new StaffSpecializationDAO())->get($_GET['sid']);
    $data =  (new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($consult->getCode());
}else if (isset($_GET['followup'])){
    $consult = (new StaffSpecializationDAO())->get($_GET['sid']);
    $data = (new InsuranceItemsCostDAO())->getItemDefaultFollowUpPriceByCode($consult->getCode());
}

exit(json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR));