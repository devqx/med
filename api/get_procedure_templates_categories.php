<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/3/14
 * Time: 1:30 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ProcedureTemplateCategoryDAO.php';

exit(json_encode($data=(new ProcedureTemplateCategoryDAO())->all()));