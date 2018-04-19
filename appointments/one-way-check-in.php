<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/1/16
 * Time: 1:07 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Resource.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AptClinic.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Appointment.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AppointmentInvitee.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AppointmentGroup.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentGroupDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
$_GET['suppress'] = TRUE;
//require_once $_SERVER ['DOCUMENT_ROOT'] . '/api/get_staff.php';

$staff = (new StaffDirectoryDAO())->getStaff( $_POST['staff_id'], TRUE, $pdo );
$p = json_decode($_POST['appointment']);

$ag = (new AppointmentGroup())
	->setCreator($staff)
	->setDepartment($staff->getDepartment())
	->setClinic(new AptClinic($p->clinic))
	->setIsAllDay($p->allDay)
	->setResource(new Resource($p->resource))
	->setDescription($p->description)
	->setPatient(new PatientDemograph($p->patient));
$apps = $appInvs = [];
foreach ($p->sdates as $key => $d) {
	$app = new Appointment();
	$app->setEditor($staff);
	$app->setStartTime($d);
	$app->setEndTime(($p->edates[$key] === null || trim($p->edates[$key]) === "") ? null : $p->edates[$key]);
	$unlimited = (new AppointmentGroupDAO())->checkAppointByClinic($d, $p->clinic, $pdo);
	if(!$unlimited && $p->forced !== true){
		$pdo->rollBack();
		exit(json_encode('warn:Sorry, Clinic has reached the daily appointment limit'));
	}
	$apps[] = $app;
}

$staffs = count($p->staffs) > 0 ? $p->staffs : [];

if (count($staffs) > 0 && is_array($staffs)) {
	foreach ($staffs as $s) {
		$ai = new AppointmentInvitee();
		$ai->setStaff(new StaffDirectory($s->id));
		$appInvs[] = $ai;
	}
}

$ag->setAppointments($apps);
$ag->setInvitees($appInvs);

