<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/5/16
 * Time: 11:38 AM
 */
$id = @$_POST['id'];
$action = @$_POST['action'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralsQueueDAO.php';
$referral = null;
if (isset($id, $action)) {
	if ($action == 'dismiss') {
		$referral = (new ReferralsQueueDAO())->get($id)->setAcknowledged(TRUE)->update();
		if ($referral) {exit(json_encode(true));}
		exit(json_encode(false));
	} else if($action=='check_in'){
		$referral = (new ReferralsQueueDAO())->get($id)->setAcknowledged(TRUE)->update();
		if ($referral) {exit(json_encode($referral->getPatient()->getId()));}
		exit(json_encode(false));
	}
	exit(json_encode(false));
}
exit(json_encode(false));

