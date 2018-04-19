<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/2/15
 * Time: 3:46 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
$instanceStatus = (new InPatientDAO())->getInPatient($_GET['aid'], FALSE)->getStatus();
if ($instanceStatus == "Active") {
	?>
	<div class="menu-head">
		<label style="display: inline-block">
			<a href="javascript:void(0)"
			   onClick="Boxy.load('/procedures/new_patient_procedure.php?id=<?= trim($_GET['pid']); ?>&aid=<?= $_GET['aid'] ?>', {title: 'New Procedure', afterHide: function() {showTabs(10); }})">
				New Procedure</a>
		</label>
	</div>
<?php }

$_GET['id'] = $_GET['pid'];
$_GET['view'] = "procedures";
include_once $_SERVER['DOCUMENT_ROOT'] . '/patient_profile.php';