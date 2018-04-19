<?php
@session_start();
//require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.staff.php';

function validatePatient($ARR)
{
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LGA.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/State.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LifeStyle.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Insurance.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Religion.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/SocioEconomicStatus.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReligionDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReligionDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/SpokenLanguageDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralCompanyDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/KinRelationDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.patient.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Country.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Company.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/District.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Contact.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Industry.php';
	
	if (!isset($_SESSION)) {
		session_start();
	}
	if (!isset($_SESSION ['staffID'])) {
		return 'error:' . Manager::$SESSION_EXPIRED;
	}
	$pat = new PatientDemograph();
	$pat->setLegacyId(trim(@$ARR['legacy_num']));
	if (!is_blank(@$ARR["title"])) {
		$pat->setTitle(trim($ARR["title"]));
	}
	
	$pat->setFname(trim(@$ARR['fname']));
	$pat->setLname(trim(@$ARR['lname']));
	$pat->setMname(trim(@$ARR['mname']));
	if(!is_blank(@$ARR['language_id'])){
	    $pat->setSpokenLang((new SpokenLanguageDAO())->get($ARR['language_id']));
    }
	if (!is_blank(@$ARR['referral_id'])) {
		$pat->setReferral((new ReferralDAO())->get($ARR['referral_id']));
	}
	if (!is_blank(@$ARR['referral_company_id'])) {
		$pat->setReferralCompany((new ReferralCompanyDAO())->get($ARR['referral_company_id']));
	}
	
	if (isset($ARR['pid'])) {
		$pat2 = (new PatientDemographDAO())->getPatient($ARR['pid'], false);
	} else {
		$pat2 = null;
	}
	
	if (isset($ARR['dob'])) {
		
		$date = DateTime::createFromFormat('d/m/Y', $ARR['dob'])->format('Y-m-d');
		//TODO: in the future: if the date of birth changed and the patient has vaccines,
		//we have to modify the vaccine chart for the ones to take.
		//we are not doing that yet
		if ($pat2 != null && $pat2->getDateOfBirth() != $date) {
			if ((new Manager())->isImmunization($ARR['pid'])) {
				return 'error:Patient cannot change his date of birth because he has been enrolled into the immunization program';
			}
		}
		$pat->setDateOfBirth($date);
		
		if (isset($ARR['estimated_dob'])) {
			$pat->setDobEstimated(true);
			$pat->setDateOfBirth(date('Y-01-01', strtotime($date)));
		} else {
			$pat->setDobEstimated(false);
		}
		
	} else {
		$pat->setDateOfBirth($pat2->getDateOfBirth());
		$pat2 = null;
	}
	
	//if(is_blank(@$ARR['kinphone'])){
	//	exit("error:Next of kin phone number is needed");
	//}
	//
	
	
	$pat->setSex(@$ARR['sex']);
	$pat->setEmail(trim(@$ARR['email']));
	$pat->setAddress(trim(@$ARR['address']));
	$pat->setEthnic(trim(@$ARR['ethnic']));

	$pat->setNationality((new Country())->get(@$ARR['nationality']));
	$pat->setOccupation(trim(@$ARR['occupation']));
	$pat->setWorkAddress(trim(@$ARR['work_address']));
	
	if (!is_blank(@$ARR['industry_id'])) {
		$pat->setIndustry(new Industry(@$ARR['industry_id']));
	}
	if (isset($ARR['religion_id'])) {
		$pat->setReligion((new ReligionDAO())->getReligion($ARR['religion_id']));
	} else {
		$pat->setReligion(null);
	}
	
	$lga = new LGA();
	$lga_r = new LGA();
	$lga->setId(@$ARR['lga']);
	$pat->setLga($lga); //lga of origin
	$state = new State();
	$state_r = new State();
	$state->setId(@$ARR['state']);
	$pat->setState($state); //state of origin
	$state_r->setId(@$ARR['state_r']);
	if (!is_blank(@$ARR['district_id'])) {
		$pat->setDistrict(new District(@$ARR['district_id']));
	} else {
		$pat->setDistrict(null);
	}
	$pat->setResState($state_r); //state of residence
	$lga_r->setId(@$ARR['lga_r']);
	$pat->setResLga($lga_r); //lga of residence
	if (!is_blank(@$ARR['district_res_id'])) {
		$pat->setDistrictRes(new District(@$ARR['district_res_id']));
	} else {
		$pat->setDistrictRes(null);
	}
	
	$pat->setKinsFirstName(trim(@$ARR['kinfname']));
	$pat->setKinsLastName(trim(@$ARR['kinlname']));
	//$pat->setKinsPhone(@$ARR['kinphone']);
	if (!is_blank(@$ARR['type']) && !is_blank(@$ARR['kinphone']) && !is_blank(@$ARR['kin_phone_country_id'])) {
		$kinPhone = (new Contact())->setType(@$ARR['type'])->setCountry((new Country())->get(@$ARR['kin_phone_country_id']))->setPhone(@$ARR['kinphone'])->setRelation('kin');
		$pat->setKinsPhone($kinPhone);
	}
	
	$pat->setKinsAddress(trim(@$ARR['kinaddress']));
	$pat->setKinRelationship((new KinRelationDAO())->get(@$ARR['kin_relation_id']));
	$reg = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true);
	
	if ($reg == null) {
		return 'error:Unidentified staff, please re-login';
	}
	$pat->setRegisteredBy($reg);
	//$pat->setPhoneNumber(@$ARR['phonen']);
	$pat->setForeignNumber(trim(@$ARR['patIntNumber']));
	$pat->setBloodGroup(@$ARR['bloodg']);
	$pat->setBloodType(@$ARR['bloodt']);
	$pat->setBaseClinic((new ClinicDAO())->getClinic(@$ARR['hospital']));
	//    $pat->setTransferedTo($reg->getClinic());
	
	if (isset($ARR['socio_economic'])) {
		$socio = new SocioEconomicStatus();
		$socio->setId(@$ARR['socio_economic']);
		$pat->setSocioEconomic($socio);
	} else {
		$pat->setSocioEconomic(null);
	}
	
	if (isset($ARR['lifestyle'])) {
		$lStyle = new LifeStyle();
		$ids = implode("|", (array)@$ARR['lifestyle']);
		$lStyle->setIds($ids);
		$pat->setLifeStyle($lStyle);
	} else {
		$pat->setLifeStyle(null);
	}
	
	if (isset($ARR['care_manager_id'])) {
		$pat->setCareManager((new StaffDirectoryDAO())->getStaff($ARR['care_manager_id']));
	} else {
		$pat->setCareManager(null);
	}
	
	// do validation first
	if (trim($pat->getFname()) == '' || trim($pat->getLname()) == '') {
		return 'error:First and last name required.';
	} else if (trim($pat->getSex()) == "--") {
		return 'error:Gender required.';
	} else if (trim($pat->getDateOfBirth()) == "") {
		return 'error:Invalid date of birth';
	} else if (trim($pat->getPhoneNumber()) !== "" && !(preg_match('/^[+][2][3][4]+[0-9]{10}$/', $pat->getPhoneNumber()) || preg_match('/^[0]+[0-9]{10}$/', $pat->getPhoneNumber()))) {
		return 'error:Valid Phone number is required.';
	} // continue other validations if required
	
	$ins = new Insurance();
	
	if (@$ARR['insuranceClass'] == '') {
		return 'error:Please select Insurance provider';
	} else {
		$scheme = (new InsuranceSchemeDAO())->get(@$ARR['insuranceClass'], false);
		$pat->setScheme($scheme);
		$sch = $scheme;
		//$sch->setId(@$ARR['insuranceClass']);
		//$sch->setType($scheme->getType());
		//$sch->setName($scheme->getName());
		
		if( (new InsuranceDAO())->getInsuranceCurrentSize(@$ARR['insuranceClass']) >= $scheme->getEnroleesMax() ){
			return 'error:Insurance Scheme max has been reached';
		}
		
		if ($scheme->getType() == "self") {
			$ins->setExpirationDate(null);
			$ins->setPolicyNumber(null);
			$ins->setEnrolleeId(null);
			$ins->setCoverageType(null);
			$ins->setCompany(null);
			$ins->setDependent(null);
		} else {
			if (@$ARR['insuranceExpiration'] == '') {
				return 'error:Please select Insurance Expiration Date';
			} else if (@$ARR['coverage_type'] == '') {
				return 'error:Coverage Type is required';
			} else if (is_blank($ARR['enrollee_number'])) {
				return 'error:Enrollee Id is required';
			} else {
				
				$exp_date = DateTime::createFromFormat('d/m/Y', @$ARR['insuranceExpiration'])->format('Y-m-d');
				$ins->setExpirationDate($exp_date);
				$ins->setPolicyNumber(@$ARR['policy_number']);
				$ins->setEnrolleeId(@$ARR['enrollee_number']);
				$ins->setCoverageType(@$ARR['coverage_type']);
				
				if (@$ARR['coverage_type'] !== 'Principal' && is_blank(@$ARR['dependent_id']) && @$ARR['external_parent'] != 'on') {
					//dependent must be provided
					return 'error:Dependent is required';
				} else if (@$ARR['coverage_type'] !== 'Principal' && is_blank(@$ARR['dependent_id']) && @$ARR['external_parent'] == 'on' && is_blank($ARR['parent_enrollee_id'])) {
					//dependent must be provided
					return 'error:Principal Enrollee\'s ID is required';
				} else if (@$ARR['coverage_type'] !== 'Principal' && is_blank(@$ARR['dependent_id']) && @$ARR['external_parent'] == 'on' && !is_blank($ARR['parent_enrollee_id'])) {
					$ins->setParentEnrolleeId($_POST['parent_enrollee_id']);
					$ins->setExternal(true);
				} else if (@$ARR['coverage_type'] !== 'Principal' && !is_blank(@$ARR['dependent_id']) && @$ARR['external_parent'] !== 'on') {
					if (@$ARR['pid'] != @$ARR['dependent_id']) {
						$ins->setDependent(new PatientDemograph(@$ARR['dependent_id']));
						$ins->setExternal(false);
					} else {
						return 'error:Patient should not depend on itself';
					}
					
				} else if (@$ARR['coverage_type'] === 'Principal') {
					$ins->setDependent(null);
				} else {
					$ins->setDependent(null);
				}
				
				if (!is_blank(@$ARR['company_id'])) {
					$ins->setCompany((new Company(@$ARR['company_id'])));
				} else {
					$ins->setCompany(null);
				}
			}
		}
	}
	
	$ins->setScheme($sch);
	if (isset($ARR['pid'])) {
		$pat->setId($ARR['pid']);
		$ins->setPatient($pat);
	}
	$pat->setInsurance($ins);
	
	$_contacts = json_decode(@$ARR['phonen']);
	$contacts = [];
	
	
	
	foreach ($_contacts as $contact) {
		$contacts[] = (new Contact())->setType($contact->type)->setCountry((new Country())->get($contact->country_id))->setPhone($contact->phone)->setPrimary(isset($contact->primary) ? true : false)->setRelation('self');
	}
	
	//if($contacts == null){
	//	exit("error:Patient contact number is required");
	//}
	$pat->setContacts($contacts);
	return ['success', $pat];
}

