<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/19/16
 * Time: 8:08 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/ArvEnrollmentDAO.php';

$data = (new ArvEnrollmentDAO())->allActive();

echo(json_encode($data));
exit();