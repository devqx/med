<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/9/16
 * Time: 10:11 AM
 */

header("Access-Control-Allow-Origin: *");
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
$patients = [];
if (isset($_REQUEST['q'])) {
    $patients = (new PatientQueueDAO())->getPatientsInQueue(date("Y-m-d"), date("Y-m-d"), [$_REQUEST['type']], ['Active', 'Blocked'], FALSE, $page=0, $pageSize=9999999, $_REQUEST['dept'], $_REQUEST['specialty'], $_REQUEST['q']);
    exit(json_encode($patients));
}
