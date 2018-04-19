<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/8/17
 * Time: 10:21 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
if(isset($_REQUEST['type'])){
	$data = (new ServiceCenterDAO())->all($_GET['type'], null, null);
}else{
	$data = (new ServiceCenterDAO())->all();
}
exit(json_encode($data));