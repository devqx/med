<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/24/16
 * Time: 2:13 PM
 */

include_once "antenatal_vars.php";

$object = (object)null;
$object->gravida = $gravida;
$object->parity = $parity;
$object->general = $general_;
$object->pregnancies = $pregnancies;

exit(json_encode($object));

