<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PatientQueue
 *
 * @author pauldic
 */
class PatientQueue implements JsonSerializable
{
	private $id;
	private $patient;
	private $type;
	private $subType;
	private $entryTime;
	private $attendedTime;
	private $tagNo;
	private $blockedBy;
	private $seenBy;
	private $specialization;
	private $department;
	private $status;
	private $followUp;
	private $review;
	private $encounter;
	private $clinic;
	private $clinic_name;
	
	// just used to transfer data to the trigger
	private $amount;
	
	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @param mixed $id
	 *
	 * @return PatientQueue
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
	 * @return PatientQueue
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
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
	 * @return PatientQueue
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getSubType()
	{
		return $this->subType;
	}
	
	/**
	 * @param mixed $subType
	 *
	 * @return PatientQueue
	 */
	public function setSubType($subType)
	{
		$this->subType = $subType;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getEntryTime()
	{
		return $this->entryTime;
	}
	
	/**
	 * @param mixed $entryTime
	 *
	 * @return PatientQueue
	 */
	public function setEntryTime($entryTime)
	{
		$this->entryTime = $entryTime;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAttendedTime()
	{
		return $this->attendedTime;
	}
	
	/**
	 * @param mixed $attendedTime
	 *
	 * @return PatientQueue
	 */
	public function setAttendedTime($attendedTime)
	{
		$this->attendedTime = $attendedTime;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getTagNo()
	{
		return $this->tagNo;
	}
	
	/**
	 * @param mixed $tagNo
	 *
	 * @return PatientQueue
	 */
	public function setTagNo($tagNo)
	{
		$this->tagNo = $tagNo;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getBlockedBy()
	{
		return $this->blockedBy;
	}
	
	/**
	 * @param mixed $blockedBy
	 *
	 * @return PatientQueue
	 */
	public function setBlockedBy($blockedBy)
	{
		$this->blockedBy = $blockedBy;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getSeenBy()
	{
		return $this->seenBy;
	}
	
	/**
	 * @param mixed $seenBy
	 *
	 * @return PatientQueue
	 */
	public function setSeenBy($seenBy)
	{
		$this->seenBy = $seenBy;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getSpecialization()
	{
		return $this->specialization;
	}
	
	/**
	 * @param mixed $specialization
	 *
	 * @return PatientQueue
	 */
	public function setSpecialization($specialization)
	{
		$this->specialization = $specialization;
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
	 *
	 * @return PatientQueue
	 */
	public function setDepartment($department)
	{
		$this->department = $department;
		return $this;
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
	 *
	 * @return PatientQueue
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getFollowUp()
	{
		return $this->followUp;
	}
	
	/**
	 * @param mixed $followUp
	 *
	 * @return PatientQueue
	 */
	public function setFollowUp($followUp)
	{
		$this->followUp = $followUp;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getReview()
	{
		return $this->review;
	}
	
	/**
	 * @param mixed $review
	 *
	 * @return PatientQueue
	 */
	public function setReview($review)
	{
		$this->review = $review;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAmount()
	{
		return $this->amount;
	}
	
	/**
	 * @param mixed $amount
	 *
	 * @return PatientQueue
	 */
	public function setAmount($amount)
	{
		$this->amount = $amount;
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
	 * @return PatientQueue
	 */
	public function setEncounter($encounter)
	{
		$this->encounter = $encounter;
		return $this;
	}

    /**
     * @return mixed
     */
    public function getClinic()
    {
        return $this->clinic;
    }

    /**
     * @param mixed $clinic
     * @return PatientQueue
     */
    public function setClinic($clinic)
    {
        $this->clinic = $clinic;
        return $this;
    }
	
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			
			$queue = (new PatientQueueDAO())->inQueue($this->getPatient()->getId(), $this->getType(), $this->getSpecialization(), false, $pdo);
			if ($queue !== null) {
				return $queue;
			}
			$patient = ($this->getPatient() ? $this->getPatient()->getId() : "NULL");
			$type = $this->getType() ? quote_esc_str($this->getType()) : "NULL";
			$clinic_id = $this->getClinic() ? $this->getClinic()->getId() : "NULL";
            //file_put_contents('/tmp/test.txt',json_encode( $clinic_id ) );
			$subType = $this->getSubType() ? quote_esc_str($this->getSubType()) : "NULL";
			$department = $this->getDepartment() ? $this->getDepartment()->getId() : "NULL";
			$tagNo = quote_esc_str((new PatientQueueDAO())->generateTagNo($clinic_id, $pdo));
			$specialization = $this->getSpecialization() ? $this->getSpecialization()->getId() : "NULL";
			
			$follow_up = ($this->getFollowUp() == null || $this->getFollowUp() == '') ? 0 : var_export($this->getFollowUp(), true);
			$review = ($this->getReview() == null || $this->getReview() == '') ? 0 : var_export($this->getReview(), true);
			$price = $this->getAmount() ? $this->getAmount() : 0;
			$encounterId = $this->getEncounter() ? $this->getEncounter()->getId() : 'null';
			
			$sql = "INSERT INTO patient_queue (patient_id, type, sub_type, department_id, tag_no, specialization_id, follow_up, review, amount, encounter_id, clinic_id) VALUES ($patient, $type, $subType, $department, $tagNo, $specialization, $follow_up, $review, $price, $encounterId,$clinic_id)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));



			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
		}
		return null;
	}



}