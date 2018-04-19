<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/6/17
 * Time: 12:34 PM
 */
include_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
@session_start();
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

?>
<div class="mini-tab">
	<a class="tab open" href="javascript:;" onclick="aTab(1)" data-href="?open">Open Request</a>
	<a class="tab filled" href="javascript:;" onclick="aTab(2)" data-href="?filled">Filled Request</a>
	<a class="tab search" href="javascript:;" onclick="aTab(3)" data-href="?search">Search Request</a>
	<a class="tab new pull-right" href="javascript:;" data-href="#">New Item Request</a>
</div>
<div id="prescription_container"></div>