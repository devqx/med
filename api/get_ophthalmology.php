<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/4/14
 * Time: 9:57 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/OphthalmologyDAO.php';
if(isset($_REQUEST['search'])){
    $DATA = (new OphthalmologyDAO())->findTests($_REQUEST['search'], TRUE);
} else if(isset($_REQUEST['id'])){
    $DATA = (new OphthalmologyDAO())->get($_REQUEST['id'], TRUE);
} else {
    $DATA = (new OphthalmologyDAO())->all(TRUE);
}
echo( json_encode($DATA));