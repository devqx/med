<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/8/18
 * Time: 8:37 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/Hx_TemplateDAO.php';
exit(json_encode($templates = (new Hx_TemplateDAO())->all()));