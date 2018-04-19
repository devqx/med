<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/InsurerDAO.php';
exit( json_encode( (new InsuranceSchemeDAO())->getInsuranceSchemes(TRUE, $_GET['insurer']) ));

