<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/18/16
 * Time: 7:08 AM
 */
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

error_log(json_encode($_POST));
error_log(json_encode($_POST['on']));
exit(json_encode($_POST));