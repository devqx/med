<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/26/16
 * Time: 10:55 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
$scan = (new PatientScanDAO())->getScan($_GET['id']);

exit(json_encode(array('id'=>$scan->getId(), 'status_'=>$scan->getStatus(), 'approved'=>$scan->getApproved())));