<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/9/17
 * Time: 10:05 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralCompanyDAO.php';
$data = [];
if (isset($_GET['single'])) {
	$data = (new ReferralCompanyDAO())->get($_GET['id']);
}
exit(json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR));