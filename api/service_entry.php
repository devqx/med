<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/9/14
 * Time: 7:01 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.main.php';
$CFG = new MainConfig();
echo json_encode($CFG->listServiceEntries());