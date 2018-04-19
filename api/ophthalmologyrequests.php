<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/4/14
 * Time: 10:24 AM
 */

if(!isset($_SESSION)){@session_start();}
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientOphthalmologyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT']. "/classes/StaffDirectory.php";

if(isset($_POST['action']) && $_POST['action']=="cancel"){
    $PLDAO = new PatientOphthalmologyDAO();
    $pl = $PLDAO->get($_POST['id']);

    $status = $PLDAO->cancel($pl);

    if($status){
        exit("ok");
    }
}
/*if(isset($_POST['action']) && $_POST['action']=="receive"){
    $PLDAO = new PatientOphthalmologyDAO();
    $pl = new PatientOphthalmology($_POST['id']);
    $pl->setReceivedBy(new StaffDirectory($_SESSION['staffID']));
    $status = $PLDAO->receive($pl);
    if($status){
        exit("ok");
    }
}*/
exit("error");