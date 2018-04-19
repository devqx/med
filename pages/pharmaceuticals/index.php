<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
@session_start();
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

if(!$this_user->hasRole($protect->pharmacy)){
    exit($protect->ACCESS_DENIED);
}?>
<div class="mini-tab">
    <a class="tab on incomplete" href="javascript:;" onclick="aTab(1)" data-href="?incomplete">Open Prescriptions</a>
    <a class="tab filled" href="javascript:;" onclick="aTab(3)" data-href="?filled">Filled Prescriptions</a>
    <a class="tab search" href="javascript:;" onclick="aTab(2)" data-href="?search">Search Prescriptions</a>
    <a class="tab new pull-right" href="javascript:;" data-href="#">New Prescription Request</a>
</div>
<div id="prescription_container"></div>