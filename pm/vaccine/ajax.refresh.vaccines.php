<?php
/**
 * Created by JetBrains PhpStorm.
 * User: robot
 * Date: 10/10/13
 * Time: 9:54 AM
 * To change this template use File | Settings | File Templates.
 */
//sleep(2);
require $_SERVER['DOCUMENT_ROOT'] . '/classes/class.vaccines.php';
$vac = new Vaccine_();
echo $vac->getAllVaccines();
