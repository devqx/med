<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StaffDAO
 *
 * @author pauldic
 */
class PatientDemographDAO
{

	private $conn = null;
	private $idLength = 10;

	function __construct()
	{
		try {
			date_default_timezone_set("Africa/Lagos");
			if (!isset($_SESSION)) {
				@session_start();
			}

			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/MedicPlusException.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LGA.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/State.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/VitalSign.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LifeStyle.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Insurance.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/SocioEconomicStatus.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LGADAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StateDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LifeStyleDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/SocioEconomicStatusDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Religion.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Registration.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReligionDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralCompanyDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/KinRelationDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/IndustryDAO.php';
			
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/CreditLimit.php';
			
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/MessageDispatch.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/MessageDispatchDAO.php';
			
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DistrictDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ContactDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SpokenLanguageDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SpokenLanguage.php';
			
			require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Country.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			errorLog($e);
		}
	}
	
	function addPatient($pat, $fakeId = null)
	{
		//$pat = new PatientDemograph();
		$pid = $this->generatePatientID();
		try {
			$pdo = $this->conn->getPDO();
			$pdo->beginTransaction();
			
			$folioNumber = is_null($pat->getLegacyId()) ? $this->_genFolioNumber((new ClinicDAO())->getClinic($pat->getBaseClinic()->getId(), false, $pdo)->getFolioPrefix(), false, $pdo) : $pat->getLegacyId();
			
			$referral_id = ($pat->getReferral() !== null) ? $pat->getReferral()->getId() : "NULL";
			$spoken_lang = ($pat->getSpokenLang() !== null) ? $pat->getSpokenLang()->getId() : "NULL";
			$referral_company_id = $pat->getReferralCompany() ? $pat->getReferralCompany()->getId() : "null";
			$kinRelationship = ($pat->getKinRelationship() != null) ? $pat->getKinRelationship()->getId() : "NULL";
			$title = ($pat->getTitle()) ? "'" . escape($pat->getTitle()) . "'" : "NULL";
			$careMgrId = ($pat->getCareManager() !== null) ? $pat->getCareManager()->getId() : "NULL";
			$schemeAtReg = $pat->getScheme()->getId();
			$dobEstimated = var_export($pat->getDobEstimated(), true);
			$socioEconomicStatus = $pat->getSocioEconomic() ? $pat->getSocioEconomic()->getId() : 'NULL';
			$districtId = $pat->getDistrict() ? $pat->getDistrict()->getId() : 'NULL';
			$districtResId = $pat->getDistrictRes() ? $pat->getDistrictRes()->getId() : 'NULL';
			$industryId = $pat->getIndustry() ? $pat->getIndustry()->getId() : 'NULL';
			$ethnic = $pat->getEthnic() ? quote_esc_str($pat->getEthnic()) : "NULL";
			
			$sql = "INSERT INTO patient_demograph (patient_ID, legacy_patient_id, title, fname, lname, mname, date_of_birth, dob_estimated, sex, email, nationality, address, lga_id, district_id, state_id, state_res_id, lga_res_id, district_res_id,  KinsFirstName, KinsLastName, KinsPhone, KinsAddress, kin_relation_id, registered_By, phonenumber, foreign_number, bloodgroup, bloodtype, basehospital, socio_economic, lifestyle, religion_id, occupation, work_address, industry_id, referral_id, referral_company_id, care_manager_id, scheme_at_registration_id, language_id, ethnic) VALUES " . "('" . $pid . "', '" . escape($folioNumber) . "', $title,'" . escape(ucfirst($pat->getFname())) . "', '" . escape(ucfirst($pat->getLname())) . "',  '" . escape(ucfirst($pat->getMname())) . "', '" . $pat->getDateOfBirth() . "', $dobEstimated, '" . $pat->getSex() . "', '" . escape($pat->getEmail()) . "', '" . $pat->getNationality()->id . "', '" . escape(ucwords($pat->getAddress())) . "', " . $pat->getLga()->getId() . ", $districtId, " . $pat->getState()->getId() . ", " . $pat->getResState()->getId() . ", " . $pat->getResLga()->getId() . ", $districtResId, " . "'" . escape(ucfirst($pat->getKinsFirstName())) . "',  '" . escape(ucfirst($pat->getKinsLastName())) . "', '',  '" . escape(ucwords($pat->getKinsAddress())) . "', $kinRelationship, '" . $pat->getRegisteredBy()->getId() . "', '" . $pat->getPhoneNumber() . "', '" . $pat->getForeignNumber() . "', '" . strtoupper($pat->getBloodGroup()) . "', '" . strtoupper($pat->getBloodType()) . "', '" . $pat->getBaseClinic()->getId() . "', " . $socioEconomicStatus . ", " . ($pat->getLifeStyle() ? quote_esc_str($pat->getLifeStyle()->getIds()) : "NULL") . ", " . ($pat->getReligion() != null && !empty($pat->getReligion()) ? $pat->getReligion()->getId() : "NULL") . ", '" . escape($pat->getOccupation()) . "', '" . escape($pat->getWorkAddress()) . "', $industryId, $referral_id, $referral_company_id, $careMgrId, $schemeAtReg, $spoken_lang, $ethnic)";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$pat->setId($pid);
				$ins = $pat->getInsurance();
				$ins->setPatient($pat);
				//charge this patient for registration
				//but on-behalf of this scheme
				$bil = new Bill();
				$bil->setPatient($pat); //set this to null and then
				//make sure the $ins object contains the `getType` attribute
				$bil->setDescription("Beneficiary enrollment charge - ");
				
				$bil->setItem(new Registration());
				$bil->setSource((new BillSourceDAO())->findSourceById(10, $pdo));
				$bil->setTransactionType("credit");
				
				$scheme = (new InsuranceSchemeDAO())->get($ins->getScheme()->getId(), true, $pdo);
				$amount = $scheme->getIndividualRegCost();
				$bil->setAmount($amount);
				$bil->setDiscounted(null);
				$bil->setDiscountedBy(null);
				$bil->setReferral($pat->getReferral());
				
				$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
				$bil->setClinic($staff->getClinic());
				$bil->setBilledTo($scheme);
				
				
				//if($amount > 0){
				$bill = (new BillDAO())->addBill($bil, 1, $pdo, null);
				//todo fix with the multiple contacts
				if (!is_blank($pat->getPhoneNumber())) {
					$message = new MessageDispatch();
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
					$clinic = new Clinic();
					if ($clinic::$editStyleByAdd) {
						$messageText = sprintf(MainConfig::$welcomeMessage, $pat->getFullname(), $staff->getClinic()->getName(), $pat->getId());
					} else {
						$messageText = sprintf(MainConfig::$welcomeMessage, $pat->getId());
					}
					
					$message->setMessage($messageText);
					$message->setSmsChannelAddress(in8nPhone($pat->getPhoneNumber()));
					
					(new MessageDispatchDAO())->sendItem($message, 1, null, $pdo);
				}
				
				//}
			} else {
				error_log("***failed to insert into demograph");
				$pdo->rollBack();
				return null;
			}
			
			
			foreach ($pat->getContacts() as $contact) {
				//$contact = new Contact();
				$contact->setPatient($pat)->add($pdo);
			}
			if ($pat->getKinsPhone()) {
				$pat->getKinsPhone()->setPatient($pat)->add($pdo);
			}
			
			$lim = new CreditLimit();
			$lim->setPatient($pat);
			$lim->setAmount(0);
			$newLimit = (new CreditLimitDAO())->addPatientLimit($lim, $pdo);
			$ins = (new InsuranceDAO())->addInsurance($ins, $pdo);

			if ($ins === null || $newLimit === null) {
				if (is_null($ins)) {
					error_log("***insurance is null");
				}
				
				if (is_null($newLimit)) {
					error_log("***credit issue wahala");
				}
				$pdo->rollBack();
				$stmt = null;
				return null;
			} else {
				if (!is_null($fakeId)) {
					$stmt_ = $pdo->prepare("DELETE FROM fake_contact WHERE fake_patient_id = $fakeId", array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					if (!$stmt_->execute()) {
						$pdo->rollBack();
						return null;
					}
					
					$stmt__ = $pdo->prepare("DELETE FROM fake_patient WHERE id = $fakeId", array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					if (!$stmt__->execute()) {
						$pdo->rollBack();
						return null;
					}
				}
				
				$pdo->commit();
				$pat->setInsurance($ins);
			}
			
			return $pat;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function getPatientAsArray($pid, $getFull = false, $pdo = null, $all = null)
	{//$all=NULL: means ONLY Active, $all=TRUE: means BOTH Active And InActive, $all=FALSE: InActive
		$pat = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_demograph WHERE (patient_ID='" . $pid . "' AND " . ($all === null ? "active=1" : (((bool)$all) ? "(active=1 OR active=0))" : "active=0")) . ")";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pat['id'] = $row["patient_ID"];
				$pat['lid'] = $row["legacy_patient_id"];
				$pat['phone'] = $row["phonenumber"];
				$pat['fullname'] = $row["lname"] . " " . $row["fname"] . " " . $row["mname"];
				$pat['sex'] = $row['sex'];
				$pat['dob'] = $row['date_of_birth'];
				if ($getFull) {
					//TODO Other fiels
				}
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$stmt = null;
			$pat = [];
		}
		return $pat;
	}
	
	function searchPatientNames($term, $limit = 100, $asArray = false, $plusMed = false, $pdo = null, $all = null)
	{//$all=NULL: means ONLY Active, $all=TRUE: means BOTH Active And InActive, $all=FALSE: InActive
		$pats = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			
			if (is_object($term)) {
				$query = " AND sex='" . $term->sex . "'";
				$term = $term->text;
			} else {
				$query = "";
			}
			$aiSearch = "";// !is_blank($term) ? "MATCH (`fname`, `lname`, `legacy_patient_id`, `mname`, `phonenumber`) AGAINST ('".$term."' IN BOOLEAN MODE) AS Relevance, ":"";
			$sql = "SELECT IS_ADMITTED(pd.patient_ID) AS is_admitted,{$aiSearch}pd.bloodgroup, pd.bloodtype, pd.title, pd.patient_ID, pd.patient_ID AS patientId, pd.legacy_patient_id, fname, lname, mname, CONCAT_WS(' ', fname, lname, mname) AS fullname, sex, date_of_birth, phonenumber FROM patient_demograph pd LEFT JOIN insurance i ON i.patient_id=pd.patient_ID WHERE " . ($all === null ? "pd.active=1" : (((bool)$all) ? "(pd.active=1 OR pd.active=0))" : "pd.active=0")) . $query;
			$sql .= " AND (legacy_patient_id LIKE '%$term%' OR phonenumber LIKE '$term%' OR fname LIKE '$term%' OR mname LIKE '$term%' OR lname LIKE '$term%' OR pd.patient_ID='$term' ) #OR MATCH (`fname`, `lname`, `legacy_patient_id`, `mname`, `phonenumber`) AGAINST ('$term' IN BOOLEAN MODE)) ORDER BY Relevance DESC";
			$sql .= " LIMIT " . (isset($limit) ? $limit : 100);
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($asArray) {
				while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
					$pat = array();
					$pat['id'] = $row["patient_ID"];
					$pat['lid'] = $row["legacy_patient_id"];
					$pat['phone'] = $row["phonenumber"];
					$pat['fullname'] = $row["lname"] . " " . $row["fname"] . " " . $row["mname"];
					$pat['sex'] = $row['sex'];
					$pat['dob'] = $row['date_of_birth'];
					$pat['insurance'] = (new InsuranceDAO())->getInsurance($row["patient_ID"], false, $pdo);
					if ($plusMed) {
						$pat['vitalSigns'] = (new VitalSignDAO())->getPatientVitalSigns($row["patient_ID"], false, $pdo);
					}
					$pat['passportPath'] = $this->getPatientImage($row['patient_ID'], $row['sex']);
					$pats[] = $pat;
				}
			} else {
				while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
					// $pat = $this->getPatient($row['patient_ID'], FALSE, $pdo);
					$pat = $row;
					$pat['insurance'] = (new InsuranceDAO())->getInsurance($row["patient_ID"], false, $pdo);
					
					if ($plusMed) {
						$pat->setVitalSigns((new VitalSignDAO())->getPatientVitalSigns($row["patient_ID"], false, $pdo));
					}
					$pats[] = $pat;
				}
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			errorLog($e);
			$pats = [];
		}
		return $pats;
	}
	
	function getPatientMedical($pid, $getFull = false, $pdo = null, $all = null, $details = false)
	{//$all=NULL: means ONLY Active, $all=TRUE: means BOTH Active And InActive, $all=FALSE: InActive
		$pat = new PatientDemograph();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$filter = ($all === null ? "active=1" : (((bool)$all) ? "(active=1 OR active=0))" : "active=0"));
			$sql = "SELECT IS_ADMITTED(patient_ID) AS is_admitted, * FROM patient_demograph WHERE (patient_ID= $pid AND $filter)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pat->setActive((bool)$row["active"]);
				$pat->setId($row["patient_ID"]);
				$pat->setLegacyId($row["legacy_patient_id"]);
				$pat->setTitle($row["title"]);
				$pat->setLoginId($row["login_id"]);
				$pat->setFname($row["fname"]);
				$pat->setLname($row["lname"]);
				$pat->setMname($row["mname"]);
				$pat->setDateOfBirth($row["date_of_birth"]);
				$pat->setSex($row["sex"]);
				$pat->setEmail($row["email"]);
				$pat->setAddress($row["address"]);
				if ($getFull && $details) {
					$lga = (new LGADAO())->getLGA($row["lga_id"], true, $pdo);
					$resLga = (new LGADAO())->getLGA($row["lga_res_id"], true, $pdo);
					$state = (new StateDAO())->getState($row["state_id"], $pdo);
					$resState = (new StateDAO())->getState($row["state_res_id"], $pdo);
					$clinic = (new ClinicDAO())->getClinic($row["basehospital"], true, $pdo);
					$ses = (new SocioEconomicStatusDAO())->getSocioEconomicStatus($row["socio_economic"], $pdo);
					$ins = (new InsuranceDAO())->getInsurance($row["patient_ID"], true, $pdo);
				} else if ($getFull) {
					$lga = (new LGADAO())->getLGA($row["lga_id"], false, $pdo);
					$resLga = (new LGADAO())->getLGA($row["lga_res_id"], false, $pdo);
					$state = (new StateDAO())->getState($row["state_id"], $pdo);
					$resState = (new StateDAO())->getState($row["state_res_id"], $pdo);
					$clinic = (new ClinicDAO())->getClinic($row["basehospital"], false, $pdo);
					$ses = (new SocioEconomicStatusDAO())->getSocioEconomicStatus($row["socio_economic"], $pdo);
					$ins = (new InsuranceDAO())->getInsurance($row["patient_ID"], true, $pdo);
				} else {
					$lga = new LGA($row['lga_id']);
					$resLga = new LGA($row['lga_res_id']);
					$state = new State($row['state_id']);
					$resState = new State($row['state_res_id']);
					$clinic = new Clinic($row['basehospital']);
					$ses = new SocioEconomicStatus($row['socio_economic']);
					$ins = new Insurance($row["patient_ID"]);
				}
				$pat->setLga($lga);
				$pat->setResLga($resLga);
				$pat->setState($state);
				$pat->setResState($resState);
				$pat->setKinsFirstName($row["KinsFirstName"]);
				$pat->setKinsLastName($row["KinsLastName"]);
				$pat->setKinsPhone($row["KinsPhone"]);
				$pat->setKinsAddress($row["KinsAddress"]);
				$pat->setRegisteredBy($row["registered_By"]);
				$pat->setPhoneNumber($row["phonenumber"]);
				$pat->setForeignNumber($row["foreign_number"]);
				$pat->setBloodGroup($row["bloodgroup"]);
				$pat->setBloodType($row["bloodtype"]);
				$pat->setBaseClinic($clinic); //obj
				$pat->setTransferedTo($row["transferedto"]);
				$pat->setEnrollmentDate($row["enrollment_date"]);
				$pat->setSocioEconomic($ses); //obj
				$pat->setLifeStyle($row["lifestyle"]);
				$pat->setPassportPath($this->getPatientImage($row['patient_ID'], $row['sex']));
				$pat->setInsurance($ins); //obj
				$pat->setVitalSigns((new VitalSignDAO())->getPatientVitalSigns($row["patient_ID"], null, null, $pdo));
				$pat->setKinRelationship((new KinRelationDAO())->get($row['kin_relation_id'], $pdo));
				$pat->setCareManager((new StaffDirectoryDAO())->getStaff($row['care_manager_id'], false, $pdo));
				$pat->setNumDaysOnAdmission($row['cum_annual_days_on_admission']);
			} else {
				$pat = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$pat = null;
		}
		return $pat;
	}
	
	function getPatientMedicalMin($pid, $pdo = null, $all = null)
	{//$all=NULL: means ONLY Active, $all=TRUE: means BOTH Active And InActive, $all=FALSE: InActive
		$pat = new PatientDemograph();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$filter = ($all === null ? "active=1" : (((bool)$all) ? "(active=1 OR active=0))" : "active=0"));
			$sql = "SELECT IS_ADMITTED(patient_ID) AS is_admitted,* FROM patient_demograph WHERE (patient_ID=" . $pid . " AND $filter)";
			//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pat->setActive((bool)$row["active"]);
				$pat->setId($row["patient_ID"]);
				$pat->setLegacyId($row["legacy_patient_id"]);
				$pat->setTitle($row["title"]);
				$pat->setFname($row["fname"]);
				$pat->setLname($row["lname"]);
				$pat->setMname($row["mname"]);
				$pat->setEthnic($row['ethnic']);
				$pat->setDateOfBirth($row["date_of_birth"]);
				$pat->setSex($row["sex"]);
				$pat->setPhoneNumber($row["phonenumber"]);
				$pat->setForeignNumber($row["foreign_number"]);
				$pat->setInsurance((new InsuranceDAO())->getInsurance($row["patient_ID"], true, $pdo));
				$pat->setPassportPath($this->getPatientImage($row['patient_ID'], $row['sex']));
				$pat->setVitalSigns((new VitalSignDAO())->getPatientVitalSigns($row["patient_ID"], null, null, false, $pdo));
				$pat->setNumDaysOnAdmission($row['cum_annual_days_on_admission']);
			} else {
				$pat = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$pat = null;
		}
		return $pat;
	}
	
	// ($pid, true,NULL,TRUE) fails. why? i don't know
	function getPatient($pid, $getFull = false, $pdo = null, $all = null)
	{ // $all=NULL: means ONLY Active, $all=TRUE: means BOTH Active And InActive, $all=FALSE: InActive
		if (is_array($pid)) {
			$pid = $pid[0];
		}
		if (is_blank($pid)) {
			return null;
		}
		$pat = new PatientDemograph();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			
			switch ($all) {
				case null:
					$filter = "d.active = 1";
					break;
				case "TRUE":
					$filter = "(d.active = 1 OR d.active = 0)";
					break;
				case "FALSE":
					$filter = "d.active = 0";
					break;
				default:
					$filter = "d.active = 0";
			}
			$sql = "SELECT IS_ADMITTED(d.patient_ID) AS is_admitted, d.*, i.insurance_scheme FROM patient_demograph d LEFT JOIN insurance i ON d.patient_ID=i.patient_id WHERE d.patient_ID=" . $pid . " AND $filter";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pat->setActive((bool)$row["active"]);
				$pat->setIsAdmitted((bool)$row['is_admitted']);
				$pat->setId($row["patient_ID"]);
				$pat->setLegacyId($row["legacy_patient_id"]);
				$pat->setTitle($row["title"]);
				$pat->setLoginId($row["login_id"]);
				$pat->setFname($row["fname"]);
				$pat->setLname($row["lname"]);
				$pat->setMname($row["mname"]);
        $pat->setEthnic($row['ethnic']);
        $pat->setDateOfBirth($row["date_of_birth"]);
				$pat->setDobEstimated((bool)$row['dob_estimated']);
				$pat->setSex($row["sex"]);
				$pat->setEmail($row["email"]);
				$pat->setNationality((new Country())->get($row['nationality'], $pdo));
				$pat->setAddress($row["address"]);
				$pat->setDeceased((bool)$row['deceased']);
				if ($getFull) {
					$lga = (new LGADAO())->getLGA($row["lga_id"], false, $pdo);
					$resLga = (new LGADAO())->getLGA($row["lga_res_id"], false, $pdo);
					$state = (new StateDAO())->getState($row["state_id"], $pdo);
					$resState = (new StateDAO())->getState($row["state_res_id"], $pdo);
					$clinic = (new ClinicDAO())->getClinic($row["basehospital"], false, $pdo);
					$ses = (new SocioEconomicStatusDAO())->getSocioEconomicStatus($row["socio_economic"], $pdo);
					$ins = (new InsuranceDAO())->getInsurance($row["patient_ID"], true, $pdo);
					$rel = (new ReligionDAO())->getReligion($row['religion_id'], $pdo);
					$lang = (new SpokenLanguageDAO())->get($row['language_id'], $pdo);
				} else {
					$lga = new LGA($row['lga_id']);
					$resLga = new LGA($row['lga_res_id']);
					$state = new  State($row['state_id']);
					$resState = new State($row['state_res_id']);
					$clinic = new Clinic($row['basehospital']);
					$ses = new SocioEconomicStatus($row['socio_economic']);
					$lang = new SpokenLanguage($row['language_id']);
					//$ins = (new InsuranceDAO())->getInsurance($row["patient_ID"], FALSE, $pdo);
					$ins = null;
					$rel = new Religion();
					$rel->setId($row['religion_id']);
				}
				$pat->setLga($lga);
				$pat->setDistrict((new DistrictDAO())->get($row['district_id'], $pdo));
				$pat->setDistrictRes((new DistrictDAO())->get($row['district_res_id'], $pdo));
				$pat->setResLga($resLga);
				$pat->setState($state);
				$pat->setResState($resState);
				$pat->setSpokenLang($lang);
				$pat->setKinsFirstName($row["KinsFirstName"]);
				$pat->setKinsLastName($row["KinsLastName"]);
				$kinsPhone = (new ContactDAO())->forPatient($row['patient_ID'], 'kin', $pdo);
				$pat->setKinsPhone(count($kinsPhone) == 1 ? $kinsPhone[0] : null);
				$pat->setKinsAddress($row["KinsAddress"]);
				$pat->setRegisteredBy($row["registered_By"]);
				$pat->setPhoneNumber($row["phonenumber"]);
				$pat->setForeignNumber($row["foreign_number"]);
				$pat->setBloodGroup($row["bloodgroup"]);
				$pat->setBloodType($row["bloodtype"]);
				$pat->setBaseClinic($clinic); //obj
				$pat->setTransferedTo($row["transferedto"]);
				$pat->setEnrollmentDate($row["enrollment_date"]);
				$pat->setSocioEconomic($ses); //obj
				$pat->setLifeStyle($row["lifestyle"]);//todo: become an array object
				$pat->setPassportPath($this->getPatientImage($row['patient_ID'], $row['sex']));
				$pat->setEnablePortal($row['portal']);
				$pat->setScheme((new InsuranceSchemeDAO())->get($row['insurance_scheme'], false, $pdo));
				
				$pat->setInsurance($ins); //obj
				$pat->setReligion($rel);
				$pat->setOccupation($row['occupation']);
				$pat->setWorkAddress($row['work_address']);
				$pat->setIndustry((new IndustryDAO())->get($row['industry_id'], $pdo));
				$pat->setKinRelationship((new KinRelationDAO())->get($row['kin_relation_id'], $pdo));
				
				$pat->setReferralCompany((new ReferralCompanyDAO())->get($row['referral_company_id'], $pdo));
				$pat->setReferral((new ReferralDAO())->get($row['referral_id'], $pdo));
				
				$pat->setCareManager((new StaffDirectoryDAO())->getStaff($row['care_manager_id'], false, $pdo));
				$pat->setSchemeAtRegistration((new InsuranceSchemeDAO())->get($row['scheme_at_registration_id'], false, $pdo));
				$pat->setNumDaysOnAdmission($row['cum_annual_days_on_admission']);
				$pat->setContacts((new ContactDAO())->forPatient($row['patient_ID'], 'self', $pdo));
				
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
				$bills = new Bills();
				
				// if not a now-created patient
				if (!filter_var((new CreditLimitDAO())->getPatientLimit($row['patient_ID'], $pdo), FILTER_SANITIZE_NUMBER_INT)) {
					$_ = $bills->_getPatientPaymentsTotals($row['patient_ID'], null, $pdo) + $bills->_getPatientCreditTotals($row['patient_ID'], null, $pdo); //- (new CreditLimitDAO())->getPatientLimit($row['patient_ID'], $pdo)->getAmount();
					$pat->setOutstanding(number_format($_, 2, '.', ''));
				}
			} else {
				$pat = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$pat = null;
		}
		return $pat;
	}
	
	function getAllFemalePatientsMin($pdo = null, $all = null)
	{
		$pats = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_demograph WHERE sex='female' AND " . ($all === null ? "active=1" : (((bool)$all) ? "(active=1 OR active=0)" : "active=0"));
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pat = new PatientDemograph();
				$pat->setId($row["patient_ID"]);
				$pat->setTitle($row["title"]);
				$pat->setFname($row["fname"]);
				$pat->setLname($row["lname"]);
				$pat->setMname($row["mname"]);
				$pat->setEthnic($row['ethnic']);
				$pat->setDateOfBirth($row["date_of_birth"]);
				$pat->setSex($row["sex"]);
				$pat->setPhoneNumber($row["phonenumber"]);
				$pat->setForeignNumber($row["foreign_number"]);
				$pat->setBloodGroup($row['bloodgroup']);
				$pat->setNumDaysOnAdmission($row['cum_annual_days_on_admission']);
				
				$pats[] = $pat;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$pats = [];
		}
		return $pats;
	}
	
	function getPatientMin($pid, $pdo = null, $all = null)
	{//$all=NULL: means ONLY Active, $all=TRUE: means BOTH Active And InActive, $all=FALSE: InActive
		$pat = new PatientDemograph();
		if (is_blank($pid) || is_null($pid)) {
			return null;
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if ($all === null) {
				$filter = "pd.active=1";
			} else if ($all !== null) {
				$filter = "(pd.active=1 OR pd.active=0)";
			} else {
				$filter = "pd.active=0";
			}
			$sql = "SELECT pd.ethnic, pd.title, pd.foreign_number, pd.patient_ID, pd.legacy_patient_id, pd.fname, pd.lname, pd.mname, pd.email, pd.sex, pd.date_of_birth, pd.phonenumber, pd.kin_relation_id, pd.care_manager_id, ins.scheme_name, pd.cum_annual_days_on_admission FROM patient_demograph pd LEFT JOIN insurance i ON pd.patient_ID=i.patient_id LEFT JOIN insurance_schemes ins ON ins.id=i.insurance_scheme WHERE pd.patient_ID=$pid AND $filter";
			//            error_log("::: ".$sql." :::");
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pat->setId($row["patient_ID"]);
				$pat->setLegacyId($row["legacy_patient_id"]);
				$pat->setTitle($row["title"]);
				$pat->setFname($row["fname"]);
				$pat->setLname($row["lname"]);
				$pat->setMname($row["mname"]);
				$pat->setEmail($row["email"]);
				$pat->setEthnic($row['ethnic']);
				$pat->setSex($row["sex"]);
				$pat->setDateOfBirth($row["date_of_birth"]);
				$pat->setPhoneNumber($row["phonenumber"]);
				$pat->setForeignNumber($row["foreign_number"]);
				$pat->setPassportPath($this->getPatientImage($row['patient_ID'], $row['sex']));
				$pat->setInsurance((new InsuranceDAO())->getInsurance($row["patient_ID"], true, $pdo));
				$pat->setKinRelationship((new KinRelationDAO())->get($row['kin_relation_id'], $pdo));
				$pat->setCareManager((new StaffDirectoryDAO())->getStaff($row['care_manager_id'], false, $pdo));
				$pat->setScheme($row['scheme_name']);
				$pat->setNumDaysOnAdmission($row['cum_annual_days_on_admission']);
				
			} else {
				$pat = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$pat = null;
		}
		return $pat;
	}
	
	function getPatients($getFull = false, $pdo = null, $all = null)
	{ //$all=NULL: means ONLY Active, $all=TRUE: means BOTH Active And InActive, $all=FALSE: InActive
		$pats = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			switch ($all) {
				case true:
					$filter = "(active = 1 OR active = 0)";
					break;
				case false:
					$filter = "active = 0";
					break;
				case null:
					$filter = "active = 1";
					break;
				default:
					$filter = "active = 0";
			}
			//$sql = "SELECT * FROM patient_demograph WHERE (" . ($all === NULL ? "active=1" : (((bool)$all) ? "(active=1 OR active=0))" : "active=0")) . ")";
			$sql = "SELECT * FROM patient_demograph WHERE $filter";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pat = new PatientDemograph();
				$pat->setActive((bool)$row["active"]);
				$pat->setId($row["patient_ID"]);
				$pat->setLegacyId($row["legacy_patient_id"]);
				$pat->setTitle($row["title"]);
				$pat->setLoginId($row["login_id"]);
				$pat->setFname($row["fname"]);
				$pat->setLname($row["lname"]);
				$pat->setMname($row["mname"]);
				$pat->setDateOfBirth($row["date_of_birth"]);
				$pat->setDobEstimated((bool)$row['dob_estimated']);
				$pat->setSex($row["sex"]);
				$pat->setEmail($row["email"]);
				$pat->setNationality($row['nationality']);
				$pat->setAddress($row["address"]);
				$pat->setEthnic($row['ethnic']);
				if ($getFull) {
					$lga = (new LGADAO())->getLGA($row["lga_id"], false, $pdo);
					$resLga = (new LGADAO())->getLGA($row["lga_res_id"], false, $pdo);
					$state = (new StateDAO())->getState($row["state_id"], $pdo);
					$resState = (new StateDAO())->getState($row["state_res_id"], $pdo);
					$clinic = (new ClinicDAO())->getClinic($row["basehospital"], false, $pdo);
					$ses = (new SocioEconomicStatusDAO())->getSocioEconomicStatus($row["socio_economic"], $pdo);
					$ins = (new InsuranceDAO())->getInsurance($row["patient_ID"], true, $pdo);
					$rel = (new ReligionDAO())->getReligion($row['religion_id'], $pdo);
				} else {
					$lga = new LGA($row['lga_id']);
					$resLga = new LGA($row['lga_res_id']);
					$state = new State($row['state_id']);
					$resState = new State($row['state_res_id']);
					$clinic = new Clinic($row['basehospital']);
					$ses = new SocioEconomicStatus($row['socio_economic']);
					$ins = (new InsuranceDAO())->getInsurance($row["patient_ID"], false, $pdo);
					$rel = new Religion();
					$rel->setId($row['religion_id']);
				}
				$pat->setLga($lga);
				$pat->setResLga($resLga);
				$pat->setState($state);
				$pat->setResState($resState);
				
				$pat->setKinsFirstName($row["KinsFirstName"]);
				$pat->setKinsLastName($row["KinsLastName"]);
				$pat->setKinsPhone($row["KinsPhone"]);
				$pat->setKinsAddress($row["KinsAddress"]);
				$pat->setRegisteredBy($row["registered_By"]);
				$pat->setPhoneNumber($row["phonenumber"]);
				$pat->setForeignNumber($row["foreign_number"]);
				$pat->setBloodGroup($row["bloodgroup"]);
				$pat->setBloodType($row["bloodtype"]);
				$pat->setBaseClinic($clinic); //obj
				$pat->setTransferedTo($row["transferedto"]);
				$pat->setEnrollmentDate($row["enrollment_date"]);
				$pat->setSocioEconomic($ses);   //obj
				$pat->setLifeStyle($row["lifestyle"]);
				$pat->setPassportPath($this->getPatientImage($row['patient_ID'], $row['sex']));
				$pat->setInsurance($ins); //obj
				$pat->setReligion($rel);
				$pat->setOccupation($row['occupation']);
				$pat->setWorkAddress($row['work_address']);
				$pat->setKinRelationship((new KinRelationDAO())->get($row['kin_relation_id'], $pdo));
				$pat->setCareManager((new StaffDirectoryDAO())->getStaff($row['care_manager_id'], false, $pdo));
				$pat->setNumDaysOnAdmission($row['cum_annual_days_on_admission']);
				
				$pats[] = $pat;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$pats = [];
		}
		return $pats;
	}
	
	function getPatientSelectedParameter($sort = "lname", $ASC = null, $sqlPart = null, $getFull = false, $pdo = null)
	{ //$all=NULL: means ONLY Active, $all=TRUE: means BOTH Active And InActive, $all=FALSE: InActive
		$pats = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT " . ($sqlPart == null ? "*" : $sqlPart) . " FROM patient_demograph ORDER BY " . $sort . " " . ($ASC == null ? "ASC" : $ASC);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pat = new PatientDemograph();
				$pat->setActive((bool)$row["active"]);
				$pat->setId($row["patient_ID"]);
				$pat->setLegacyId($row["legacy_patient_id"]);
				$pat->setTitle($row["title"]);
				$pat->setLoginId($row["login_id"]);
				$pat->setFname($row["fname"]);
				$pat->setLname($row["lname"]);
				
				$pat->setMname($row["mname"]);
				$pat->setDateOfBirth($row["date_of_birth"]);
				$pat->setSex($row["sex"]);
				$pat->setEmail($row["email"]);
				$pat->setNationality($row['nationality']);
				$pat->setAddress($row["address"]);
				$pat->setEthnic($row['ethnic']);
				if ($getFull) {
					$lga = (new LGADAO())->getLGA($row["lga_id"], false, $pdo);
					$resLga = (new LGADAO())->getLGA($row["lga_res_id"], false, $pdo);
					$state = (new StateDAO())->getState($row["state_id"], $pdo);
					$resState = (new StateDAO())->getState($row["state_res_id"], $pdo);
					$clinic = (new ClinicDAO())->getClinic($row["basehospital"], false, $pdo);
					$ses = (new SocioEconomicStatusDAO())->getSocioEconomicStatus($row["socio_economic"], $pdo);
					$ins = (new InsuranceDAO())->getInsurance($row["patient_ID"], true, $pdo);
				} else {
					$lga = new LGA($row['lga_id']);
					$resLga = new LGA($row['lga_res_id']);
					$state = new State($row['state_id']);
					$resState = new State($row['state_res_id']);
					$clinic = new Clinic($row['basehospital']);
					$ses = new SocioEconomicStatus($row['socio_economic']);
					$ins = (new InsuranceDAO())->getInsurance($row["patient_ID"], false, $pdo);
				}
				$pat->setLga($lga);
				$pat->setResLga($resLga);
				$pat->setState($state);
				$pat->setResState($resState);
				
				$pat->setOccupation($row['occupation']);
				$pat->setWorkAddress($row['work_address']);
				$pat->setKinsFirstName($row["KinsFirstName"]);
				$pat->setKinsLastName($row["KinsLastName"]);
				$pat->setKinsPhone($row["KinsPhone"]);
				$pat->setKinsAddress($row["KinsAddress"]);
				$pat->setRegisteredBy($row["registered_By"]);
				$pat->setPhoneNumber($row["phonenumber"]);
				$pat->setForeignNumber($row["foreign_number"]);
				$pat->setBloodGroup($row["bloodgroup"]);
				$pat->setBloodType($row["bloodtype"]);
				$pat->setBaseClinic($clinic); //obj
				$pat->setTransferedTo($row["transferedto"]);
				$pat->setEnrollmentDate($row["enrollment_date"]);
				$pat->setSocioEconomic($ses); //obj
				$pat->setLifeStyle($row["lifestyle"]);
				$pat->setPassportPath($this->getPatientImage($row['patient_ID'], $row['sex']));
				$pat->setInsurance($ins); //obj
				$pat->setKinRelationship((new KinRelationDAO())->get($row['kin_relation_id'], $pdo));
				$pat->setCareManager((new StaffDirectoryDAO())->getStaff($row['care_manager_id'], false, $pdo));
				
				$pats[] = $pat;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pats = array();
			errorLog($e);
		}
		return $pats;
	}
	
	function getPatientFlatLists($page, $pageSize,$sort = "lname", $ASC = null, $pdo = null)
	{ //$all=NULL: means ONLY Active, $all=TRUE: means BOTH Active And InActive, $all=FALSE: InActive
		$pats = array();
		$total = 0;
		$sql = "SELECT * FROM patient_demograph ORDER BY " . $sort . " " . ($ASC == null ? "ASC" : $ASC);
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
			
		}
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pat = new PatientDemograph();
				$pat->setActive((bool)$row["active"]);
				$pat->setId($row["patient_ID"]);
				$pat->setLegacyId($row["legacy_patient_id"]);
				$pat->setTitle($row["title"]);
				$pat->setLoginId($row["login_id"]);
				$pat->setFname($row["fname"]);
				$pat->setLname($row["lname"]);
				
				$pat->setMname($row["mname"]);
				$pat->setDateOfBirth($row["date_of_birth"]);
				$pat->setSex($row["sex"]);
				$pat->setEmail($row["email"]);
				$pat->setNationality($row['nationality']);
				$pat->setAddress($row["address"]);
				$pat->setEthnic($row['ethnic']);
				
				$pat->setOccupation($row['occupation']);
				$pat->setWorkAddress($row['work_address']);
				$pat->setKinsFirstName($row["KinsFirstName"]);
				$pat->setKinsLastName($row["KinsLastName"]);
				$pat->setKinsPhone($row["KinsPhone"]);
				$pat->setKinsAddress($row["KinsAddress"]);
				$pat->setRegisteredBy($row["registered_By"]);
				$pat->setPhoneNumber($row["phonenumber"]);
				$pat->setForeignNumber($row["foreign_number"]);
				$pat->setBloodGroup($row["bloodgroup"]);
				$pat->setBloodType($row["bloodtype"]);
				$pat->setTransferedTo($row["transferedto"]);
				$pat->setEnrollmentDate($row["enrollment_date"]);
				$pat->setLifeStyle($row["lifestyle"]);
				$pat->setPassportPath($this->getPatientImage($row['patient_ID'], $row['sex']));
				$pat->setKinRelationship((new KinRelationDAO())->get($row['kin_relation_id'], $pdo));
				$pat->setCareManager((new StaffDirectoryDAO())->getStaff($row['care_manager_id'], false, $pdo));
				$pats[] = $pat;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pats = array();
			errorLog($e);
		}
		$results = (object)null;
		$results->data = $pats;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function hasEmail($pid, $pdo = null)
	{
		$status = false;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_demograph WHERE patient_ID=" . $pid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
			if ($stmt->rowCount() > 0) {
				if (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
					$status = true;
				}
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$status = false;
			$stmt = null;
		}
		return $status;
	}
	
	function updatePatient($pat, $scheme_id)
	{
		//$pat = new PatientDemograph();
		$title = ($pat->getTitle()) ? "'" . escape($pat->getTitle()) . "'" : "NULL";
		$foreignNumber = !is_blank($pat->getForeignNumber()) ? quote_esc_str($pat->getForeignNumber()) : "NULL";
		try {
			$pdo = $this->conn->getPDO();
			$pdo->beginTransaction();
			$user = $_SESSION['staffID'];
			$kinRelationId = ($pat->getKinRelationship() != null) ? $pat->getKinRelationship()->getId() : "NULL";
			$care_manager_id = ($pat->getCareManager() != null) ? $pat->getCareManager()->getId() : "NULL";
			$referral_id = ($pat->getReferral() !== null) ? $pat->getReferral()->getId() : "NULL";
			$referral_company_id = $pat->getReferralCompany() ? $pat->getReferralCompany()->getId() : "null";
			$dobEstimated = var_export($pat->getDobEstimated(), true);
			$socioEconomicStatus = $pat->getSocioEconomic() ? $pat->getSocioEconomic()->getId() : 'NULL';
			$districtId = $pat->getDistrict() ? $pat->getDistrict()->getId() : 'NULL';
			$districtResId = $pat->getDistrictRes() ? $pat->getDistrictRes()->getId() : 'NULL';
			$industryId = $pat->getIndustry() ? $pat->getIndustry()->getId() : 'NULL';
			$lifeStyle = $pat->getLifeStyle() ? quote_esc_str($pat->getLifeStyle()->getIds()) : "NULL";
			$lang_id = $pat->getSpokenLang() ? $pat->getSpokenLang()->getId() : "NULL";
			$ethnic = $pat->getEthnic() ? $pat->getEthnic() : "NULL";
			
			$sql = "UPDATE patient_demograph SET title=$title, legacy_patient_id='" . $pat->getLegacyId() . "', fname='" . ucfirst($pat->getFname()) . "', lname='" . ucfirst($pat->getLname()) . "', mname='" . ucfirst($pat->getMname()) . "', date_of_birth='" . $pat->getDateOfBirth() . "', dob_estimated=$dobEstimated, sex='" . $pat->getSex() . "', email='" . $pat->getEmail() . "', nationality=" . $pat->getNationality()->id . ", address='" . escape(ucwords($pat->getAddress())) . "', lga_id=" . $pat->getLga()->getId() . ", district_id=$districtId, district_res_id=$districtResId, state_id=" . $pat->getState()->getId() . ", state_res_id=" . $pat->getResState()->getId() . ", lga_res_id=" . $pat->getResLga()->getId() . ", KinsFirstName='" . ucfirst($pat->getKinsFirstName()) . "', KinsLastName='" . ucfirst($pat->getKinsLastName()) . "', KinsPhone='" . $pat->getKinsPhone() . "', KinsAddress='" . escape(ucwords($pat->getKinsAddress())) . "', kin_relation_id=$kinRelationId, registered_By='" . $pat->getRegisteredBy()->getId() . "', phonenumber='" . $pat->getPhoneNumber() . "', foreign_number=$foreignNumber,  bloodgroup='" . strtoupper($pat->getBloodGroup()) . "', bloodtype='" . strtoupper($pat->getBloodType()) . "', " . "basehospital='" . $pat->getBaseClinic()->getId() . "', socio_economic=" . $socioEconomicStatus . ", lifestyle=$lifeStyle, religion_id=" . ($pat->getReligion() != null && !empty($pat->getReligion()) ? $pat->getReligion()->getId() : "NULL") . ", occupation='" . $pat->getOccupation() . "', work_address='" . escape($pat->getWorkAddress()) . "', industry_id=$industryId, care_manager_id=$care_manager_id, referral_id=$referral_id, referral_company_id=$referral_company_id, last_modified_date=NOW(), last_modified_by=$user, language_id=$lang_id, ethnic='" . $ethnic . "' WHERE patient_ID=" . $pat->getId();
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$insStatus = (new InsuranceDAO())->updateInsurance($pat->getInsurance(), $pdo);
			if (!$insStatus){
				$pdo->rollBack();
				return null;
			}
			if ($scheme_id != $pat->getScheme()->getId()) {
				$ins = $pat->getInsurance();
				//charge this patient for registration
				//but on-behalf of this scheme
				$bil = new Bill();
				$bil->setPatient($pat); //set this to null and then
				//make sure the $ins object contains the `getType` attribute
				$bil->setDescription("Beneficiary enrollment charge - [change]");
				
				$bil->setItem(new Registration());
				$bil->setSource((new BillSourceDAO())->findSourceById(10, $pdo));
				$bil->setTransactionType("credit");
				
				$scheme = (new InsuranceSchemeDAO())->get($ins->getScheme()->getId(), true, $pdo);
				$amount = $scheme->getIndividualRegCost();
				$bil->setAmount($amount);
				$bil->setDiscounted(null);
				$bil->setDiscountedBy(null);
				$bil->setReferral($pat->getReferral());
				
				$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
				$bil->setClinic($staff->getClinic());
				$bil->setBilledTo($scheme);
				
				$bill = (new BillDAO())->addBill($bil, 1, $pdo, null);
			}
			(new ContactDAO())->clearPatient($pat->getId(), $pdo);
			//if((new ContactDAO())->clearPatient($pat->getId(), $pdo)){
			foreach ($pat->getContacts() as $contact) {
				$contact->setPatient($pat)->add($pdo);
			}
			//}
			
			if ($pat->getKinsPhone()) {
				//if((new ContactDAO())->clearPatient($pat->getId(), $pdo)) {
				$pat->getKinsPhone()->setPatient($pat)->add($pdo);
				//}
			}
			
			$pdo->commit();
			$stmt = null;
		} catch
			(PDOException $e) {
				error_log($e->getMessage());
				$stmt = null;
				$pat = null;
			}
		return $pat;
	}
	
	function updateEmail($pid, $email)
	{
		$status = false;
		try {
			$pdo = $this->conn->getPDO();
			$sql = "UPDATE patient_demograph SET email='" . $email . "' WHERE patient_ID=" . $pid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$status = $stmt->execute();
			$stmt = null;
		} catch (PDOException $e) {
			$status = false;
			$stmt = null;
		}
		return $status;
	}
	
	function changeState($pid, $state)
	{
		try {
			$pdo = $this->conn->getPDO();
			$oBill = (new BillDAO())->getPatientOutstandingSum($pid, $pdo);
			if ($oBill == null) {
				error_log("oBill is NULL");
				throw new MedicPlusException("Oops! something went wrong!");
			}
			if ($oBill > 0) {
				throw new MedicPlusException("Sorry, this patient still owes N" . number_format($oBill));
			}
			if (!(new PatientQueueDAO())->cancelQueue(null, $pid, $pdo)) {
				error_log("cancelQueue fails");
				throw new MedicPlusException("Oops! something went wrong!!");
			}
			if (!(new AppointmentDAO())->cancelAppointment(null, $pid, $pdo)) {
				error_log("Cancel appointment fails");
				throw new MedicPlusException("Oops! something went wrong!!!");
			}
			//TODO: cancel all 'Active
			
			$pdo->beginTransaction();
			$sql = "UPDATE patient_demograph SET active=$state  WHERE patient_ID=$pid";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$sql2 = "UPDATE clinical_task SET `status` = 'Cancelled' WHERE patient_id = $pid AND `status` = 'Active'";
			$stmt2 = $pdo->prepare($sql2, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt2->execute();
			if ($stmt->rowCount() == 1 AND $stmt2->rowCount() >= 0) {
				$status = true;
				$stmt = null;
				$pdo->commit();
			} else {
				$status = false;
				error_log("What is the error again, trying to change the patient account state?");
				$pdo->rollBack();
			}
			
		} catch (PDOException $e) {
			$status = false;
			$stmt = null;
			errorLog($e);
		}
		return $status;
	}
	
	function deceasePatient($pid)
	{
		try {
			$pdo = $this->conn->getPDO();
			$oBill = (new BillDAO())->getPatientOutstandingSum($pid, $pdo);
			if ($oBill == null) {
				error_log("oBill is NULL");
				throw new MedicPlusException("Oops! something went wrong!");
			}
			if ($oBill > 0) {
				throw new MedicPlusException("Sorry, this patient still owes N" . number_format($oBill));
			}
			if (!(new PatientQueueDAO())->cancelQueue(null, $pid, $pdo)) {
				error_log("cancelQueue fails");
				throw new MedicPlusException("Oops! something went wrong!!");
			}
			if (!(new AppointmentDAO())->cancelAppointment(null, $pid, $pdo)) {
				error_log("Cancel appointment fails");
				throw new MedicPlusException("Oops! something went wrong!!!");
			}
			//TODO: cancel all 'Active
			
			$pdo->beginTransaction();
			$sql = "UPDATE patient_demograph SET deceased=TRUE WHERE patient_ID=$pid";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$sql2 = "UPDATE clinical_task SET `status` = 'Cancelled' WHERE patient_id = $pid AND `status` = 'Active'";
			$stmt2 = $pdo->prepare($sql2, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt2->execute();
			if ($stmt->rowCount() == 1 AND $stmt2->rowCount() >= 0) {
				$status = true;
				$stmt = null;
				$pdo->commit();
			} else {
				$status = false;
				error_log("What is the error again, trying to change the patient account state?");
				$pdo->rollBack();
			}
			
		} catch (PDOException $e) {
			$status = false;
			$stmt = null;
			errorLog($e);
		}
		return $status;
	}
	
	function getPatientImage($pid, $sex)
	{
		if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/img/profiles/" . $pid . "_profile.jpg")) {
			$img = "/img/profiles/" . $pid . "_profile.jpg";
		} else if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/img/profiles/" . (int)$pid . "_profile.jpg")) {
			$img = "/img/profiles/" . (int)$pid . "_profile.jpg";
		} else {
			$img = "/img/profiles/" . $sex . ".jpg";
		}
		return $img;
	}
	
	private function generatePatientID()
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT `AUTO_INCREMENT` AS val FROM  INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $pdo->getDBName() . "' AND TABLE_NAME = 'patient_demograph'";
		//error_log($sql);
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		
		$data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		
		$value = $data['val'];
		$genID = sprintf('%011d', $value);
		//error_log($value);
		//$genID = str_pad($value, 11, '0', STR_PAD_LEFT);
		return $genID;
	}
	
	function _genFolioNumber($prefix = null, $masked = false, $pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$sql = "SELECT `AUTO_INCREMENT` AS val FROM  INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $pdo->getDBName() . "' AND TABLE_NAME = 'patient_demograph'";
		
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		
		$data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		
		$value = $data['val'];
		$str = date($prefix) . sprintf('%06d', $value);
		if ($masked) {
			return preg_replace('/[A-Z0-9]/', '*', $str);
		} else {
			return $str;
		}
	}
	
	function querySQL($DSQL, $pdo = null)
	{
		$pats = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($DSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pat = new PatientDemograph();
				$pat->setId($row["patient_ID"]);
				$pat->setFname($row["fname"]);
				$pat->setLname($row["lname"]);
				$pat->setMname($row["mname"]);
				
				$pats[] = $pat;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$pats = array();
		}
		return $pats;
	}
	
	public function mergePatients($getPatient, $getPatient1, $pdo = null)
	{
		$old = $getPatient->getId();
		$new = $getPatient1->getId();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			
			$pdo->beginTransaction();
			
			$sql[] = "UPDATE alert SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE antenatal_assessment SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE antenatal_notes SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE appointment_group SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE clinical_task SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE bills SET patient_id = $new WHERE patient_id = $old";
			// $sql[] = "UPDATE credit_limit SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE dispensed_drugs SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE enrollments_antenatal SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE enrollments_immunization SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE enrollments_labour SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE encounter SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE invoice SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE in_patient SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE lab_requests SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE message_dispatch SET pid = $new WHERE pid = $old";
			$sql[] = "UPDATE message_queue_temp SET patient = $new WHERE patient = $old";
			$sql[] = "UPDATE message_subscription SET patient = $new WHERE patient = $old";
			$sql[] = "UPDATE patient_allergen SET patient_ID = $new WHERE patient_ID = $old";
			$sql[] = "UPDATE patient_antenatal SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE patient_antenatal_items SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE patient_attachment SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE patient_diagnoses SET patient_ID = $new WHERE patient_ID = $old";
			$sql[] = "UPDATE patient_in_room SET patientID = $new WHERE patientID = $old";
			$sql[] = "UPDATE patient_labs SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE patient_physical_assessments SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE patient_pre_conditions SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE patient_procedure SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE patient_queue SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE patient_regimens SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE patient_scan SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE patient_systems_review SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE patient_vaccine SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE patient_vaccine_booster SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE patient_visit_notes SET patient_ID = $new WHERE patient_ID = $old";
			$sql[] = "UPDATE special_event SET patient_id = $new WHERE patient_id = $old";
			$sql[] = "UPDATE vital_sign SET patient_id = $new WHERE patient_id = $old";
			
			$sql[] = "UPDATE patient_demograph SET active = FALSE WHERE patient_ID = $old";
			
			//$sql[] = "DELETE FROM insurance WHERE patient_id = $old";
			//$sql[] = "UPDATE insurance SET patient_id = $new WHERE patient_id = $old"; // exception
			
			foreach ($sql as $i => $item) {
				try {
					$stmt = $pdo->prepare($item, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					$stmt->execute();
				} catch (PDOException $e) {
					$pdo->rollBack();
					error_log("ERROR: Failed migration at $i for patient[$old->$new]");
					return "error:Transaction failed mid-way ($item)";
				}
			}
			$pdo->commit();
			return "success:Data migrated successfully";
		} catch (PDOException $e) {
			error_log("ERROR: " . $e->getMessage());
			return "error:Migration Error, Please try again";
		}
	}
	
}
