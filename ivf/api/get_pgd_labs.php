<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/14/16
 * Time: 8:53 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] .'/ivf/classes/DAOs/GeneticLabDAO.php';
if(isset($_REQUEST['search'])){
    $DATA = (new GeneticLabDAO())->find($_REQUEST['search']);
}else {
    $DATA = (new GeneticLabDAO())->all();
}
echo( json_encode($DATA));