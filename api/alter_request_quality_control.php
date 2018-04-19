<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/18/16
 * Time: 9:16 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/QualityControlDAO.php';
$qc = (new QualityControlDAO())->get($_POST['id'])->setActionDate(date('Y-m-d H:i:s'))->update();
if($qc !== null){
	exit("success");
}
exit("error");