function getItem($iCode, $pdo)
{
	if (strpos($iCode, 'VC') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VaccineDAO.php';
		$item = (new VaccineDAO())->getVaccineByCode($iCode, $pdo);
	} else if (substr($iCode, 0, 3) === 'DR0') {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
		$item = (new DrugDAO())->getDrugByCode($iCode, false, $pdo);
	} else if (strpos($iCode, 'LA') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
		$item = (new LabDAO())->getLabByCode($iCode, false, $pdo);
	} else if (strpos($iCode, 'CO') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
		//not a flat object yet because of usage
		$item = (new StaffSpecializationDAO())->getSpecializationByCode($iCode, $pdo);
	} else if (strpos($iCode, 'SC') !== false) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
		//not a flat object yet because of usage
		$item = (new ScanDAO())->getScanByCode($iCode, $pdo);
	} else if (substr($iCode, 0, 2) === 'RT') {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/RoomTypeDAO.php';
		$item = (new RoomTypeDAO())->getRoomTypeByCode($iCode, false, $pdo);
	} else if (strpos($iCode, 'AD') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AdmissionConfigurationDAO.php';
		$item = (new AdmissionConfigurationDAO())->getAdmissionConfigurationByCode($iCode, $pdo);
	} else if (strpos($iCode, 'PR') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureDAO.php';
		$item = (new ProcedureDAO())->getProcedureByCode($iCode, $pdo);
	} else if (strpos($iCode, 'IT') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
		//not yet a flat object
		$item = (new ItemDAO())->getItemByCode($iCode, $pdo);
	} else if (strpos($iCode, 'OP') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyDAO.php';
		$item = (new OphthalmologyDAO())->getByCode($iCode, true, $pdo);
	} else if (strpos($iCode, 'DT') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryDAO.php';
		$item = (new DentistryDAO())->getByCode($iCode, $pdo);
	} else if (strpos($iCode, 'NS') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/NursingServiceDAO.php';
		$item = (new NursingServiceDAO())->getByCode($iCode, $pdo);
	} else if (strpos($iCode, 'WR') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/WardDAO.php';
		$item = (new WardDAO())->getByCode($iCode, $pdo);
	} else if (strpos($iCode, 'PI') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyItemDAO.php';
		$item = (new OphthalmologyItemDAO())->getByCode($iCode, $pdo);
	} else if (strpos($iCode, 'IP') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PhysiotherapyItemDAO.php';
		//not yet a flat object
		$item = (new PhysiotherapyItemDAO())->getByCode($iCode, $pdo);
	} else if (strpos($iCode, 'ME') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/MedicalExamDAO.php';
		$item = (new MedicalExamDAO())->getByCode($iCode, $pdo);
	} else if (strpos($iCode, 'MS') !== false) {
		$item = (new MiscellaneousItem())->setCode('MS00001')->setId(1)->setName('Miscellaneous Item');
	} else if (is_dir($_SERVER ['DOCUMENT_ROOT'] . "/antenatal") && strpos($iCode, 'AP') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackagesDAO.php';
		//not yet a flat object
		$item = (new AntenatalPackagesDAO())->getByCode($iCode, $pdo);
	} else if (is_dir($_SERVER ['DOCUMENT_ROOT'] . "/ivf") && strpos($iCode, 'PGD') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticLabDAO.php';
		//not yet a flat object
		$item = (new GeneticLabDAO())->getByCode($iCode, $pdo);
	} else if (is_dir($_SERVER ['DOCUMENT_ROOT'] . "/ivf") && strpos($iCode, 'IVP') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/IVFPackageDAO.php';
		//not yet a flat object
		$item = (new IVFPackageDAO())->getByCode($iCode, $pdo);
	} else if (strpos($iCode, 'PKG') !== false) {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PackageDAO.php';
		//not yet a flat object
		$item = (new PackageDAO())->getByCode($iCode, $pdo);
	} else if (substr($iCode, 0, 3) === 'DRT') {
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DRTDAO.php';
		//not yet a flat object
		$item = (new DRTDAO())->getByCode($iCode, $pdo);
	} else {
		error_log("========" . $iCode);
		$item = null;//$iCode;
		// $item should be set to null so that the other properties won't be accessible, even though they are not, really
	}
	return $item;
}

