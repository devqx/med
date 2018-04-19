<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/6/15
 * Time: 9:54 AM
 */
class AntenatalAssessment implements JsonSerializable
{
	
	private $id;
	private $date;
	private $user;
	private $antenatalInstance;
	private $fundusHeight;
	private $fhr;
	private $fetalPresentation;
	private $fetalBrainRelationship;
	private $fetalLie;
	private $comments;
	private $lab;
	private $scan;
	private $nextAppointmentDate;
	
	private $data;
	
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
	 * @return AntenatalAssessment
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return null
	 */
	public function getDate()
	{
		return $this->date;
	}
	
	/**
	 * @param null $date
	 *
	 * @return AntenatalAssessment
	 */
	public function setDate($date)
	{
		$this->date = $date;
		return $this;
	}
	
	/**
	 * @return null
	 */
	public function getUser()
	{
		return $this->user;
	}
	
	/**
	 * @param null $user
	 *
	 * @return AntenatalAssessment
	 */
	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}
	
	/**
	 * @return null
	 */
	public function getData()
	{
		return $this->data;
	}
	
	/**
	 * @param null $data
	 *
	 * @return AntenatalAssessment
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAntenatalInstance()
	{
		return $this->antenatalInstance;
	}
	
	/**
	 * @param mixed $antenatalInstance
	 *
	 * @return AntenatalAssessment
	 */
	public function setAntenatalInstance($antenatalInstance)
	{
		$this->antenatalInstance = $antenatalInstance;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getFundusHeight()
	{
		return $this->fundusHeight;
	}
	
	/**
	 * @param mixed $fundusHeight
	 *
	 * @return $this
	 */
	public function setFundusHeight($fundusHeight)
	{
		$this->fundusHeight = $fundusHeight;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getFhr()
	{
		return $this->fhr;
	}
	
	/**
	 * @param mixed $fhr
	 *
	 * @return $this
	 */
	public function setFhr($fhr)
	{
		$this->fhr = $fhr;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getFetalPresentation()
	{
		return $this->fetalPresentation;
	}
	
	/**
	 * @param mixed $fetalPresentation
	 *
	 * @return $this
	 */
	public function setFetalPresentation($fetalPresentation)
	{
		$this->fetalPresentation = $fetalPresentation;
		return $this;
		
	}
	
	/**
	 * @return mixed
	 */
	public function getFetalBrainRelationship()
	{
		return $this->fetalBrainRelationship;
	}
	
	/**
	 * @param mixed $fetalBrainRelationship
	 *
	 * @return AntenatalAssessment
	 */
	public function setFetalBrainRelationship($fetalBrainRelationship)
	{
		$this->fetalBrainRelationship = $fetalBrainRelationship;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getFetalLie()
	{
		return $this->fetalLie;
	}
	
	/**
	 * @param mixed $fetalLie
	 *
	 * @return AntenatalAssessment
	 */
	public function setFetalLie($fetalLie)
	{
		$this->fetalLie = $fetalLie;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getComments()
	{
		return $this->comments;
	}
	
	/**
	 * @param mixed $comments
	 *
	 * @return $this
	 */
	public function setComments($comments)
	{
		$this->comments = $comments;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getLab()
	{
		return $this->lab;
	}
	
	/**
	 * @param mixed $lab
	 *
	 * @return $this
	 */
	public function setLab($lab)
	{
		$this->lab = $lab;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getScan()
	{
		return $this->scan;
	}
	
	/**
	 * @param mixed $scan
	 *
	 * @return $this
	 */
	public function setScan($scan)
	{
		$this->scan = $scan;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getNextAppointmentDate()
	{
		return $this->nextAppointmentDate;
	}
	
	/**
	 * @param mixed $nextAppointmentDate
	 *
	 * @return $this
	 */
	public function setNextAppointmentDate($nextAppointmentDate)
	{
		$this->nextAppointmentDate = $nextAppointmentDate;
		return $this;
	}
	
	
	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	
}