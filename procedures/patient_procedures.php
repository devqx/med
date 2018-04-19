<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/10/14
 * Time: 12:39 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
$pageSize = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 0;
$serviceCentreId = isset($_REQUEST['service_centre_id']) ? $_REQUEST['service_centre_id'] : null;
$data = (new PatientProcedureDAO())->getPatientProcedures($_GET['pid'], null, null, $page, $pageSize, null, $serviceCentreId);
$totalSearch = $data->total;
$pro = $data->data;
include_once 'templater.php';
exit;

