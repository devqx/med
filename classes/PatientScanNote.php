<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/17/14
 * Time: 12:28 PM
 */

class PatientScanNote implements JsonSerializable
{
	private $id;
	private $patient_scan;
	private $note;
	private $isComment=false;
	private $area;
	
	private $dateAdded;
	private $creator;
	
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
	 * @return PatientScanNote
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getPatientScan()
	{
		return $this->patient_scan;
	}
	
	/**
	 * @param mixed $patient_scan
	 *
	 * @return PatientScanNote
	 */
	public function setPatientScan($patient_scan)
	{
		$this->patient_scan = $patient_scan;
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
	 * @return PatientScanNote
	 */
	public function setNote($note)
	{
		$this->note = $note;
		return $this;
	}
	
	
	
	/**
	 * @return mixed
	 */
	public function getArea()
	{
		return $this->area;
	}
	
	/**
	 * @param mixed $area
	 *
	 * @return PatientScanNote
	 */
	public function setArea($area)
	{
		$this->area = $area;
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
	 * @return PatientScanNote
	 */
	public function setDateAdded($dateAdded)
	{
		$this->dateAdded = $dateAdded;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCreator()
	{
		return $this->creator;
	}
	
	/**
	 * @param mixed $creator
	 *
	 * @return PatientScanNote
	 */
	public function setCreator($creator)
	{
		$this->creator = $creator;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isComment(): bool
	{
		return $this->isComment;
	}
	
	/**
	 * @param bool $isComment
	 *
	 * @return PatientScanNote
	 */
	public function setIsComment(bool $isComment): PatientScanNote
	{
		$this->isComment = $isComment;
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











