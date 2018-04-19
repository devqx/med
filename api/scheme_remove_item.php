<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/11/14
 * Time: 3:58 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/InsuranceItemsCostDAO.php';
//exit("error:".$_POST['id']);
$item = (new InsuranceItemsCostDAO())->getInsuranceItemsCost($_POST['id']);

if($item->getInsuranceScheme()->getId()==1){
    exit("error:You cannot remove from the base scheme");
} else {
    $status = (new InsuranceItemsCostDAO())->removeItem($item);
    if($status === true){
        exit("success:Item removed");
    } else if ($status === false){
        exit("error:Failed to remove item");
    } else {
        exit("error:System error");
    }
}