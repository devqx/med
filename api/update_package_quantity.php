<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/15/16
 * Time: 9:21 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Package.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PackageItemDAO.php';

$code = $_POST['itemCode'];
$qty = $_POST['quantity'];
$id = $_POST['id'];
$package_id = $_POST['package_id'];

$packageItem = (new PackageItemDAO())->get($id)->setPackage( new Package($package_id) )->setQuantity(parseNumber($qty))->update();

if($packageItem!=null){
	exit(json_encode(array('status'=>'success', 'message'=>'Quantity updated')));
}
exit(json_encode(array('status'=>'error', 'message'=>'Action failed')));