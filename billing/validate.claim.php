<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/28/18
 * Time: 12:25 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Claim.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';

$cl = new Claim();
if($_POST['type'] == 'validate'){
$cl->setId($_POST['claim_id'])->setState('validated');
if($cl->validateClaim() != null){
	exit('success:Claim validated successfully');
}
}else if($_POST['type'] == 'confirm'){
	$cl->setId($_POST['claim_id'])->setState('confirmed')->setConfirmedBy((new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false))->setConfirmedDate(date('Y-m-d'));
	if($cl->validateClaim() != null){
		exit('success:Claim confirmed successfully');
	}
}
exit('error:Could not process claim');





