<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/2/16
 * Time: 9:15 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/NursingTemplateDAO.php';
$data = (new NursingTemplateDAO())->all();
exit(json_encode($data));