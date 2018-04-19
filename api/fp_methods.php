<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/7/14
 * Time: 4:23 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.main.php';
$config = new MainConfig();
echo json_encode($config->listFamilyPlanningMethods());exit;