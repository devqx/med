<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/30/16
 * Time: 8:39 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugRequisitionDAO.php';
$data = (new DrugRequisitionDAO())->all();
include_once "template.php";
