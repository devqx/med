<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/29/15
 * Time: 12:24 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/InsuranceItemsCostDAO.php';
//we want to show all that's in the system, as it is for scheme 1
$bItems = (new InsuranceItemsCostDAO())->getInsuredItemsCostsByScheme(1);
//$bItems = (new InsuranceItemsCostDAO())->getInsuredItemsCostsByScheme($_REQUEST['s']);

if(isset($_REQUEST['group_id'])){
    foreach ($bItems as $i=>$bItem) { //$bItem = new InsuranceItemsCost();
//        if($bItem->getServiceGroup()->getId() != $_REQUEST['group_id'])
        if($bItem->item_group_category_id != $_REQUEST['group_id'])
            unset($bItems[$i]);
    }
    $bItems = array_values($bItems);
}
exit(json_encode($bItems));