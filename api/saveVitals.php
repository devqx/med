<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/24/14
 * Time: 12:01 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.patient.php';
$patient = new Manager();
echo $patient->saveVitalSign($_POST['type'], $_POST['pid'], $_POST['value']);
//exit("error:TEST");