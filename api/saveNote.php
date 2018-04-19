<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/23/14
 * Time: 3:27 PM
 */
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/class.patient.php';
$patient = new Manager();
echo $patient->savePatientVisitNote($_POST['pid'], $_POST['note'], 'subj');
exit;