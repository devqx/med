<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$pharm = (new DrugGenericDAO())->filterDrugGenerics($_REQUEST['pharmacy'], NULL);

exit( json_encode($pharm));
