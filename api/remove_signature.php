<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/29/16
 * Time: 11:59 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SignatureDAO.php';
$id = $_POST['d'];
$s = (new SignatureDAO())->get($id)->setActive(FALSE)->update();
if($s!==null){
	exit(json_encode(true));
}
exit(json_encode(false));
