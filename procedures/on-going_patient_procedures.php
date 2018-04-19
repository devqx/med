<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/19/14
 * Time: 11:10 AM
 */

$serviceCentreId = isset($_REQUEST['service_centre_id']) ? $_REQUEST['service_centre_id'] : NULL;
$category_id = isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : null;

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientProcedureDAO.php';

$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;


$data = (new PatientProcedureDAO())->all(NULL, NULL, NULL, NULL, $serviceCentreId, $category_id, null, $page, $pageSize, "started");
$totalSearch = $data->total;
$pro = $data->data;
include_once 'templater.php';
exit;