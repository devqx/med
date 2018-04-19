<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/3/14
 * Time: 12:40 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ProcedureTemplateDAO.php';
exit(json_encode($templates = (new ProcedureTemplateDAO())->all()));