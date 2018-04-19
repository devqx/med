<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 12/21/16
 * Time: 10:47 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/RefererTemplateDAO.php';
exit(json_encode($templates = (new RefererTemplateDAO())->all(), JSON_PARTIAL_OUTPUT_ON_ERROR));