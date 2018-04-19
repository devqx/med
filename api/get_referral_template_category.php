<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 12/21/16
 * Time: 10:10 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/RefererTemplateCategoryDAO.php';

exit(json_encode($data=(new RefererTemplateCategoryDAO())->getCategories()));