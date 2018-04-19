<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/6/14
 * Time: 12:59 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.main.php';
$config = new MainConfig();
echo json_encode($config->getHIVSymptoms());exit;