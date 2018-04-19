<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/3/14
 * Time: 3:43 PM
 */
if (isset($_GET['open'])) {
	//those with status == `open`
	if (!empty($_GET)) {
		$link = "?open";
		foreach ($_GET as $key => $val) {
			$link .= "&" . $key . "=" . urlencode($val);
		}
	}
	include_once $_SERVER['DOCUMENT_ROOT'] . '/procedures/open_patient_procedure.php';
	exit;
} else if (isset($_GET['search'])) {
	include_once $_SERVER['DOCUMENT_ROOT'] . '/procedures/search_patient_procedure.php';
	exit;
} else if (isset($_GET['scheduled'])) {
	include_once $_SERVER['DOCUMENT_ROOT'] . '/procedures/scheduled_procedures.php';
	exit;
} else if (isset($_GET['new'])) {
	include_once $_SERVER['DOCUMENT_ROOT'] . '/procedures/new_patient_procedure.php';
	exit;
} else if (isset($_GET['my'])) {
	include_once $_SERVER['DOCUMENT_ROOT'] . '/procedures/my_patient_procedure.php';
	exit;
} else if (isset($_GET['current'])) {
	include_once $_SERVER['DOCUMENT_ROOT'] . '/procedures/on-going_patient_procedures.php';
	exit;
}
$script_block = <<<EOF
  $(document).ready(function(){
    //simulate the first tab opened
    $('#procedure_container').load("?open");
  });
EOF;
$page = "pages/procedures/index.php";
$title = "Procedures";
include "../template.inc.in.php";