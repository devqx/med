<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/21/16
 * Time: 12:30 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceDAO.php';
exit( json_encode(  (new InsuranceDAO())->getPatientInsuranceSlim($_REQUEST['pid'])  ) );