function getAntenatalItemType($iCode)
{
	if (strpos($iCode, 'VC') !== false) {
		return 'Vaccine';
	} else if (substr($iCode, 0, 3) === 'DR0') {
		return 'Drug';
	} else if (strpos($iCode, 'LA') !== false) {
		return 'Lab';
	} else if (strpos($iCode, 'CO') !== false) {
		return 'Consultation';
	} else if (strpos($iCode, 'SC') !== false) {
		return 'Scan';
	} else if (substr($iCode, 0, 2) === 'RT') {
		return null;
	} else if (strpos($iCode, 'AD') !== false) {
		return null;
	} else if (strpos($iCode, 'PR') !== false) {
		return 'Procedure';
	} else if (strpos($iCode, 'IT') !== false) {
		return null;
	} else if (strpos($iCode, 'OP') !== false) {
		return null;
	} else if (strpos($iCode, 'DT') !== false) {
		return null;
	} else if (strpos($iCode, 'NS') !== false) {
		return null;
	} else if (strpos($iCode, 'WR') !== false) {
		return null;
	} else if (strpos($iCode, 'PI') !== false) {
		return null;
	} else if (strpos($iCode, 'IP') !== false) {
		return null;
	} else if (strpos($iCode, 'ME') !== false) {
		return null;
	} else if (strpos($iCode, 'MS') !== false) {
		return null;
	} else if (is_dir($_SERVER ['DOCUMENT_ROOT'] . "/antenatal") && strpos($iCode, 'AP') !== false) {
		return null;
	} else if (is_dir($_SERVER ['DOCUMENT_ROOT'] . "/ivf") && strpos($iCode, 'PGD') !== false) {
		return null;
	} else if (is_dir($_SERVER ['DOCUMENT_ROOT'] . "/ivf") && strpos($iCode, 'IVP') !== false) {
		return null;
	} else if (strpos($iCode, 'PKG') !== false) {
		return null;
	} else if (substr($iCode, 0, 3) === 'DRT') {
		return null;
	} else {
		return null;
	}
}

