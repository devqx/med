<div style="width: 200px;height: 50px;font-size: small">
	<?php if (isset($_GET['drugid']) && is_numeric($_GET['drugid'])) {
		require_once 'class.pharmacy.php';
		$ph = new Pharmacy();
		$drugID = $_GET['drugid'];
		$ret = $ph->purgeExpiredDrug($drugID, 'hospital');
		if ($ret == 'success') {
			echo 'Drug was purged successfully';
		} else {
			echo 'An error has occurred. Please contact the administrator if the error persists.';
		}
	}
	?>
</div>