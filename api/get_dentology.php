<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/4/14
 * Time: 9:57 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/DentologyDAO.php';
if(isset($_REQUEST['search'])){
    $DATA = (new DentologyDAO())->findTests($_REQUEST['search'], TRUE);
} else if(isset($_REQUEST['id'])){
    $DATA = (new DentologyDAO())->get($_REQUEST['id'], TRUE);
} else {
    $DATA = (new DentologyDAO())->all(TRUE);
}
echo( json_encode($DATA));