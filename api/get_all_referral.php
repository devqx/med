<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/23/17
 * Time: 11:44 AM
 */


include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
 $referrs = (new ReferralDAO())->getAll();
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
	header('Content-Type: application/json;charset=UTF-8');
		$data = json_encode($referrs);
	if (!isset($_GET['suppress'])) {
		echo $data;
	}
}
