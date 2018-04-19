<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/11/15
 * Time: 1:53 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/PatientScanDAO.php';

$scan=(new PatientScanDAO())->getScan($_POST['id']);
$s=(new PatientScanDAO())->rejectScan($scan);