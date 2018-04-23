<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StateDAO.php';

$dao=new StateDAO();
$states=$dao->getStates(TRUE);

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
// // header('Content-Type: application/json');
    $data = json_encode($states);
    if($_GET['suppress']){
        echo $data;
        exit;
    }
}else if ($_SERVER['REQUEST_METHOD'] === 'GET') {

}

function getLgas($stas, $lid){
    foreach ($stas as $st){
        foreach ($st->getLgas() as $l){
            if($l->getId()==$lid){
                return $st->getLgas();
            }
        }
    }
    return [];
}

function getStateId($stas, $lid){
    foreach ($stas as $st){
        foreach ($st->getLgas() as $l){
            if($l->getId()==$lid){
                return $st->getId();
            }
        }
    }
    return 0;
}

function getStateIndex($stas, $lid){
    for ($i=0; $i<sizeof($stas); $i++){
        foreach ($stas[$i]->getLgas() as $l){
            if($l->getId()==$lid){
                return $i;
            }
        }
    }
    return -1;
}