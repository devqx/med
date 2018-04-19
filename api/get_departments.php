<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/16/14
 * Time: 9:41 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/DepartmentDAO.php';
$data = (new DepartmentDAO())->getDepartments();
exit(json_encode($data));