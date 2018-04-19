<?php

include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
if(isset($_REQUEST['q'])){
    $doctors = (new StaffDirectoryDAO())->getDoctors($_REQUEST['q']);
} else {
    $doctors = (new StaffDirectoryDAO())->getDoctors();
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
    header('Content-Type: application/json');
    $data = json_encode($doctors);
    if (!isset($_GET['suppress'])) {
        echo $data;
    }
}
//    echo json_encode($doctors);
?>
