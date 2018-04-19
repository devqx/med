<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of VitalSign
 *
 * @author pauldic
 * @modified robotp
 */
class VitalSign implements JsonSerializable
{
	private $id;
	private $value;
	private $patient;
	private $readDate;
	private $unixTime;
	private $type;
	private $inPatient;
	private $hospital;
	private $readBy;
	private $encounter;
	private $abnormal;
	
	
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
	 * @return VitalSign
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}
	
	/**
	 * @param mixed $value
	 *
	 * @return VitalSign
	 */
	public function setValue($value)
	{
		$this->value = $value;
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
	 * @return VitalSign
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getReadDate()
	{
		return $this->readDate;
	}
	
	/**
	 * @param mixed $readDate
	 *
	 * @return VitalSign
	 */
	public function setReadDate($readDate)
	{
		$this->readDate = $readDate;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getUnixTime()
	{
		return $this->unixTime;
	}
	
	/**
	 * @param mixed $unixTime
	 *
	 * @return VitalSign
	 */
	public function setUnixTime($unixTime)
	{
		$this->unixTime = $unixTime;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * @param mixed $type
	 *
	 * @return VitalSign
	 */
	public function setType($type)
	{
		$this->type = $type;
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
	 * @return VitalSign
	 */
	public function setInPatient($inPatient)
	{
		$this->inPatient = $inPatient;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getHospital()
	{
		return $this->hospital;
	}
	
	/**
	 * @param mixed $hospital
	 *
	 * @return VitalSign
	 */
	public function setHospital($hospital)
	{
		$this->hospital = $hospital;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getReadBy()
	{
		return $this->readBy;
	}
	
	/**
	 * @param mixed $readBy
	 *
	 * @return VitalSign
	 */
	public function setReadBy($readBy)
	{
		$this->readBy = $readBy;
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
	 * @return VitalSign
	 */
	public function setEncounter($encounter)
	{
		$this->encounter = $encounter;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAbnormal()
	{
		return $this->abnormal;
	}
	
	/**
	 * @param mixed $abnormal
	 *
	 * @return VitalSign
	 */
	public function setAbnormal($abnormal)
	{
		$this->abnormal = $abnormal;
		return $this;
	}
	
	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Alert.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AlertDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
		
		@session_start();
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$commitHere = !$pdo->inTransaction();
			try {$pdo->beginTransaction();}catch (PDOException $exception){}
			$patientId = $this->getPatient() ? $this->getPatient()->getId() : 'NULL';
			$readDate = $this->getReadDate() ? quote_esc_str($this->getReadDate()) : 'NOW()';
			$value = $this->getValue() ? quote_esc_str($this->getValue()) : 'NULL';
			$inpatientId = $this->getInPatient() ? $this->getInPatient()->getId() : 'NULL';
			$type = $this->getType() ? $this->getType()->getId() : 'NULL';
			$hospitalId = 1;
			$readBy = $this->getReadBy() ? $this->getReadBy()->getId() : $_SESSION['staffID'];
			$encounterId = $this->getEncounter() ? $this->getEncounter()->getId() : 'NULL';
			
			$vital = (new VitalDAO())->get($type, $pdo);
			$abnormal = false;
			
			$values = array_filter(explode('/', $this->getValue()));
			if ($vital->getMaximum() && $vital->getMinimum()) {
				if (count($values) == 1) {
					$val = $values[0];
					if (floatval($val) > floatval($vital->getMaximum()) || floatval($val) < floatval($vital->getMinimum())) {
						$abnormal = true;
					}
				} else if (count($values) == 2) {
					$maxs = array_filter(explode("/", $vital->getMaximum()));
					$mins = array_filter(explode("/", $vital->getMinimum()));
					if ((floatval($values[0]) > floatval($maxs[0]) || floatval($values[0]) < floatval($mins[0])) || (floatval($values[1]) > floatval($maxs[1]) || floatval($values[1]) < floatval($mins[1]))) {
						$abnormal = true;
					}
				}
				
				if($abnormal){
					$alert = new Alert();
					$alert->setMessage($vital->getName() . " Value ({$this->getValue()}) is not within the NORMAL range");
					$alert->setType($vital->getName());
					$alert->setPatient($this->getPatient());
					
					@(new AlertDAO())->add($alert, $pdo);
				}
			}
			
			$sql = "INSERT INTO vital_sign (patient_id, read_date, `value`, in_patient_id, type_id, hospital_id, read_by, encounter_id) VALUES ($patientId, $readDate, $value, $inpatientId, $type, $hospitalId, $readBy, $encounterId)";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$this->setId($pdo->lastInsertId());
				if($commitHere){$pdo->commit();}
				return $this;
			}
			if($commitHere){$pdo->rollBack();}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
}
