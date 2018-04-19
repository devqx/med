<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/17/14
 * Time: 1:18 PM
 */
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/DentistryDAO.php';
$scans = (new DentistryDAO())->getServices();

if(isset($_GET['search'])){
    $scans = (new DentistryDAO())->findServices($_GET['search']);
}

exit(json_encode($scans));