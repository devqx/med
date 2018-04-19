<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/14/16
 * Time: 1:25 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticRequestDAO.php';
$requests = (new GeneticRequestDAO())->all('draft');
include_once "template.php";