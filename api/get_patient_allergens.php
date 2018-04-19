<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/19/16
 * Time: 1:14 PM
 */


require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAllergensDAO.php';

$drugAllergens = new ArrayObject();
$pid = isset($_REQUEST['id']) ? $_REQUEST['id'] : (isset($_REQUEST['pid']) ? $_REQUEST['pid'] : 0);
if (isset($pid)) {
	$drugAllergens = (new PatientAllergensDAO())->forPatient($pid, $categoryId=1);
}

if (!isset($_GET['suppress'])) {
	echo json_encode($drugAllergens);
}
