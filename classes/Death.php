<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/13/15
 * Time: 3:45 PM
 */
class Death implements JsonSerializable
{
	private $id;
	private $certNumber;
	private $ageAtDeath;
	private $timeOfDeath;
	private $patient;
	private $deathCausePrimary;
	private $deathCauseSecondary;
	private $inPatient;
	private $validatedBy;
	private $validatedOn;
	private $createUser;
	private $createDate;
	
	/**
	 * Death constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null)
	{
		$this->id = $id;
	}
	
	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	/**
	 * @return null
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @param null $id
	 *
	 * @return Death
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCertNumber()
	{
		return $this->certNumber;
	}
	
	/**
	 * @param mixed $certNumber
	 *
	 * @return Death
	 */
	public function setCertNumber($certNumber)
	{
		$this->certNumber = $certNumber;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAgeAtDeath()
	{
		return $this->ageAtDeath;
	}
	
	/**
	 * @param mixed $ageAtDeath
	 *
	 * @return Death
	 */
	public function setAgeAtDeath($ageAtDeath)
	{
		$this->ageAtDeath = $ageAtDeath;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getTimeOfDeath()
	{
		return $this->timeOfDeath;
	}
	
	/**
	 * @param mixed $timeOfDeath
	 *
	 * @return Death
	 */
	public function setTimeOfDeath($timeOfDeath)
	{
		$this->timeOfDeath = $timeOfDeath;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getPatient()
	{
		return $this->patient;
	}
	
	/**
	 * @param mixed $patient
	 *
	 * @return Death
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDeathCausePrimary()
	{
		return $this->deathCausePrimary;
	}
	
	/**
	 * @param mixed $deathCausePrimary
	 *
	 * @return Death
	 */
	public function setDeathCausePrimary($deathCausePrimary)
	{
		$this->deathCausePrimary = $deathCausePrimary;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDeathCauseSecondary()
	{
		return $this->deathCauseSecondary;
	}
	
	/**
	 * @param mixed $deathCauseSecondary
	 *
	 * @return Death
	 */
	public function setDeathCauseSecondary($deathCauseSecondary)
	{
		$this->deathCauseSecondary = $deathCauseSecondary;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getValidatedBy()
	{
		return $this->validatedBy;
	}
	
	/**
	 * @param mixed $validatedBy
	 *
	 * @return Death
	 */
	public function setValidatedBy($validatedBy)
	{
		$this->validatedBy = $validatedBy;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getValidatedOn()
	{
		return $this->validatedOn;
	}
	
	/**
	 * @param mixed $validatedOn
	 *
	 * @return Death
	 */
	public function setValidatedOn($validatedOn)
	{
		$this->validatedOn = $validatedOn;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCreateUser()
	{
		return $this->createUser;
	}
	
	/**
	 * @param mixed $createUser
	 *
	 * @return Death
	 */
	public function setCreateUser($createUser)
	{
		$this->createUser = $createUser;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCreateDate()
	{
		return $this->createDate;
	}
	
	/**
	 * @param mixed $createDate
	 *
	 * @return Death
	 */
	public function setCreateDate($createDate)
	{
		$this->createDate = $createDate;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getInPatient()
	{
		return $this->inPatient;
	}
	
	/**
	 * @param mixed $inPatient
	 *
	 * @return Death
	 */
	public function setInPatient($inPatient)
	{
		$this->inPatient = $inPatient;
		return $this;
	}
	
	
	function update($pdo=null){
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
			
			$patient = $this->getPatient() ? $this->getPatient()->getId() : 'null';
			$primary = $this->getDeathCausePrimary() ? $this->getDeathCausePrimary()->getId() : 'null';
			$secondary = $this->getDeathCauseSecondary() ? $this->getDeathCauseSecondary()->getId() : 'null';
			$timeOfDeath = quote_esc_str($this->getTimeOfDeath());
			
			$sql = "UPDATE death SET patient_id=$patient, primary_cause_id=$primary, secondary_cause_id=$secondary, datetime_of_death=$timeOfDeath WHERE id = {$this->getId()}";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()<=1){
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}