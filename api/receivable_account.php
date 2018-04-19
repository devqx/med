<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/15/15
 * Time: 4:04 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';

//error_log(json_encode($_POST));
$source_id = $_POST['name'];
$account_id = $_POST['value'];
$scheme_id = $_POST['pk'];

$account_value = (new InsuranceSchemeDAO())->setSchemeReceivableAccountBySource($scheme_id, $source_id, $account_id);

$return = (object)null;
$return->newValue = $account_value; //value or null
exit(json_encode($return));