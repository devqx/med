<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/RoomDAO.php';

$full=isset($_REQUEST['full'])? TRUE:FALSE;

if(isset($_REQUEST['roomId'])){
    $room = (new RoomDAO())->getRoom($_REQUEST['roomId'], $full);
}else{
    $rooms=(new RoomDAO())->getRooms($full);
}

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {  //for Ajax
    header('Content-Type: application/json');
    
    if(isset($_REQUEST['roomId'])){
        $data = json_encode($room);
    }else{
        $data = json_encode($rooms);        
    }
    
    if (!isset($_GET['suppress'])) {
        echo $data; exit;
    }
    
}
