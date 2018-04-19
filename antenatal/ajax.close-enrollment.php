<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 7/2/15
 * Time: 10:25 AM
 */

require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
require $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

if(is_blank($_POST['reason'])){
    exit("error:Closing Note is required");
}
$DAO = new AntenatalEnrollmentDAO();
$instance = $DAO->get($_GET['aid']);
$instance->setCloseNote( $_POST['reason'] );
$instance->setClosedBy( new StaffDirectory($_SESSION['staffID']) );
if(!$DAO->closeInstance($instance)){
    exit("error:Oops! something went went wrong");
}else{
    exit("ok:Patient antenatal closed");
}
