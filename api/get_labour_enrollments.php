<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/5/16
 * Time: 2:27 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabourEnrollmentDAO.php';

$data = (new LabourEnrollmentDAO())->allActive();
exit(json_encode($data));