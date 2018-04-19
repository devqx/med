<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/18/17
 * Time: 11:19 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureSpecialtyDAO.php';
$data = (new ProcedureSpecialtyDAO())->all();
exit(json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR));