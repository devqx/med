<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/2/16
 * Time: 2:39 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FormularyDAO.php';

if(!is_blank($_GET['action']) && @$_GET['action']=='generics'){
	$data = (new FormularyDAO())->get(@$_GET['id']);
	
	exit(json_encode( $data ));
}