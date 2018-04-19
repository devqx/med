<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BlockDAO.php';

$full=isset($_REQUEST['full'])? TRUE:FALSE;

if(isset($_REQUEST['blockId'])){
    $block = (new BlockDAO())->getBlock($_REQUEST['blockId'], $full);
}else{
    $blocks=(new BlockDAO())->getBlocks($full);
}


if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {  //for Ajax
    header('Content-Type: application/json');
    
    if(isset($_REQUEST['blockId'])){
        $data = json_encode($block);
    }else{
        $data = json_encode($blocks);        
    }
    
    if (!isset($_GET['suppress'])) {
        echo $data;exit; 
    }
    
}

//exit(json_encode($blocks));