<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/22/16
 * Time: 12:47 PM
 */
@session_start();
if(!isset($_SESSION['username'])){exit('error:Your session might have expired. Please use another tab to login and continue on this window');}
$uid = $_SESSION['staffID'];
$uname = $_SESSION['username'];
$pin = $_POST['pin'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.staff.php';

if(isset($_POST['user_id'])){
	exit( (new StaffManager())->doLogin($_POST['user_id'], $pin, null, null, false) );
}
exit( (new StaffManager())->doLogin($uname, $pin, null, null, false) );