<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/29/16
 * Time: 11:18 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';

$data = (new InsuranceItemsCostDAO())->getInsuranceItem($_POST['code'], $_POST['patient_id']);
exit(json_encode($data));