function in8nPhone($phone_)
{
	//get the dialling code of this patients' country
	//FIX, let it be Nigerian numbers only
	$rPLUS = "/^\+/";
	//strip the plus, if there
	$phone = preg_replace($rPLUS, "", $phone_);
	$ptn = "/^0/"; // Regex
	$str = $phone; //
	$rpltxt = "234"; // Replacement string
	$i81n_phone = preg_replace($ptn, $rpltxt, $str);
	if (strlen($i81n_phone) != 13) {
	    return "";
		//return ("error:SMS to phone number;$i81n_phone not supported");
	} else {
		return $i81n_phone;
	}
}

function convertEnumToArray($enum)
{
	//    "SELECT SUBSTRING(COLUMN_TYPE,5) as type FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='medicplus'  AND TABLE_NAME='appointment_group' AND COLUMN_NAME='type'";
	$enum = str_replace("(", "", $enum);
	$enum = str_replace(")", "", $enum);
	$enum = str_replace("'", "", $enum);
	$arr = explode(",", $enum);
	return $arr;
}

function convertAppointmentGroupToEvent($apps)
{
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentGroupDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AptClinicDAO.php';
	$options = (new AptClinicDAO())->names();
	
	$aids = [];
	$events = [];
	$byClinics = [];
	
	$e['aids'] = [];
	
	$x = 0;
	
	foreach ($apps as $app) {
		$event['id'] = $app->group_id;
		$event['clinic_name'] = str_replace("&", "β", $app->clinic_name);
		$event['start'] = $app->start_time;
		$event['end'] = $app->end_time;
		//$event['url'] = "https://www.google.com/calendar/feeds/pauldiconline%40gmail.com/public/basic";
		$event['aid'] = $app->id;
		$event['startEditable'] = true;
		$event['durationEditable'] = true;
		$event['timezone'] = date_default_timezone_get();
		$event['isAllDay'] = $app->is_all_day;
		$event['color'] = getEventColor($app->clinic_name, $options);
		$event['patient'] = ($app->patient_id === null ? "" : $app->patient_id);
		$invitees = [];
		$event['invitees'] = $invitees;
		
		/**
		 * If the ids is found in array I shouldn't add it again iff part of the contents is All Day event
		 * Note: the idea is that we group all individual events as one but allow the ones that span through multiple days repeat
		 */
		//sorry the above comment does not apply to the below function
		
		//$start = explode(" ", $app->start_time)[0];
		//$end = (($app->end_time === null || $app->end_time === "") ? "" : explode(" ", $app->end_time)[0]);
		
		if (!in_array($app->clinic_name . "," . $app->start_time, $byClinics)) {
			$x = 1;
			$aids = [];
			$byClinics[] = $app->clinic_name . "," . $app->start_time;
			$events[] = $event;
		} else {
			$x += 1;
		}
		$aids[] = $app->id;
		
		if ($x > 1) {
			$events[sizeof($events) - 1]['title'] = $events[sizeof($events) - 1]['clinic_name'] . " ($x)";
			$events[sizeof($events) - 1]['count'] = $x;
		} else {
			$events[sizeof($events) - 1]['title'] = $events[sizeof($events) - 1]['clinic_name'] . " ";
			$events[sizeof($events) - 1]['count'] = 1;
		}
		
		$events[sizeof($events) - 1]['aids'] = $aids;
		
		/*if (in_array($app->clinic_name, $aids) && $app->is_all_day && $start === $end) {
			$aids[] = $app->clinic_name;
			$events[] = $event;
		} else if (!in_array($app->clinic_name, $aids)) {
				$aids[] = $app->clinic_name;
				$events[] = $event;
		}*/
		
		
	}
	return $events;
}

