<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/11/16
 * Time: 2:23 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Country.php';
if(!is_blank(@$_POST['id']) && @$_POST['type']=='single'){
	exit(json_encode( (new Country())->get($_POST['id']), JSON_PARTIAL_OUTPUT_ON_ERROR  ));
}
exit(json_encode(null));
