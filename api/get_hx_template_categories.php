<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/7/18
 * Time: 10:41 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/Hx_Template_CategoryDAO.php';

exit(json_encode($data=(new Hx_Template_CategoryDAO())->all()));