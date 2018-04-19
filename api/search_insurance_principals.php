<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/30/16
 * Time: 10:30 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceDAO.php';
$data = (new InsuranceDAO())->getPrincipals($_GET['scheme_id'], $_GET['q']);
exit(json_encode( $data ));