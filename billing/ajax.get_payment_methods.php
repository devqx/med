<?php
/**
 * Created by JetBrains PhpStorm.
 * User: peter
 * Date: 10/25/13
 * Time: 6:01 PM
 * To change this template use File | Settings | File Templates.
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PaymentMethodDAO.php';
$methods = (new PaymentMethodDAO())->all(TRUE);
exit (json_encode($methods));