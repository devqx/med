<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/9/17
 * Time: 10:21 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';

if(isset($_GET['single'])){
	$data = (new ReferralDAO())->get($_GET['id']);
	exit(json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR));
}