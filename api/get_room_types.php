<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/RoomTypeDAO.php';

$full=isset($_REQUEST['full'])? TRUE:FALSE;

if(isset($_REQUEST['roomTypeId'])){
    $roomType = (new RoomTypeDAO())->getRoomType($_REQUEST['roomTypeId'], $full);
}else{
    $roomTypes=(new RoomTypeDAO())->getRoomTypes($full);
}

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {  //for Ajax
    header('Content-Type: application/json');
    
    if(isset($_REQUEST['roomTypeId'])){
        $data = json_encode($roomType);
    }else{
        $data = json_encode($roomTypes);        
    }
    
    if (!isset($_GET['suppress'])){
        echo $data;
        exit;
    }
}
