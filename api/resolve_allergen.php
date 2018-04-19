<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/20/17
 * Time: 1:34 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAllergensDAO.php';
$data = (new PatientAllergensDAO())->get($_POST['id'])->setActive(FALSE)->update();

if($data !== null) {
	exit(json_encode(true));
}
exit(json_encode(false));