<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/13/14
 * Time: 2:42 PM
 */
class PatientDentistry implements JsonSerializable
{
	private $id;
	private $patient;
	private $services;
	private $requestNote;
	private $requested_by;
	private $request_date;
	private $approved;
	private $approved_by;
	private $date_last_modified;
	private $referral;
	
	private $requestCode;
	private $notes;
	
	private $status;
	private $approvedDate;
	
	private $cancelled;
	private $canceled_by;
	private $date_canceled;
	
	private $serviceCenter;
	
	function __construct($id = null)
	{
		$this->id = $id;
	}
	
	/**
	 * @return mixed
	 */
	public function getNotes()
	{
		return $this->notes;
	}
	
	/**
	 * @param mixed $notes
	 */
	public function setNotes($notes)
	{
		$this->notes = $notes;
	}
	
	/**
	 * @return mixed
	 */
	public function getRequestCode()
	{
		return $this->requestCode;
	}
	
	/**
	 * @param mixed $requestCode
	 */
	public function setRequestCode($requestCode)
	{
		$this->requestCode = $requestCode;
	}
	
	/**
	 * @return mixed
	 */
	public function getApproved()
	{
		return $this->approved;
	}
	
	/**
	 * @param mixed $approved
	 */
	public function setApproved($approved)
	{
		$this->approved = $approved;
	}
	
	/**
	 * @return mixed
	 */
	public function getApprovedBy()
	{
		return $this->approved_by;
	}
	
	/**
	 * @param mixed $approved_by
	 */
	public function setApprovedBy($approved_by)
	{
		$this->approved_by = $approved_by;
	}
	
	/**
	 * @return mixed
	 */
	public function getDateLastModified()
	{
		return $this->date_last_modified;
	}
	
	/**
	 * @param mixed $date_last_modified
	 */
	public function setDateLastModified($date_last_modified)
	{
		$this->date_last_modified = $date_last_modified;
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
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
	}
	
	/**
	 * @return mixed
	 */
	public function getRequestDate()
	{
		return $this->request_date;
	}
	
	/**
	 * @param mixed $request_date
	 */
	public function setRequestDate($request_date)
	{
		$this->request_date = $request_date;
	}
	
	/**
	 * @return mixed
	 */
	public function getRequestedBy()
	{
		return $this->requested_by;
	}
	
	/**
	 * @param mixed $requested_by
	 */
	public function setRequestedBy($requested_by)
	{
		$this->requested_by = $requested_by;
	}
	
	/**
	 * @return mixed
	 */
	public function getServices()
	{
		return $this->services;
	}
	
	/**
	 * @param mixed $services
	 */
	public function setServices($services)
	{
		$this->services = $services;
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
	public function getStatus()
	{
		return $this->status;
	}
	
	/**
	 * @param mixed $status
	 */
	public function setStatus($status)
	{
		$this->status = $status;
	}
	
	/**
	 * @return mixed
	 */
	public function getApprovedDate()
	{
		return $this->approvedDate;
	}
	
	/**
	 * @param mixed $approvedDate
	 */
	public function setApprovedDate($approvedDate)
	{
		$this->approvedDate = $approvedDate;
	}
	
	/**
	 * @return mixed
	 */
	public function getCancelled()
	{
		return (bool)$this->cancelled;
	}
	
	/**
	 * @param mixed $cancelled
	 */
	public function setCancelled($cancelled)
	{
		$this->cancelled = $cancelled;
	}
	
	/**
	 * @return mixed
	 */
	public function getCanceledBy()
	{
		return $this->canceled_by;
	}
	
	/**
	 * @param mixed $canceled_by
	 */
	public function setCanceledBy($canceled_by)
	{
		$this->canceled_by = $canceled_by;
	}
	
	/**
	 * @return mixed
	 */
	public function getDateCanceled()
	{
		return $this->date_canceled;
	}
	
	/**
	 * @param mixed $date_canceled
	 */
	public function setDateCanceled($date_canceled)
	{
		$this->date_canceled = $date_canceled;
	}
	
	/**
	 * @return mixed
	 */
	public function getRequestNote()
	{
		return $this->requestNote;
	}
	
	/**
	 * @param mixed $requestNote
	 */
	public function setRequestNote($requestNote)
	{
		$this->requestNote = $requestNote;
	}
	
	/**
	 * @return mixed
	 */
	public function getServiceCenter()
	{
		return $this->serviceCenter;
	}
	
	/**
	 * @param mixed $serviceCenter
	 *
	 * @return PatientDentistry
	 */
	public function setServiceCenter($serviceCenter)
	{
		$this->serviceCenter = $serviceCenter;
		return $this;
	}
	
	/**
	 * (PHP 5 &gt;= 5.4.0)<br/>
	 * Specify data which should be serialized to JSON
	 *
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 */
	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	
} 