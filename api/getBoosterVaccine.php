<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 3/26/15
 * Time: 5:30 PM
 */

include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VaccineBoosterDAO.php';
include_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/VaccineBooster.php';

$vaccines=(new VaccineBoosterDAO())->getVaccineBoosters(TRUE);
$data = json_encode($vaccines);
echo $data;
exit();