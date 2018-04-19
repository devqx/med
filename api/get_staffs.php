<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . "/protect.php";
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';

$staff = (new StaffDirectoryDAO())->getStaffs(TRUE);
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
    //header('Content-Type: application/json');
    $data = json_encode($staff);
    if (!isset($_GET['suppress'])) {
        echo $data;
    }
}
