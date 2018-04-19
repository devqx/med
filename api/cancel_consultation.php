<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/12/16
 * Time: 4:52 PM
 */
if(!isset($_SESSION)){@session_start();}
if(isset($_POST['q'])){
    require_once $_SERVER['DOCUMENT_ROOT']. "/classes/DAOs/BillDAO.php";
    require_once $_SERVER['DOCUMENT_ROOT']. "/classes/StaffDirectory.php";

    $staff = new StaffDirectory($_SESSION['staffID']);
    $bill = (new BillDAO())->getBill($_POST['q'], TRUE);
    exit(json_encode((new BillDAO())->cancelConsultationVisit($bill, $staff)));
}
exit(json_encode(false));