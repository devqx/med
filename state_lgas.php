<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StateDAO.php';

$dao = new StateDAO();
$states = $dao->getStates(true);

header('Content-Type: application/json');
$data = json_encode($states);
echo $data;