function convertAppointmentGroupToEventBkUp($apps)
{
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentGroupDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AptClinicDAO.php';
	$options = (new AptClinicDAO())->names();
	
	$aids = [];
	$events = [];
	foreach ($apps as $app) {
		$event['id'] = $app->getGroup()->getId();
		$event['aids'] = $app->getIds();
		$event['title'] = str_replace("&", "β", $app->getGroup()->getClinic()->getName()) . ($app->getCount() === '1' ? "" : " (" . $app->getCount() . ")");
		$event['start'] = $app->getStartTime();
		$event['end'] = $app->getEndTime();
		//$event['url'] = "https://www.google.com/calendar/feeds/pauldiconline%40gmail.com/public/basic";
		$event['aid'] = $app->getId();
		$event['startEditable'] = true;
		$event['durationEditable'] = true;
		$event['timezone'] = date_default_timezone_get();
		$event['isAllDay'] = $app->getGroup()->isAllDay();
		$event['color'] = getEventColor($app->getGroup()->getClinic()->getName(), $options);
		$event['patient'] = ($app->getGroup()->getPatient() === null ? "" : $app->getGroup()->getPatient()->getId());
		$invitees = [];
		if (sizeof($app->getGroup()->getInvitees()) > 0) {
			foreach ($app->getGroup()->getInvitees() as $invitee) {
				$invitees[] = $invitee->getId();
			}
		}
		$event['invitees'] = $invitees;
		
		/**
		 * If the ids is found in array I shouldn't add it again iff part of the contents is All Day event
		 * Note: the idea is that we group all individual events as one but allow the ones that span through multiple days repeat
		 */
		$start = explode(" ", $app->getStartTime())[0];
		$end = (($app->getEndTime() === null || $app->getEndTime() === "") ? "" : explode(" ", $app->getEndTime())[0]);
		if (in_array($app->getIds(), $aids) && $app->getGroup()->isAllDay() && $start === $end) {
			$aids[] = $app->getIds();
			$events[] = $event;
		} else {
			if (!in_array($app->getIds(), $aids)) {
				$aids[] = $app->getIds();
				$events[] = $event;
			}
		}
	}
	return $events;
}

