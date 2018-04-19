<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/17/14
 * Time: 2:33 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ScanCategoryDAO.php';
$categories = (new ScanCategoryDAO())->getCategories();
$data = $categories;


exit(json_encode($categories));