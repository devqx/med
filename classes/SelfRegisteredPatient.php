<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/1/16
 * Time: 2:48 PM
 */
class SelfRegisteredPatient implements JsonSerializable
{

	private $id;
	private $title;
	private $fname;
	private $lname;
	private $sex;
	private $email;
	private $contact;
	private $date_of_birth;
	private $country;
	private $state;
	private $district;
	private $occupation;
	private $industry;
	private $work_address;
	private $res_state;
	private $res_lga;
	private $res_district;
	private $lga;
	private $res_address;
	private $blood_group;
	private $genotype;
	private $relationship;
	private $religion;
	private $next_kin_fname;
	private $next_kin_lname;
	private $next_kin_phone;
	private $next_kin_address;
	private $spokenLang;

	/**
	 * SelfRegisteredPatient constructor.
	 * @param $id
	 */
	public function __construct($id=null)
	{
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 * @return SelfRegisteredPatient
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param mixed $title
	 * @return SelfRegisteredPatient
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getFname()
	{
		return $this->fname;
	}

	/**
	 * @param mixed $fname
	 * @return SelfRegisteredPatient
	 */
	public function setFname($fname)
	{
		$this->fname = $fname;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSex()
	{
		return $this->sex;
	}

	/**
	 * @param mixed $sex
	 * @return SelfRegisteredPatient
	 */
	public function setSex($sex)
	{
		$this->sex = $sex;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLname()
	{
		return $this->lname;
	}

	/**
	 * @param mixed $lname
	 * @return SelfRegisteredPatient
	 */
	public function setLname($lname)
	{
		$this->lname = $lname;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @return mixed
	 */
	public function getResLga()
	{
		return $this->res_lga;
	}

	/**
	 * @param mixed $res_lga
	 * @return SelfRegisteredPatient
	 */
	public function setResLga($res_lga)
	{
		$this->res_lga = $res_lga;
		return $this;
	}

	/**
	 * @param mixed $email
	 * @return SelfRegisteredPatient
	 */
	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getContact()
	{
		return $this->contact;
	}

	/**
	 * @param mixed $contact_id
	 * @return SelfRegisteredPatient
	 */
	public function setContact($contact)
	{
		$this->contact = $contact;
		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getNextKinAddress()
	{
		return $this->next_kin_address;
	}

	/**
	 * @param mixed $next_kin_address
	 * @return SelfRegisteredPatient
	 */
	public function setNextKinAddress($next_kin_address)
	{
		$this->next_kin_address = $next_kin_address;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDateOfBirth()
	{
		return $this->date_of_birth;
	}

	/**
	 * @param mixed $date_of_birth
	 * @return SelfRegisteredPatient
	 */
	public function setDateOfBirth($date_of_birth)
	{
		$this->date_of_birth = $date_of_birth;
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
	 * @param mixed $country_id
	 * @return SelfRegisteredPatient
	 */
	public function setCountry($country)
	{
		$this->country = $country;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @param mixed $state_id
	 * @return SelfRegisteredPatient
	 */
	public function setState($state)
	{
		$this->state = $state;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDistrict()
	{
		return $this->district;
	}

	/**
	 * @param mixed $district_id
	 * @return SelfRegisteredPatient
	 */
	public function setDistrict($district)
	{
		$this->district = $district;
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
	 * @return SelfRegisteredPatient
	 */
	public function setOccupation($occupation)
	{
		$this->occupation = $occupation;
		return $this;
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
	 * @return SelfRegisteredPatient
	 */
	public function setWorkAddress($work_address)
	{
		$this->work_address = $work_address;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getResState()
	{
		return $this->res_state;
	}

	/**
	 * @param mixed $res_state_id
	 * @return SelfRegisteredPatient
	 */
	public function setResState($res_state)
	{
		$this->res_state = $res_state;
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
	 * @param mixed $industry_id
	 * @return SelfRegisteredPatient
	 */
	public function setIndustry($industry)
	{
		$this->industry = $industry;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getResDistrict()
	{
		return $this->res_district;
	}

	/**
	 * @param mixed $res_district_id
	 * @return SelfRegisteredPatient
	 */
	public function setResDistrict($res_district)
	{
		$this->res_district = $res_district;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLga()
	{
		return $this->lga;
	}

	/**
	 * @param mixed $lga_id
	 * @return SelfRegisteredPatient
	 */
	public function setLga($lga)
	{
		$this->lga = $lga;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getResAddress()
	{
		return $this->res_address;
	}

	/**
	 * @param mixed $res_address
	 * @return SelfRegisteredPatient
	 */
	public function setResAddress($res_address)
	{
		$this->res_address = $res_address;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBloodGroup()
	{
		return $this->blood_group;
	}

	/**
	 * @param mixed $blood_group
	 * @return SelfRegisteredPatient
	 */
	public function setBloodGroup($blood_group)
	{
		$this->blood_group = $blood_group;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getGenotype()
	{
		return $this->genotype;
	}

	/**
	 * @param mixed $genotype
	 * @return SelfRegisteredPatient
	 */
	public function setGenotype($genotype)
	{
		$this->genotype = $genotype;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRelationship()
	{
		return $this->relationship;
	}

	/**
	 * @param mixed $relationship_id
	 * @return SelfRegisteredPatient
	 */
	public function setRelationship($relationship)
	{
		$this->relationship = $relationship;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getReligion()
	{
		return $this->religion;
	}

	/**
	 * @param mixed $religion_id
	 * @return SelfRegisteredPatient
	 */
	public function setReligion($religion)
	{
		$this->religion = $religion;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getNextKinFname()
	{
		return $this->next_kin_fname;
	}

	/**
	 * @param mixed $next_kin_fname
	 * @return SelfRegisteredPatient
	 */
	public function setNextKinFname($next_kin_fname)
	{
		$this->next_kin_fname = $next_kin_fname;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getNextKinLname()
	{
		return $this->next_kin_lname;
	}

	/**
	 * @param mixed $next_kin_lname
	 * @return SelfRegisteredPatient
	 */
	public function setNextKinLname($next_kin_lname)
	{
		$this->next_kin_lname = $next_kin_lname;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getNextKinPhone()
	{
		return $this->next_kin_phone;
	}

	/**
	 * @param mixed $next_kin_phone
	 * @return SelfRegisteredPatient
	 */
	public function setNextKinPhone($next_kin_phone)
	{
		$this->next_kin_phone = $next_kin_phone;
		return $this;
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
     * @return SelfRegisteredPatient
     */
    public function setSpokenLang($spokenLang)
    {
        $this->spokenLang = $spokenLang;
        return $this;
    }

    /**
     * @return mixed
     */



	function jsonSerialize()
	{
		// TODO: Implement jsonSerialize() method.
		return (object)get_object_vars(this);
	}


}
