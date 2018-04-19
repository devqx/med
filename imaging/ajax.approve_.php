<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/11/15
 * Time: 3:06 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';

$scan = (new PatientScanDAO())->getScan($_POST['id']);

if(count($scan->getNotes()['reports']) > 0){
	$s = (new PatientScanDAO())->approveScan_($scan);
	
	exit($s !== null ? "ok":"error");
}
exit("error1"); //no note entered; what were you trying to approve?
