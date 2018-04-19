<?php
/**
 * Created by PhpStorm.
 * User: nnamdi
 * Date: 11/28/16
 * Time: 1:36 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';

$pid = escape($_REQUEST['pid']);
$key = escape($_REQUEST['search']);

$data = (new BillDAO())->searchBillsToClaim($key, $pid);
exit(json_encode($data));