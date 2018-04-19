<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/18/16
 * Time: 4:59 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticRequestDAO.php';
if($_POST['action']=="approve"){
	$s = 'result_approved';
} else if($_POST['action']=="cancel"){
	$s = 'cancelled';
} else {
	$s = 'draft';
}
$request = (new GeneticRequestDAO())->get($_POST['request_id'])->setStatus($s)->update();
if($request !== null){
	exit("success");
}
exit("error");