function convertAppointmentToEvent($apps)
{
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentGroupDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AptClinicDAO.php';
	$options = (new AptClinicDAO())->names();
	$events = [];
	foreach ($apps as $app) {
		$event['id'] = $app->group_id;
		$event['title'] = str_replace("&", "β", $app->clinic_name);
		$event['start'] = $app->start_time;
		$event['end'] = $app->end_time;
		$event['aid'] = $app->id;
		$event['isAllDay'] = $app->is_all_day;
		$event['description'] = $app->description;
		$event['color'] = getEventColor($app->clinic_name, $options);
		$event['pid'] = ($app->patient_id === null ? "" : $app->patient_id);
		$event['sTimezone'] = "Africa/Lagos";
		$event['eTimezone'] = "Africa/Lagos";
		$event['resource'] = $app->resource_id;
		$invitees = [];
		$event['invitees'] = $invitees;
		
		$aids[] = $app->id;
		$events[] = $event;
	}
	return $events;
}

function convertAppointmentToEventBkUp($apps)
{
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentGroupDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AptClinicDAO.php';
	$options = (new AptClinicDAO())->names();
	$events = [];
	foreach ($apps as $app) {
		$event['id'] = $app->getGroup()->getId();
		$event['title'] = str_replace("&", "*", $app->getGroup()->getClinic()->getName());
		$event['start'] = $app->getStartTime();
		$event['end'] = $app->getEndTime();
		$event['aid'] = $app->getId();
		$event['isAllDay'] = $app->getGroup()->isAllDay();
		$event['description'] = $app->getGroup()->getDescription();
		$event['color'] = getEventColor($app->getGroup()->getClinic()->getName(), $options);
		$event['pid'] = ($app->getGroup()->getPatient() === null ? "" : $app->getGroup()->getPatient()->getId());
		$event['sTimezone'] = "Africa/Lagos";
		$event['eTimezone'] = "Africa/Lagos";
		//        $event['timezone'] = date_default_timezone_get();
		$event['resource'] = $app->getGroup()->getResource();
		$invitees = [];
		
		if (sizeof($app->getGroup()->getInvitees()) > 0) {
			foreach ($app->getGroup()->getInvitees() as $invitee) {
				$invitees[] = $invitee->getId();
			}
		}
		$event['invitees'] = $invitees;
		
		$aids[] = $app->getIds();
		$events[] = $event;
	}
	return $events;
}


function getEventColor($type, $options)
{
	/*$colors = [];
	for($i=0; $i < 20; $i++) {
		$colors[] = '#' . strtoupper(dechex(rand(0,10000000)));
	}
	echo json_encode($colors);*/
	//$colors = ['#3A87AD', '#3AAD51', '#AD883A', '#5A5E88', '#883AAD'];
	$colors = ["#810424", "#2C231E", "#3AC505", "#5DC4F9", "#8D3E9A", "#2FE055", "#783D73", "#293336", "#2A598C", "#71DB53", "#91E28D", "#4D7FDE", "#7E85BA", "#644BC2", "#7D19E2", "#2B10C3", "#24AE3E", "#80610C", "#310E12", "#36AB0B", "#8C7F0B", "#95C400", "#614915", "#6079EF", "#847B41", "#55A6FE", "#8234AA", "#18CE46", "#43FD93", "#1B0986"];
	foreach ($options as $key => $option) {
		if ($option === $type) {
			return $colors[$key];
		}
	}
	return '#AD3A3F';
}


function generateBillableItemCode($category, $pdo)
{
	$code = null;
	try {
		$sql = "SELECT LPAD(auto_increment, 8, 0) AS val FROM information_schema.tables where table_schema = '" . $pdo->getDBName() . "' and table_name = '$category'";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			$code = $row['val'];
		}
		$stmt = null;
	} catch (PDOException $e) {
		errorLog($e);
		$stmt = null;
		$code = null;
	}
	return $code;
}

