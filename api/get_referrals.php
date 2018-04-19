<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/22/15
 * Time: 11:54 AM
 */

include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';

$date = ((isset($_REQUEST['from']) && $_REQUEST['from'] != '' && isset($_REQUEST['to']) && $_REQUEST['to'] != '') ? true : false);
if ($date === false) {
	$referralReport = array();
} else {
	$referralReport = (new BillDAO())->getBillsForReferrals($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['referrer_id'], $_REQUEST['hospital'], true);
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
	//    header('Content-Type: application/json');
	//    $data = json_encode($referralReport);
	if (!isset($_GET['suppress'])) {
		echo $referralReport;
	}
}