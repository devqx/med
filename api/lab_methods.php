<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/28/17
 * Time: 2:31 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabMethodDAO.php';

$methods = (new LabMethodDAO())->all();

exit(json_encode($methods, JSON_PARTIAL_OUTPUT_ON_ERROR));