<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/15/14
 * Time: 10:23 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureCategoryDAO.php';
exit(json_encode($cats = (new ProcedureCategoryDAO())->all()));