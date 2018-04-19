<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 10/16/15
 * Time: 2:29 PM
 */
if(!isset($_SESSION)){@session_start();}
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/DeathDAO.php';
require_once $_SERVER['DOCUMENT_ROOT']. "/classes/Death.php";
require_once $_SERVER['DOCUMENT_ROOT']. "/classes/StaffDirectory.php";

if(isset($_POST['action']) && $_POST['action']=="validate"){
    $dd = new Death($_POST['id']);
    $dd->setValidatedBy(new StaffDirectory($_SESSION['staffID']));
    $status = (new DeathDAO())->validate($dd);
    if($status){
        exit("ok");
    }
}
exit("error");