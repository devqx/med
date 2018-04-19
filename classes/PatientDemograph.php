<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PatientDemograph
 *
 * @author pauldic
 */
class PatientDemograph implements JsonSerializable
{
	public static $bloodGroups
		= array('UNKNOWN', 'O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+');
	public static $bloodTypes = [];//array('UNKNOWN', 'AA', 'AS', 'SS', 'SC','AC','CC','Thalassemia','Others');
	private $active;
	private $patientId;
	private $legacyPatientId;
	private $loginId;
	private $title;
	private $fname;
	private $lname;
	private $mname;
	private $dateOfBirth;
	private $dobEstimated;
	private $sex;
	private $email;
	private $religion;
	private $address;
	private $country;
	private $industry;
	private $lga;
	private $state;
	private $district;
	private $lgaRes;
	private $stateRes;
	private $districtRes;
	private $kinsFirstName;
	private $kinsLastName;
	private $kinsPhone;
	private $kinsAddress;
	private $kinRelationship;
	private $registeredBy;
	private $phoneNumber;//todo deprecated this
	private $contacts;
	private $foreignNumber;
	private $bloodGroup;
	private $bloodType;
	private $baseClinic;
	private $transferedTo;
	private $enrollmentDate;
	private $socioEconomic;
	private $lifeStyle;
	private $search;
	private $passportPath = "img/profiles/";
	private $insurance;
	private $schemeAtRegistration;
	private $scheme;
	
	private $fullname;
	private $shortName;
	private $age;
	private $nationality;
	private $occupation;
	private $careManager;
	private $deceased;
	
	private $work_address;
	private $referral;
	private $referralCompany;
	
	private $outstanding;
	private $numDaysOnAdmission;
	private $isAdmitted = false;
	private $spokenLang;
	private $ethnic;

	private $enablePortal;
	
	private $vitalSigns
		= ["weight" => null, "temp" => null, "sArea" => null, "rp" => null, "pulse" => null, "muac" => null, "height" => null, "hCircum" => null, "bp" => null];
	
