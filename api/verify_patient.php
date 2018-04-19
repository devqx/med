<?php
header("Access-Control-Allow-Origin: *");
header('Content-type: application/json');
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
$asArray = isset($_REQUEST['asArray']) ? true : false;
$medical = isset($_REQUEST['medical']) ? true : false;
$patients = [];
$patient = null;

if (isset($_REQUEST['pid'])) {
	$patient = (new PatientDemographDAO())->getPatient($_REQUEST['pid'], false);
	
	if (isset($_REQUEST['dob'], $_REQUEST['pid'], $_REQUEST['surname'])) {
		//three-factor
		if ($patient !== null && !(date('Y-m-d', strtotime($patient->getDateOfBirth())) == date('Y-m-d', strtotime($_REQUEST['dob'])) && strtolower($patient->getLname()) == strtolower($_REQUEST['surname']))) {
			$patient = null;
		}
	} else if (isset($_REQUEST['pid'], $_REQUEST['surname'])) {
		//two-factor
		if ($patient !== null && !(strtolower($patient->getLname()) == strtolower($_REQUEST['surname']))) {
			$patient = null;
		}
	}
	
	if ($patient != null) {
		$date = date('jS M, Y');
		$patient->welcomeMessage
			= <<<EOF
		<p>Your medical information is personal. Garki Hospital is dedicated to protecting your privacy. We may use and disclose your medical information to help us obtain payment for the healthcare services you received at Garki Hospital. For example, in order for your insurance company or HMO to pay for your treatment, we must submit a bill that identifies you, your diagnosis and the treatment we provided.</p>

<p>By providing this signature you affirm that you have presented in person at Garki Hospital today $date for the purpose of receiving healthcare or doing medically indicated tests and that you have authorized Garki Hospital to provide only that information which is necessary to obtain payment for the healthcare services you received in Garki Hospital from your insurance company or HMO.</p>

<p>This signature is valid only for this clinical encounter and cannot be used for any other clinical encounter.</p>
EOF;
		
		$data = json_encode($patient);
		exit($data);
	}
	exit(json_encode(null));
}

