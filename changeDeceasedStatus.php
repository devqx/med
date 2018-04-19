<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/MedicPlusException.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/protect.php');


try {
	if ((new PatientDemographDAO())->deceasePatient($_POST['pid'])) {
		exit("ok:Action completed successfully");
	} else {
		exit("error:Oops! Sorry action failed");
	}
} catch (MedicPlusException $e) {
	exit("error:" . $e->getMessage());
}