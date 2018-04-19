<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/30/14
 * Time: 3:18 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.staff.php';
$staff  = new StaffManager();

@$staff->doRoomCleanUp($_SESSION['staffID']);

header("Location: ".$_SERVER['HTTP_REFERER']);