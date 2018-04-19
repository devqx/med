<?php

include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugCategoryDAO.php';
$drugCategories = [];
$drugCategory = (object)null;
$lastItemId = isset($_REQUEST['last_item_id'])?$_REQUEST['last_item_id']:NULL;
if(isset($_REQUEST['cid'])){
    $drugCategory = (new DrugCategoryDAO())->getCategory($_REQUEST['cid']);
} else if(isset($_REQUEST['search'])){
    $drugCategories = (new DrugCategoryDAO())->find($_REQUEST['search']);
} else{
    $drugCategories = (new DrugCategoryDAO())->getCategories($lastItemId);
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
    header('Content-Type: application/json');
    if(isset($_REQUEST['cid'])){
        $data = json_encode($drugCategory);
    }else{
        $data = json_encode($drugCategories);
    }
    if (!isset($_GET['suppress'])) {
        echo $data;
    }
}
//    echo json_encode($drugCategories);

