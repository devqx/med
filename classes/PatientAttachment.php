<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/22/15
 * Time: 11:31 AM
 */
class PatientAttachment implements JsonSerializable
{
	private $id;
	private $patient;
	private $note;
	private $category;
	private $url;
	private $dateAdded;
	private $user;
	private $encounter;
	private $is_deleted;
	private $deleted_by;
	
	function __construct($id = null)
	{
		$this->id = $id;
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
	 * @return PatientAttachment
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 * @return PatientAttachment
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getNote()
	{
		return $this->note;
	}
	
	/**
	 * @param mixed $note
	 *
	 * @return PatientAttachment
	 */
	public function setNote($note)
	{
		$this->note = $note;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCategory()
	{
		return $this->category;
	}
	
	/**
	 * @param mixed $category
	 *
	 * @return PatientAttachment
	 */
	public function setCategory($category)
	{
		$this->category = $category;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getUrl()
	{
		return $this->url;
	}
	
	/**
	 * @param mixed $url
	 *
	 * @return PatientAttachment
	 */
	public function setUrl($url)
	{
		$this->url = $url;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDateAdded()
	{
		return $this->dateAdded;
	}
	
	/**
	 * @param mixed $dateAdded
	 *
	 * @return PatientAttachment
	 */
	public function setDateAdded($dateAdded)
	{
		$this->dateAdded = $dateAdded;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getUser()
	{
		return $this->user;
	}
	
	/**
	 * @param mixed $user
	 *
	 * @return PatientAttachment
	 */
	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getEncounter()
	{
		return $this->encounter;
	}
	
	/**
	 * @param mixed $encounter
	 *
	 * @return PatientAttachment
	 */
	public function setEncounter($encounter)
	{
		$this->encounter = $encounter;
		return $this;
	}

    /**
     * @return mixed
     */
    public function getIsDeleted()
    {
        return $this->is_deleted;
    }

    /**
     * @param mixed $is_deleted
     */
    public function setIsDeleted($is_deleted)
    {
        $this->is_deleted = $is_deleted;
    }

    /**
     * @return mixed
     */
    public function getDeletedBy()
    {
        return $this->deleted_by;
    }

    /**
     * @param mixed $deleted_by
     */
    public function setDeletedBy($deleted_by)
    {
        $this->deleted_by = $deleted_by;
    }

	
	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
}