<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 3:12 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/OphthalmologyItemDAO.php';
if(isset($_REQUEST['search'])){
    $DATA = (new OphthalmologyItemDAO())->findItems($_REQUEST['search']);
}else {
    $DATA = (new OphthalmologyItemDAO())->all();
}
echo( json_encode($DATA));