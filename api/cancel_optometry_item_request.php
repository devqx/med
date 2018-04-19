<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/29/17
 * Time: 4:10 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/OphItemsRequestDAO.php';
@session_start();
if(!isset($_SESSION['staffID'])){exit('error:No active session');}

$status = (new OphItemsRequestDAO)->get($_POST['request_id'])->cancel();
ob_end_clean();
if($status!=null){
	exit('success:Request cancelled!');
}
exit('error:An error occurred while cancelling');