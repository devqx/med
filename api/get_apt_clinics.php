<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/21/16
 * Time: 10:37 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AptClinicDAO.php';
$apt_clinics = (new AptClinicDAO())->all();

exit(json_encode($apt_clinics));