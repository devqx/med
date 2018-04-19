<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/30/15
 * Time: 9:59 AM
 */
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/InsuranceItemsCostDAO.php';

$iitc = (new InsuranceItemsCostDAO())->updateCoPayPriceByFamily($_POST['name'], $_POST['pk'], $_POST['value']);

$return = (object)null;
$return->newValue = $iitc;
exit(json_encode($return));