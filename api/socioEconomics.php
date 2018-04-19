<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/SocioEconomicStatusDAO.php';

$dao=new SocioEconomicStatusDAO();
    $sess=$dao->getSocioEconomicStatuss();
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
    header('Content-Type: application/json');
    $data = json_encode($sess);
    echo $data;
    
}
    ?>
