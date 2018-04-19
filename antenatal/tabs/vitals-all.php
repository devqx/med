<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

if ($this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse)) {
	include_once $_SERVER['DOCUMENT_ROOT'].'/vitals-all.php';
	exit;
	?>

<?php } else {
	echo $protect->ACCESS_DENIED;
}
