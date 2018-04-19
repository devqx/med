<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/4/14
 * Time: 9:57 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
if (isset($_REQUEST['search'])) {
	$DATA = (new LabDAO())->findLabs($_REQUEST['search'], true);
} else if (isset($_REQUEST['lab_ids'])) {
	$DATA = (new LabDAO())->getLabsById($_REQUEST['lab_ids'], true);
} else {
	$DATA = (new LabDAO())->getLabs(true);
}
echo(json_encode($DATA, JSON_PARTIAL_OUTPUT_ON_ERROR));