<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/protect.php');
$protect = new Protect();
//todo: clean up these methods, and use objects
//now they're still working like this
if(isset($_POST['updatebtn'])){
    foreach ($_POST as $key => $value){
        $staff_cat[] = $key;
        $staff_fee[] = $value;
    }
    require_once  $_SERVER['DOCUMENT_ROOT'] .'/classes/class.confstaff.php';
    $staff_config = new StaffConfig;
    $ret = $staff_config->updateStaffFee($staff_cat, $staff_fee);
    echo $ret;exit;
}
else if (isset($_GET['user']) && isset($_GET['action'])){
    $ret = null;
    require_once ($_SERVER['DOCUMENT_ROOT'] .'/classes/class.staff.php');
    $staff_config = new StaffManager;
    if ($_GET['action'] == "delete"){
        $ret = $staff_config->doDeleteUser($_GET['user']);
    }else if ($_REQUEST['action'] == "disable") {
        $ret = $staff_config->doStaffDisable($_GET['user']);
    }else if ($_REQUEST['action'] == "enable") {
        $ret = $staff_config->doStaffEnable($_GET['user']);
    }
    exit($ret);
}
$title ="Practice Management";
$page = $_SERVER['DOCUMENT_ROOT']."/pages/pm/index.php";
$script_block = <<<EOF
function start(){}
function finished(s){}
EOF;
$extra_style = ['/style/pm.css'];
include $_SERVER['DOCUMENT_ROOT'].'/template.inc.in.php';