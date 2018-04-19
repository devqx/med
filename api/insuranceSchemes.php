<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
if(!isset($_SESSION)){@session_start();}
$dao = new InsuranceSchemeDAO();
    $full=  (isset($_REQUEST['full'])? TRUE:FALSE);
    $insSchemes= $dao->getInsuranceSchemes($full, null);
    
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax

    $data = json_encode($insSchemes);
    if(!isset($_GET['suppress'])){
        echo $data;
    }
}else{
	//echo json_encode($insSchemes, JSON_PARTIAL_OUTPUT_ON_ERROR);
	
}

