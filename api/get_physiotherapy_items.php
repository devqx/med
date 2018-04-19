<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 3:12 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/PhysiotherapyItemDAO.php';
if(isset($_REQUEST['search'])){
    $DATA = (new PhysiotherapyItemDAO())->findItems($_REQUEST['search']);
}else {
    $DATA = (new PhysiotherapyItemDAO())->all();
}
echo( json_encode($DATA));

