<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/8/17
 * Time: 3:37 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureResourceTypeDAO.php';
exit(json_encode((new ProcedureResourceTypeDAO())->all(), JSON_PARTIAL_OUTPUT_ON_ERROR));