	function __construct($id = null)
	{
		$this->patientId = $id;
	}
	
	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->title;
	}
	
	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}
	
	public function getDeceased()
	{
		return $this->deceased;
	}
	
	public function setDeceased($deceased)
	{
		$this->deceased = $deceased;
		return $this;
	}
	
	public function isActive()
	{
		return $this->active;
	}
	
	public function getId()
	{
		return $this->patientId;
	}
	
	public function getLegacyId()
	{
		return $this->legacyPatientId;
	}
	
	public function getLoginId()
	{
		return $this->loginId;
	}
	
	public function setLoginId($loginId)
	{
		$this->loginId = $loginId;
		return $this;
	}
	
	public function getSex()
	{
		return $this->sex;
	}
	
	public function setSex($sex)
	{
		$this->sex = $sex;
		return $this;
	}
	
	public function getEmail()
	{
		return $this->email;
	}
	
	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}
	
	public function getReligion()
	{
		return $this->religion;
	}
	
	public function setReligion($religion)
	{
		$this->religion = $religion;
		return $this;
	}
	
	public function getNationality()
	{
		return $this->nationality;
	}
	
	public function setNationality($nationality)
	{
		$this->nationality = $nationality;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getIndustry()
	{
		return $this->industry;
	}
	
	/**
	 * @param mixed $industry
	 *
	 * @return PatientDemograph
	 */
	public function setIndustry($industry)
	{
		$this->industry = $industry;
		return $this;
	}
	
	public function getAddress()
	{
		return $this->address;
	}
	
	public function setAddress($address)
	{
		$this->address = $address;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCountry()
	{
		return $this->country;
	}
	
	/**
	 * @param mixed $country
	 *
	 * @return PatientDemograph
	 */
	public function setCountry($country)
	{
		$this->country = $country;
		return $this;
	}
	
	public function getLga()
	{
		return $this->lga;
	}
	
	public function setLga($lga)
	{
		$this->lga = $lga;
	}
	
	/**
	 * @return mixed
	 */
	public function getDistrict()
	{
		return $this->district;
	}
	
	/**
	 * @param mixed $district
	 *
	 * @return PatientDemograph
	 */
	public function setDistrict($district)
	{
		$this->district = $district;
		return $this;
	}
	
	public function getState()
	{
		return $this->state;
	}
	
	public function setState($state)
	{
		$this->state = $state;
	}
	
	public function getResLga()
	{
		return $this->lgaRes;
	}
	
	public function getResState()
	{
		return $this->stateRes;
	}
	
	/**
	 * @return mixed
	 */
	public function getDistrictRes()
	{
		return $this->districtRes;
	}
	
	/**
	 * @param mixed $districtRes
	 *
	 * @return PatientDemograph
	 */
	public function setDistrictRes($districtRes)
	{
		$this->districtRes = $districtRes;
		return $this;
	}
	
	
	/**
	 * @return mixed
	 */
	public function getOccupation()
	{
		return $this->occupation;
	}
	
	/**
	 * @param mixed $occupation
	 */
	public function setOccupation($occupation)
	{
		$this->occupation = $occupation;
	}
	
	/**
	 * @return mixed
	 */
	public function getWorkAddress()
	{
		return $this->work_address;
	}
	
	/**
	 * @param mixed $work_address
	 */
	public function setWorkAddress($work_address)
	{
		$this->work_address = $work_address;
	}
	
	public function getKinsFirstName()
	{
		return $this->kinsFirstName;
	}
	
	public function setKinsFirstName($kinsFirstName)
	{
		$this->kinsFirstName = $kinsFirstName;
	}
	
	public function getKinsLastName()
	{
		return $this->kinsLastName;
	}
	
	public function setKinsLastName($kinsLastName)
	{
		$this->kinsLastName = $kinsLastName;
	}
	
	public function getKinsPhone()
	{
		return $this->kinsPhone;
	}
	
	public function setKinsPhone($kinsPhone)
	{
		$this->kinsPhone = $kinsPhone;
	}
	
	public function getKinsAddress()
	{
		return $this->kinsAddress;
	}
	
	public function setKinsAddress($kinsAddress)
	{
		$this->kinsAddress = $kinsAddress;
	}
	
	/**
	 * @return mixed
	 */
	public function getKinRelationship()
	{
		return $this->kinRelationship;
	}
	
	/**
	 * @param mixed $kinRelationship
	 */
	public function setKinRelationship($kinRelationship)
	{
		$this->kinRelationship = $kinRelationship;
	}
	
	
	public function getRegisteredBy()
	{
		return $this->registeredBy;
	}
	
	public function setRegisteredBy($registeredBy)
	{
		$this->registeredBy = $registeredBy;
	}
	
	public function getPhoneNumber()
	{
		return $this->phoneNumber;
	}
	
	
	public function setPhoneNumber($phonenumber)
	{
		$this->phoneNumber = $phonenumber;
	}
	
	public function getForeignNumber()
	{
		return $this->foreignNumber;
	}
	
	public function setForeignNumber($foreignNumber)
	{
		$this->foreignNumber = $foreignNumber;
	}
	
	public function getBloodGroup()
	{
		return $this->bloodGroup;
	}
	
	public function setBloodGroup($bloodgroup)
	{
		$this->bloodGroup = $bloodgroup;
	}
	
	public function getBloodType()
	{
		return $this->bloodType;
	}
	
	public function setBloodType($bloodtype)
	{
		$this->bloodType = $bloodtype;
	}
	
	public function getBaseClinic()
	{
		return $this->baseClinic;
	}
	
	public function setBaseClinic($basehospital)
	{
		$this->baseClinic = $basehospital;
	}
	
	public function getTransferedTo()
	{
		return $this->transferedTo;
	}
	
	public function setTransferedTo($transferedto)
	{
		$this->transferedTo = $transferedto;
	}
	
	public function getEnrollmentDate()
	{
		return $this->enrollmentDate;
	}
	
	public function setEnrollmentDate($enrollmentDate)
	{
		$this->enrollmentDate = $enrollmentDate;
	}
	
	public function getSocioEconomic()
	{
		return $this->socioEconomic;
	}
	
	public function setSocioEconomic($socioEconomic)
	{
		$this->socioEconomic = $socioEconomic;
	}
	
	public function getLifeStyle()
	{
		return $this->lifeStyle;
	}
	
	public function setLifeStyle($lifestyle)
	{
		$this->lifeStyle = $lifestyle;
	}
	
	public function getSearch()
	{
		return $this->search;
	}
	
	public function setSearch($search)
	{
		$this->search = $search;
	}
	
	public function getShortName()
	{
		$names = array();
		if (!empty($this->getLname())) {
			$names[] = $this->getLname() . ",";
		}
		if (!empty($this->getFname())) {
			$names[] = $this->getFname()[0] . ".";
		}
		if (!empty($this->getMname())) {
			$names[] = $this->getMname()[0] . ".";
		}
		return implode(" ", $names);
	}
	
	public function getLname()
	{
		return $this->lname;
	}
	
	public function setLname($lname)
	{
		$this->lname = $lname;
	}
	
	public function getFname()
	{
		return $this->fname;
	}
	
	public function setFname($fname)
	{
		$this->fname = $fname;
	}
	
	public function getMname()
	{
		return $this->mname;
	}
	
	public function setMname($mname)
	{
		$this->mname = $mname;
	}
	
	public function getPassportPath()
	{
		return $this->passportPath;
	}
	
	public function setPassportPath($passportPath)
	{
		$this->passportPath = $passportPath;
	}
	
	public function setActive($active)
	{
		$this->active = $active;
	}
	
	public function setId($patientId)
	{
		$this->patientId = $patientId;
	}
	
	public function setLegacyId($legacyPatientId)
	{
		$this->legacyPatientId = $legacyPatientId;
	}
	
	public function setResLga($lgaRes)
	{
		$this->lgaRes = $lgaRes;
	}
	
	public function setResState($stateRes)
	{
		$this->stateRes = $stateRes;
	}
	
	public function getInsurance()
	{
		return $this->insurance;
	}
	
	public function setInsurance($insurance)
	{
		$this->insurance = $insurance;
	}

    /**
     * @return mixed
     */
    public function getSpokenLang()
    {
        return $this->spokenLang;
    }

    /**
     * @param mixed $spokenLang
     * @return PatientDemograph
     */
    public function setSpokenLang($spokenLang)
    {
        $this->spokenLang = $spokenLang;
        return $this;
    }


	
	/**
	 * @return bool
	 */
	public function isAdmitted(): bool
	{
		return $this->isAdmitted;
	}
	
	/**
	 * @param bool $isAdmitted
	 *
	 * @return PatientDemograph
	 */
	public function setIsAdmitted(bool $isAdmitted): PatientDemograph
	{
		$this->isAdmitted = $isAdmitted;
		return $this;
	}
	
	
	public function getVitalSigns()
	{
		return $this->vitalSigns;
	}
	
	/*public function setFullname($fullname) {
			$this->fullname = $fullname ." ".$this->fname." ".$this->mname;
	}*/
	
	public function setVitalSigns($vitalSigns)
	{
		$this->vitalSigns = $vitalSigns;
	}
	
	/**
	 * @return mixed
	 */
	public function getReferral()
	{
		return $this->referral;
	}
	
	/**
	 * @param mixed $referral
	 */
	public function setReferral($referral)
	{
		$this->referral = $referral;
	}
	
	/**
	 * @return mixed
	 */
	public function getReferralCompany()
	{
		return $this->referralCompany;
	}
	
	/**
	 * @param mixed $referralCompany
	 *
	 * @return PatientDemograph
	 */
	public function setReferralCompany($referralCompany)
	{
		$this->referralCompany = $referralCompany;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDobEstimated()
	{
		return $this->dobEstimated;
	}
	
	/**
	 * @param mixed $dobEstimated
	 *
	 * @return PatientDemograph
	 */
	public function setDobEstimated($dobEstimated)
	{
		$this->dobEstimated = $dobEstimated;
		return $this;
	}
	
	public function __toString()
	{
		return strval($this->fullname);
	}
	
	public function jsonSerialize()
	{
		$this->fullname = $this->getFullname();
		
		$this->age = $this->getAge();
		return (object)get_object_vars($this);
	}
	
	/**
	 * @return mixed
	 */
	public function getContacts()
	{
		return $this->contacts;
	}
	
	/**
	 * @param mixed $contacts
	 *
	 * @return PatientDemograph
	 */
	public function setContacts($contacts)
	{
		$this->contacts = $contacts;
		return $this;
	}
	
	public function getFullname()
	{
		$names = array();
		if (!empty($this->getTitle())) {
			//$names[] =  ($this->getTitle()) . " ";
			$names[] = str_replace("|", ",", $this->getTitle()) . " ";
		}
		if (!empty($this->getFname())) {
			$names[] = $this->getFname();
		}
		if (!empty($this->getMname())) {
			$names[] = $this->getMname() . ",";
		}
		if (!empty($this->getLname())) {
			$names[] = $this->getLname();
		}
		
		return implode(" ", $names);
	}
	
	public function getAge()
	{
		if ($this->getDateOfBirth() == null) {
			return null;
		}
		$date1 = new DateTime($this->getDateOfBirth());
		$date2 = new DateTime(date("Y-m-d"));
		
		$interval = $date1->diff($date2);
		$years = $interval->y;
		$months = $interval->m;
		
		if ($years < 1) {
			return $months . " months";
		}
		return $years . " years";
	}
	
	public function getDateOfBirth()
	{
		return $this->dateOfBirth;
	}
	
	public function setDateOfBirth($dateOfBirth)
	{
		$this->dateOfBirth = $dateOfBirth;
	}
	
	/**
	 * @return mixed
	 */
	public function getCareManager()
	{
		return $this->careManager;
	}
	
	/**
	 * @param mixed $careManager
	 */
	public function setCareManager($careManager)
	{
		$this->careManager = $careManager;
	}
	
	/**
	 * @return mixed
	 */
	public function getOutstanding()
	{
		return $this->outstanding;
	}
	
	/**
	 * @param mixed $outstanding
	 */
	public function setOutstanding($outstanding)
	{
		$this->outstanding = $outstanding;
	}
	
	/**
	 * @return mixed
	 */
	public function getSchemeAtRegistration()
	{
		return $this->schemeAtRegistration;
	}
	
	/**
	 * @param mixed $schemeAtRegistration
	 *
	 * @return PatientDemograph
	 */
	public function setSchemeAtRegistration($schemeAtRegistration)
	{
		$this->schemeAtRegistration = $schemeAtRegistration;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getScheme()
	{
		return $this->scheme;
	}
	
	/**
	 * @param mixed $scheme
	 *
	 * @return PatientDemograph
	 */
	public function setScheme($scheme)
	{
		$this->scheme = $scheme;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getNumDaysOnAdmission()
	{
		return $this->numDaysOnAdmission;
	}
	
	/**
	 * @param mixed $numDaysOnAdmission
	 *
	 * @return PatientDemograph
	 */
	public function setNumDaysOnAdmission($numDaysOnAdmission)
	{
		$this->numDaysOnAdmission = $numDaysOnAdmission;
		return $this;
	}

    /**
     * @return mixed
     */
    public function getEthnic()
    {
        return $this->ethnic;
    }

    /**
     * @param mixed $ethnic
     * @return PatientDemograph
     */
    public function setEthnic($ethnic)
    {
        $this->ethnic = $ethnic;
        return $this;
    }



    /**
     * @return mixed
     */
    public function getEnablePortal()
    {
        return $this->enablePortal;
    }

    /**
     * @param mixed $enablePortal
     * @return PatientDemograph
     */
    public function setEnablePortal($enablePortal)
    {
        $this->enablePortal = $enablePortal;
        return $this;
    }



	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$deceased = var_export($this->getDeceased(), true);
		//$active = var_export($this->isActive(), true);
		//$legacyId = !is_blank($this->getLegacyId()) ? quote_esc_str($this->getLegacyId()) : 'NULL';
		//$title = []; //todo, it's an array
		
		try {
			$sql = "UPDATE patient_demograph SET deceased = $deceased WHERE patient_ID={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return $this;
			}
			return null;
		} catch (PDOException $exception) {
			errorLog($exception);
			return null;
		}
	}
}

