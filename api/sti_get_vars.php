<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/21/16
 * Time: 3:22 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/CareEntryPointDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/ModeOfTestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/PriorARTDAO.php';
$vars = (object)null;
$vars->careEntryPoints = (new CareEntryPointDAO())->all();
$vars->modesOfTest = (new ModeOfTestDAO())->all();
$vars->priorARTs = (new PriorARTDAO())->all();

exit(json_encode($vars));