function getTypeOptions($col, $table, $pdo = null)
{
	$types = array();
	try {
		if ($pdo == null) {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			$conn = new MyDBConnector();
			$pdo = $conn->getPDO();
		}
		$sql = "SELECT SUBSTRING(COLUMN_TYPE,5) AS type FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='" . $pdo->getDBName() . "'  AND TABLE_NAME='" . $table . "' AND COLUMN_NAME='" . $col . "'";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			$type = $row["type"];
			$types = convertEnumToArray($type);
		} else {
			$types = [];
		}
		$stmt = null;
	} catch (PDOException $e) {
		errorLog($e);
		$types = [];
	}
	sort($types);
	return $types;
}


/**
 * Returns an array of all indexes of the given array where the string occur or [] if the string is not found in the array
 */
function indexOccurancesOfStringInArray($arr, $str)
{
	$indexs = [];
	foreach ($arr as $i => $val) {
		if ($val === $str) {
			$indexs[] = $i;
		}
	}
	return $indexs;
}


function escape($value)
{
	$search = array("\\", "\x00", "\n", "\r", "'", '"', "\x1a");
	$replace = array("\\\\", "\\0", "\\n", "\\r", "\'", '\"', "\\Z");
	
	return str_replace($search, $replace, $value);
}


function getLastVital($vitals, $type)
{
	foreach ($vitals as $v) {
		if ($v->getType() === $type) {
			return $v;
		}
	}
	return null;
}


function get_version()
{
	return trim(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/.~version', FILE_USE_INCLUDE_PATH | FILE_TEXT));
}


function is_blank($value)
{
	return !isset($value) || (empty(@$value) && !is_numeric(@$value));
}


//This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
function convertPHPSizeToBytes($sSize)
{
	if (is_numeric($sSize)) {
		return $sSize;
	}
	$sSuffix = substr($sSize, -1);
	$iValue = substr($sSize, 0, -1);
	switch (strtoupper($sSuffix)) {
		case 'P':
			$iValue *= 1024;
		case 'T':
			$iValue *= 1024;
		case 'G':
			$iValue *= 1024;
		case 'M':
			$iValue *= 1024;
		case 'K':
			$iValue *= 1024;
			break;
	}
	return $iValue;
}

function getMaximumFileUploadSize()
{
	return min(convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize')));
}


function wait()
{
	$min = 1;
	$max = 3;
	sleep(mt_rand(mt_getrandmax() * $min, mt_getrandmax() * $max) / mt_getrandmax());
}


function uuid()
{
	return intval(mt_rand(mt_getrandmax() * 1, mt_getrandmax() * 10) / mt_getrandmax() * 100000);
}


function errorLog($e)
{
	error_log($e->getMessage() . " at (" . $e->getFile() . ":" . $e->getLine() . ")[" . $e->getCode() . "]");
}

function quote_esc_str($str)
{
	return "'" . escape($str) . "'";
}

function truncate($str, $length, $incMore = false)
{
	if (strlen($str) > $length && $length > 0) {
		return substr($str, 0, $length) . (($incMore) ? ' &hellip; <span class="fadedText small">more<span>' : '');
	}
	return $str;
}

function is_real_number($num)
{
	return (bool)preg_match('/^[0-9]\d*(\.\d+)?$/', $num);
}

function get_index_by_value($value, $array)
{
	foreach ($array as $index => $string) {
		if ($string == $value) {
			return $index;
		}
	}
	return null;
}

function getAge($date)
{
	$date1 = new DateTime($date);
	$date2 = new DateTime(date("Y-m-d"));
	
	$interval = $date1->diff($date2);
	$years = $interval->y;
	$months = $interval->m;
	
	if ($years < 1) {
		return $months . " months";
	}
	return $years . " years";
}


function pluralize_($string, $count)
{
	$irregulars = array('man' => 'men', 'woman' => 'women', 'fungus' => 'fungi', 'thief' => 'thieves', 'species' => 'species', 'medium' => 'media', 'person' => 'people', 'echo' => 'echoes', 'hero' => 'heroes', 'potato' => 'potatoes', 'veto' => 'vetoes', 'auto' => 'autos', 'memo' => 'memos', 'pimento' => 'pimentos', 'pro' => 'pros');
	
	$es = array('s', 'z', 'ch', 'sh', 'x');
	
	$last_letter = $string{strlen($string) - 1};
	if ($count != 1) {
		if (array_key_exists($string, $irregulars)) {
			return $irregulars[$string];
		} else if ($last_letter == 'y') {
			if ($string{strlen($string) - 2} == 'e') {
				return substr($string, 0, strlen($string) - 2) . 'ies';
			} else {
				return substr($string, 0, strlen($string) - 1) . 'ies';
			}
		} else if (in_array(substr($string, 0, -2), $es) || in_array($last_letter, $es)) {
			return $string . 'es';
		} else {
			return $string . 's';
		}
	} else {
		return $string;
	}
}

function pluralize($word, $count)
{
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/Inflector.php';
	if (is_blank($word)) {
		return pluralize('unit', $count);
	}
	if ((int)$count !== 1) {
		return /*$count . ' ' .*/ (new Inflector())->pluralize($word);
	}
	return /*$count . ' ' . */(new Inflector())->singularize($word);
}

function validatePregnancies($gravida, $para, $alive, $miscarriages)
{
	if (is_blank($gravida)) {
		//exit("error:Gravida is blank");
	}
	if (is_blank($para)) {
		//exit("error:Para is blank");
	}
	if (!is_blank($para) && !is_blank($gravida) && (int)$para >= (int)$gravida) {
		//return("error:Para cannot be greater than or equal to Gravida");
	}
	if (is_blank($alive)) {
		//return("error:Alive is blank");
	}
	if (!is_blank($alive) && !is_blank($gravida) && (int)$alive > (int)$gravida) {
		//return("error:Alive cannot be greater than Gravida");
	}
	if (is_blank($miscarriages)) {
		//return("error:Miscarriages is blank");
	}
	if (!is_blank($miscarriages) && !is_blank($gravida) && (int)$miscarriages >= (int)$gravida) {
		//return("error:Wrong Miscarriages <-> Gravida relationship");
	}
	
	if ((int)$para != (int)$alive + (int)$miscarriages) {
		if ((int)$gravida > (int)$para + (int)$alive + (int)$miscarriages) {
		
		} else {
			//return("error:Gravida, Para, Miscarriages mismatch");
		}
	}
	return true;
}

function parseNumber($number)
{
	$fmt = new NumberFormatter('en_US', NumberFormatter::DECIMAL);
	return $fmt->parse($number);
}


function array_flatten($array)
{
	if (is_array($array)) {
		/** source http://www.cowburn.info/2012/03/17/flattening-a-multidimensional-array-in-php/ */
		return array_filter(array_unique(iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($array)), false)));
	}
	return [];
	
	
	/**source https://davidwalsh.name/flatten-nested-arrays-php
	 *
	 * $return = array();
	 * for($x = 0; $x <= count($array); $x++) {
	 * if(is_array($array[$x])) {
	 * $return = array_flatten($array[$x], $return);
	 * }
	 * else {
	 * if(isset($array[$x])) {
	 * $return[] = $array[$x];
	 * }
	 * }
	 * }
	 * return $return;*/
}

