<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/11/15
 * Time: 1:53 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDentistryDAO.php';

$scan=(new PatientDentistryDAO())->get($_POST['id']);
$s=(new PatientDentistryDAO())->reject($scan);