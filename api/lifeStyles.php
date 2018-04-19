<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LifeStyleDAO.php';

$dao=new LifeStyleDAO();
    $lifeStyles=$dao->getLifeStyles();
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
    header('Content-Type: application/json');
    $data = json_encode($lifeStyles);
    echo $data;
}
?>
