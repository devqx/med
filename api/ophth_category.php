<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . "/protect.php";
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyCategoryDAO.php';

$full=  isset($_REQUEST['full'])? TRUE:FALSE;
if(isset($_REQUEST['id'])){
    exit( json_encode( (new OphthalmologyCategoryDAO())->get($_REQUEST['id']) ) );
}
$labCats=(new OphthalmologyCategoryDAO())->all($full);
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
    header('Content-Type: application/json');
    $data = json_encode($labCats);
    if(!isset($_GET['suppress'])){
        echo $data;
    }
}
//echo (json_encode($labCats));
exit;
