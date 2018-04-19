<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/7/15
 * Time: 4:45 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/LabComboDAO.php';
$labCombos = ( (new LabComboDAO())->all() );

if(isset($_GET['fetch'])){
    exit(json_encode($labCombos, JSON_PARTIAL_OUTPUT_ON_ERROR));
}
