<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/4/16
 * Time: 3:20 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ProcedureChecklistTemplateDAO.php';
exit(json_encode($templates = (new ProcedureChecklistTemplateDAO())->all()));