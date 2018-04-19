<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StaffDirectory
 *
 * @author pauldic
 */
class StaffDirectory implements JsonSerializable
{
	private $id;
	private $firstName;
	private $lastName;
	private $phone;
	private $clinic;
	private $specialization;
	private $email;
	private $password;
	private $profession;
	private $username;
	private $roles;
	private $rolesRaw;
	private $status;
	private $department;
	private $fullname;
	private $shortname;

	private $careTeams;
	private $anId;
	private $sipUserName;
	private $sipPassword;
	private $sipExtension;
	private $folioNumber;

	private $is_consultant;

	private $is_doctor;

	function __construct($id = null)
	{
		$this->id = $id;
	}

	/**
	 * @param mixed $access
	 * @return boolean
	 */
	public function hasRole($access)
	{
		if ($this->getRoles() != null) {
			return in_array($access, $this->getRoles());
		}
		return false;
	}

	public function getRoles()
	{
		return $this->roles;
	}

	public function setRoles($roles)
	{
		$this->roles = $roles;
	}
	
	/**
	 * @return mixed
	 */
	public function getRolesRaw()
	{
		return $this->rolesRaw;
	}
	
	/**
	 * @param mixed $rolesRaw
	 *
	 * @return StaffDirectory
	 */
	public function setRolesRaw($rolesRaw)
	{
		$this->rolesRaw = $rolesRaw;
		return $this;
	}
	
	
	/**
	 * @return mixed
	 */
	public function getDepartment()
	{
		return $this->department;
	}

	/**
	 * @param mixed $department
	 */
	public function setDepartment($department)
	{
		$this->department = $department;
	}

	public function getId()
	{
		return $this->id;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function getFirstName()
	{
		return $this->firstName;
	}

	public function setFirstName($firstName)
	{
		$this->firstName = $firstName;
	}

	public function getLastName()
	{
		return $this->lastName;
	}

	public function setLastName($lastName)
	{
		$this->lastName = $lastName;
	}

	public function getPhone()
	{
		return $this->phone;
	}

	public function setPhone($phone)
	{
		$this->phone = $phone;
	}

	public function getClinic()
	{
		return $this->clinic;
	}

	public function setClinic($clinic)
	{
		$this->clinic = $clinic;
	}

	public function getSpecialization()
	{
		return $this->specialization;
	}

	public function setSpecialization($specialization)
	{
		$this->specialization = $specialization;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function setEmail($email)
	{
		$this->email = $email;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function setPassword($password)
	{
		$this->password = $password;
	}

	public function getProfession()
	{
		return $this->profession;
	}

	public function setProfession($profession)
	{
		$this->profession = $profession;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function setUsername($username)
	{
		$this->username = $username;
	}

	/**
	 * @return mixed
	 */
	public function getSipUserName()
	{
		return $this->sipUserName;
	}

	/**
	 * @param mixed $sipUserName
	 * @return StaffDirectory
	 */
	public function setSipUserName($sipUserName)
	{
		$this->sipUserName = $sipUserName;
		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getSipPassword()
	{
		return $this->sipPassword;
	}

	/**
	 * @param mixed $sipPassword
	 * @return StaffDirectory
	 */
	public function setSipPassword($sipPassword)
	{
		$this->sipPassword = $sipPassword;
		return $this;
	}
	

	public function getStatus()
	{
		return $this->status;
	}

	public function setStatus($status)
	{
		$this->status = $status;
	}

	public function getCareTeams()
	{
		return $this->careTeams;
	}

	public function setCareTeams($teams)
	{
		$this->careTeams = $teams;
	}

	public function getAnId()
	{
		return $this->anId;
	}

	public function setAnId($anId)
	{
		$this->anId = $anId;
	}

	public function __toString()
	{
		return $this->lastName . " " . $this->firstName;
	}

	/**
	 * @return mixed
	 */
	public function getSipExtension()
	{
		return $this->sipExtension;
	}

	/**
	 * @param mixed $sipExtension
	 * @return StaffDirectory
	 */
	public function setSipExtension($sipExtension)
	{
		$this->sipExtension = $sipExtension;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getFolioNumber()
	{
		return $this->folioNumber;
	}
	
	/**
	 * @param mixed $folioNumber
	 *
	 * @return StaffDirectory
	 */
	public function setFolioNumber($folioNumber)
	{
		$this->folioNumber = $folioNumber;
		return $this;
	}
	
	public function jsonSerialize()
	{
		$this->password = null;
		$this->fullname = $this->getFullname();
		$this->shortname = $this->getShortname();
		unset($this->roles);
		unset($this->anId);
		//unset($this->clinic);
		//unset($this->department);
		//unset($this->careTeams);
		return (object)get_object_vars($this);
	}

	public function getFullname()
	{
		return $this->lastName . " " . $this->firstName;
	}

	public function getShortname()
	{
		return $this->lastName . " " . $this->firstName{0} . ".";
	}

    /**
     * @return mixed
     */
    public function getisConsultant()
    {
        return $this->is_consultant;
    }

    /**
     * @param mixed $is_consultant
     * @return StaffDirectory
     */
    public function setIsConsultant($is_consultant)
    {
        $this->is_consultant = $is_consultant;
        return $this;
    }





}