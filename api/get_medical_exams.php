<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/3/16
 * Time: 4:30 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/MedicalExamDAO.php';
if(isset($_REQUEST['search'])){
	$data = (new MedicalExamDAO())->find($_REQUEST['search']);
} else {
	$data = (new MedicalExamDAO())->all();
}
exit(json_encode($data));