$appGroup = (new AppointmentGroupDAO())->add($ag, $pdo);

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AppointmentExist.php';
if($appGroup instanceof AppointmentExist){
	$pdo->rollBack();
	exit(json_encode("error:There's an appointment that is already set for the requested time(s) for one or more of the selected staffs"));
} else if ($appGroup == null) {
	$pdo->rollBack();
	exit(json_encode('error:Unable to book appointment (Maybe another active appointment exists)'));
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
	$appointments = (new AppointmentDAO())->getAppointmentByGroup($appGroup->getId(), FALSE, $pdo);

	$patient_id = $_POST['patient_id'];
	$appointment_id = $appointments[0]->getId();

	$department_id = $_POST['did'];
	$staffId = $_POST['staff_id'];

	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Department.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Encounter.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';

	$appoint = (new AppointmentDAO())->setStatus($appointment_id, 'Active', $staffId, $pdo);

	$apt = (new AppointmentDAO())->getAppointment($appointment_id, TRUE, $pdo);

	$patient = new PatientDemograph($patient_id);
	$que = new PatientQueue();
	$que->setStatus('Active');

	$que->setType('Nursing');
    $que->setClinic(new AptClinic($p->clinic));
	$que->setPatient($patient);
	$que->setDepartment(new Department($department_id));
	$st = (new PatientQueueDAO())->addPatientQueue($que, $pdo);
	$pat = (new PatientDemographDAO())->getPatient($_POST['patient_id'], FALSE, $pdo, null);
	
	$encounterStart = date(MainConfig::$mysqlDateTimeFormat);
	
	if (!is_blank($_POST['room_id'])) {
		$b = NULL;
		$price = 0;
		$specialty = (new StaffSpecializationDAO())->get($_POST['room_id'], $pdo);
		if($_POST['review']!=='true'){
			if ($_POST['followUp']==='true') {
				$price = (new InsuranceItemsCostDAO())->getItemFollowUpPriceByCode($specialty->getCode(), $_POST['patient_id'], TRUE, $pdo);
			} else {
				$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($specialty->getCode(), $_POST['patient_id'], TRUE, $pdo);
			}
			
			$staff = (new StaffDirectoryDAO())->getStaff($staffId, FALSE, $pdo);
			
			$bil = new Bill();
			$bil->setPatient($pat);
			$bil->setDescription(($_POST['followUp']=='true' ? "FollowUp " : "") . "Consultancy charges: " . $specialty->getName());
			$bil->setItem($specialty);
			$bil->setSource((new BillSourceDAO())->findSourceById(3, $pdo));
			$bil->setTransactionType("credit");
			$bil->setTransactionDate($encounterStart);
			$bil->setAmount($price);
			$bil->setPriceType(($_POST['followUp'] == 'true') ? "followUpPrice" : "selling_price");
			$bil->setDiscounted('no');
			$bil->setDiscountedBy(null);
			$bil->setReceiver($staff);
			$bil->setClinic($staff->getClinic());
			$bil->setCostCentre((new DepartmentDAO())->get($department_id, $pdo)->getCostCentre());
			$bil->setBilledTo($pat->getScheme());
			
			$b = (new BillDAO())->addBill($bil, 1, $pdo);
		}
		
		//this addbill function creates a blank new line of output: delete that output
		ob_clean();

		// TODO: send this doctor a notification ...?
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
		$pq = new PatientQueue();
		$pq->setType("Doctors");
		$pq->setSpecialization($specialty);
		$pq->setPatient($pat);
        $pq->setClinic(new AptClinic($p->clinic));
		$pq->setDepartment(new Department($_POST['did']));
		$pq->setFollowUp($_POST['followUp']=='true' ? 1 : 0);
		$pq->setReview($_POST['review']=='true' ? 1 : 0);
		$pq->setAmount($price);
		//push to doctors' queue
		if ((new PatientQueueDAO())->inQueue($pq->getPatient()->getId(), $pq->getType(), $pq->getSpecialization(), FALSE, $pdo)) {
			ob_end_clean();
			$pdo->rollBack();
			exit("error:Sorry, Patient is already Active on the {$specialty->getName()} Doctors queue");
		}
		$referrer = !is_blank($_POST['referrer_id']) ? (new ReferralDAO())->get($_POST['referrer_id'], $pdo) : NULL;
		
		if($_POST['review']!=='true') {
			$encounter = (new Encounter())->setBill($b!=null && $b->getId()!=null ? $b : NULL)->setStartDate($encounterStart)->setFollowUp($_POST['followUp'] == 'true' ? true : false)->setPatient($pat)->setDepartment(new Department($_POST['did']))->setInitiator($staff)->setSpecialization($specialty)->setScheme($pat->getScheme())->setReferrer($referrer);
			$newEncounter = (new EncounterDAO())->add($encounter, $pdo);
			if ($newEncounter == null) {
				ob_end_clean();
				$pdo->rollBack();
				exit("error:Sorry, An encounter failed to start");
			}
			
			$pq->setEncounter($newEncounter);
		}
		(new PatientQueueDAO())->addPatientQueue($pq, $pdo);
	} else {
		$encounter = (new Encounter())->setStartDate($encounterStart)->setFollowUp($_POST['followUp']=='true' ? true : false)->setPatient($pat)->setDepartment(new Department($_POST['did']))->setInitiator($staff)->setSpecialization(NULL);

		if ((new EncounterDAO())->add($encounter, $pdo) == null) {
			ob_end_clean();
			$pdo->rollBack();
			exit("error:Sorry, An encounter failed to start");
		}
	}

	if ($st !== null) {
		$pdo->commit();
		exit("success:Appointment Booked; Patient was checked in successfully");
	} else {
		$pdo->rollBack();
		exit("error:Failed to add to Nursing queue");
	}
	//echo json_encode(array('status' => $appoint));
}
exit;


$server = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
$url = $server . '/functions/appointment_processor.php';

$fields = array('createAppointment' => urlencode(json_encode($_POST['appointment'])), 'staff_id' => urlencode($_POST['staff_id']));
$fields_string = '';
foreach ($fields as $key => $value) {
	$fields_string .= $key . '=' . $value . '&';
}
rtrim($fields_string, '&');

$ch = curl_init();//open connection

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, count($fields));
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);//set the url, number of POST vars, POST data
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);//execute post
curl_close($ch);//close connection
$json_result = json_decode($result, true);

$return1 = explode(":", $json_result);
if($return1[0]==='success'){
	$appointment_id = $return1[2];
	//start the check in part
	$checkInUrl = $server . '/check_in.php';

	$fields1 = array(
		'pid' => urlencode($_POST['patient_id']),
		'qid' => urlencode($appointment_id),
		'did' => urlencode($_POST['did']),
		'room_id' => urlencode($_POST['room_id']),
		'staff_id' => urlencode($_POST['staff_id']),
	);

	if($_POST['followUp']==="true" ){
		$fields1['followUp'] = urlencode($_POST['followUp']);
	}
	$fields_string1 = '';
	foreach ($fields1 as $key => $value) {
		$fields_string1 .= $key . '=' . $value . '&';
	}
	rtrim($fields_string1, '&');
	$ch1 = curl_init();//open connection

	curl_setopt($ch1, CURLOPT_URL, $checkInUrl);
	curl_setopt($ch1, CURLOPT_POST, count($fields1));
	curl_setopt($ch1, CURLOPT_POSTFIELDS, $fields_string1);//set the url, number of POST vars, POST data
	curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
	$result1 = curl_exec($ch1);//execute post
	curl_close($ch1);//close connection
	exit($result1);
}

exit($json_result);