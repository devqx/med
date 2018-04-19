<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 4/6/17
 * Time: 2:58 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/ItemDAO.php';
$it = (new ItemDAO())->getItem($_REQUEST['id']);
$batches = (new InsuranceItemsCostDAO())->getItemPriceByCode($it->getCode(), $_REQUEST['pid']);
exit(json_encode( $batches ));