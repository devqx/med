<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/17/16
 * Time: 3:02 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DistrictDAO.php';
if(!is_blank(@$_POST['state_id']) && @$_POST['type']=='list'){
	exit(json_encode( (new DistrictDAO())->forState($_POST['state_id'])  ));
} else if(@$_POST['type']=='single'){
	exit( json_encode( (new DistrictDAO())->get($_POST['id']) ) );
}
exit(json_encode([]));
