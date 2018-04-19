<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/2/17
 * Time: 3:40 PM
 */
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/SFormCategoryDAO.php';
exit( json_encode( (new SFormCategoryDAO())->all(), JSON_PARTIAL_OUTPUT_ON_ERROR ) );