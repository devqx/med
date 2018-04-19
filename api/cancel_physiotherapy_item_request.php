<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/30/17
 * Time: 9:10 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PhysioItemsRequestDAO.php';
@session_start();
if(!isset($_SESSION['staffID'])){exit('error:No active session');}
$status = (new PhysioItemsRequestDAO)->get($_POST['request_id'])->cancel();
ob_end_clean();
if($status!=null){
	exit('success:Request cancelled!');
}
exit('error:An error occurred while cancelling');