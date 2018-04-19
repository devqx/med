<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/15/16
 * Time: 11:56 AM
 */
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

error_log(json_encode($_POST));
exit(json_encode($_POST));