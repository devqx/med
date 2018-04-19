<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
//error_log("*********************************************************".$_REQUEST['q']);
if(isset($_REQUEST['scheme'])){
    $full =  (isset($_REQUEST['full'])? TRUE:FALSE);
    $limit = isset($_REQUEST['limit'])?  $_REQUEST['limit']:NULL;
    $bills=(new BillDAO())->searchBills($_REQUEST['scheme'], $_REQUEST['from'], $_REQUEST['to'], $full);
} else {
    $bills=(new BillDAO())->searchBills();
}
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
    header('Content-Type: application/json');
    $data = json_encode($bills);
    if(!isset($_GET['suppress'])){
        echo $data;
    }
}
