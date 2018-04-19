<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/12/15
 * Time: 9:37 AM
 */

require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/DentistryTemplateDAO.php';

$templates = (new DentistryTemplateDAO())->all();
exit(json_encode($templates));