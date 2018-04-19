<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/31/14
 * Time: 5:40 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureDAO.php';

if (isset($_GET['search'])) {
	$process = (new ProcedureDAO())->findProcedures($_GET['search']);
} else if (isset($_REQUEST['ids'])) {
	$process = (new ProcedureDAO())->getByIds($_REQUEST['ids']);
} else {
	$process = (new ProcedureDAO())->getProcedures();
}

exit(json_encode($process, JSON_PARTIAL_OUTPUT_ON_ERROR));