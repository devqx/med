<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/3/14
 * Time: 1:30 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ExamTemplateCategoryDAO.php';

exit(json_encode($data=(new ExamTemplateCategoryDAO())->getCategories()));