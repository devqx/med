<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/24/16
 * Time: 2:58 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PAuthCodeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

if(is_blank($_POST['code'])){exit('error:No code was sent');}
if(is_blank($_POST['id'])){exit('error:Request contained incomplete record');}

$item = (new PAuthCodeDAO())->get($_POST['id'])->setCode($_POST['code'])->setReceiveDate(date(MainConfig::$mysqlDateTimeFormat))->setStatus('Received')->update();
if($item != null){
	exit('success:Code updated!');
}
exit('error:Failed to update the code');