function sameSizeArray()
{
	$args = func_get_args();
	$sizes = array();
	foreach ($args as $index => $arg) {
		$sizes[] = count($args[$index]);
		unset($args[$index]);
	}
	return count(array_values(array_unique($sizes))) == 1 ? true : false;
}

//$output = ;

function weeksAndDays($date1, $date2){
	$dateDiff = (new DateTime($date1))->diff( (new DateTime($date2)) )->days;
	
	return floor($dateDiff/7).'w'. ( $dateDiff % 7 > 0 ? ($dateDiff % 7).'d' :'' );
	//floor((new DateTime($lmp))->diff( (new DateTime($a->getDate())) )->days/7).'w'. ((new DateTime($lmp))->diff( (new DateTime($a->getDate())) )->days%7).'d'
}

function arrayIterator($array, $prop){
	$data = array();
	//error_log(json_encode($array));
	foreach ($array as $item){
		//error_log($item->{$prop}());
		$data[] = $item->{$prop}();
	}
	
	return implode(', ', $data);
}

/**
 * @param      $array
 * @param      $columnKey
 * @param null $indexKey
 *
 * @return array
 * @see http://php.net/manual/en/function.array-column.php#119754
 */
function array_col($array, $columnKey, $indexKey = null)
{
	$result = array();
	foreach ($array as $subArray) {
		if (is_null($indexKey) && array_key_exists($columnKey, $subArray)) {
			$result[] = is_object($subArray)?$subArray->$columnKey: $subArray[$columnKey];
		} elseif (array_key_exists($indexKey, $subArray)) {
			if (is_null($columnKey)) {
				$index = is_object($subArray)?$subArray->$indexKey: $subArray[$indexKey];
				$result[$index] = $subArray;
			} elseif (array_key_exists($columnKey, $subArray)) {
				$index = is_object($subArray)?$subArray->$indexKey: $subArray[$indexKey];
				$result[$index] = is_object($subArray)?$subArray->$columnKey: $subArray[$columnKey];
			}
		}
	}
	return $result;
}
