<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/27/17
 * Time: 6:23 PM
 */

require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/ClinicalTaskComboDataDAO.php';
$data = (new ClinicalTaskComboDataDAO())->get($_POST['id'])->delete();

exit(json_encode($data));