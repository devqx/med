<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PrescriptionData
 *
 * @author pauldic
 */
class PrescriptionData implements JsonSerializable
{
	private $id;
	private $code;
	private $drug;
	private $generic;
	private $quantity;
	private $dose;
	private $duration;
	private $comment;
	private $frequency;
	private $refillable;
	private $refillDate;
	private $refillNumber;
	private $refill_state;
	private $refill_status;
	private $status;
	private $requestedBy;
	private $modifiedBy;
	private $filledBy;
	private $filledOn;
	private $completedBy;
	private $completedOn;
	private $cancelledBy;
	private $cancelledOn;
	private $cancelNote;
	
	private $batch;
	
	private $hospital;
	private $bodypart;
	private $externalSource;
	
	private $bill;
	
	private $related;
	private $substituted_on;
	private $substituted_by;
	private $substitution_reason;
	private $diagnosis;
	
	/**
	 * @return mixed
	 */
	public function getBodypart()
	{
		return $this->bodypart;
	}
	
	/**
	 * @param mixed $bodypart
	 *
	 * @return PrescriptionData
	 */
	public function setBodyPart($bodypart)
	{
		$this->bodypart = $bodypart;
		return $this;
	}
	
	function __construct($id = null)
	{
		$this->id = $id;
	}
	
	/**
	 * @return mixed
	 */
	public function getBatch()
	{
		return $this->batch;
	}
	
	/**
	 * @param mixed $batch
	 *
	 * @return $this
	 */
	public function setBatch($batch)
	{
		$this->batch = $batch;
		return $this;
		
	}
	
	
	/**
	 * @return mixed
	 */
	public function getRefillDate()
	{
		return $this->refillDate;
	}
	
	/**
	 * @param mixed $refillDate
	 *
	 * @return PrescriptionData
	 */
	public function setRefillDate($refillDate)
	{
		$this->refillDate = $refillDate;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getRefillNumber()
	{
		return $this->refillNumber;
	}
	
	/**
	 * @param mixed $refillNumber
	 *
	 * @return PrescriptionData
	 */
	public function setRefillNumber($refillNumber)
	{
		$this->refillNumber = $refillNumber;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getRefillStatus()
	{
		return $this->refill_status;
	}
	
	/**
	 * @param mixed $refill_status
	 *
	 * @return PrescriptionData
	 */
	public function setRefillStatus($refill_status)
	{
		$this->refill_status = $refill_status;
		return $this;
	}
	
	
	/**
	 * @return mixed
	 */
	public function getRefillState()
	{
		return $this->refill_state;
	}
	
	/**
	 * @param mixed $refill_state
	 *
	 * @return PrescriptionData
	 */
	public function setRefillState($refill_state)
	{
		$this->refill_state = $refill_state;
		return $this;
	}
	
	
	/**
	 * @return mixed
	 */
	public function getCancelledOn()
	{
		return $this->cancelledOn;
	}
	
	/**
	 * @param mixed $cancelledOn
	 *
	 * @return $this
	 */
	public function setCancelledOn($cancelledOn)
	{
		$this->cancelledOn = $cancelledOn;
		return $this;
		
	}
	
	/**
	 * @return mixed
	 */
	public function getCancelNote()
	{
		return $this->cancelNote;
	}
	
	/**
	 * @param mixed $cancelNote
	 *
	 * @return PrescriptionData
	 */
	public function setCancelNote($cancelNote)
	{
		$this->cancelNote = $cancelNote;
		return $this;
	}
	
	
	/**
	 * @return mixed
	 */
	public function getCompletedOn()
	{
		return $this->completedOn;
	}
	
	/**
	 * @param mixed $completedOn
	 *
	 * @return $this
	 */
	public function setCompletedOn($completedOn)
	{
		$this->completedOn = $completedOn;
		return $this;
		
	}
	
	/**
	 * @return mixed
	 */
	public function getFilledOn()
	{
		return $this->filledOn;
	}
	
	/**
	 * @param mixed $filledOn
	 *
	 * @return $this
	 */
	public function setFilledOn($filledOn)
	{
		$this->filledOn = $filledOn;
		return $this;
		
	}
	
	/**
	 * @return mixed
	 */
	public function getCancelledBy()
	{
		return $this->cancelledBy;
	}
	
	/**
	 * @param mixed $cancelledBy
	 *
	 * @return $this
	 */
	public function setCancelledBy($cancelledBy)
	{
		$this->cancelledBy = $cancelledBy;
		return $this;
		
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function setId($id)
	{
		$this->id = $id;
		return $this;
		
	}
	
	public function getCode()
	{
		return $this->code;
	}
	
	public function setCode($code)
	{
		$this->code = $code;
		return $this;
		
	}
	
	public function getDrug()
	{
		return $this->drug;
	}
	
	public function setDrug($drug)
	{
		$this->drug = $drug;
		return $this;
		
	}
	
	public function getGeneric()
	{
		return $this->generic;
	}
	
	public function setGeneric($generic)
	{
		$this->generic = $generic;
		return $this;
		
	}
	
	public function getQuantity()
	{
		return $this->quantity;
	}
	
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
		return $this;
		
	}
	
	public function getDose()
	{
		return $this->dose;
	}
	
	public function setDose($dose)
	{
		$this->dose = $dose;
		return $this;
		
	}
	
	public function getDuration()
	{
		return $this->duration;
	}
	
	public function setDuration($duration)
	{
		$this->duration = $duration;
		return $this;
		
	}
	
	/**
	 * @return mixed
	 */
	public function getComment()
	{
		return $this->comment;
	}
	
	/**
	 * @param mixed $comment
	 *
	 * @return PrescriptionData
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
		return $this;
	}
	
	public function getFrequency()
	{
		return $this->frequency;
	}
	
	public function setFrequency($frequency)
	{
		$this->frequency = $frequency;
		return $this;
		
	}
	
	public function isRefillable()
	{
		return $this->refillable;
	}
	
	public function getStatus()
	{
		return $this->status;
	}
	
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
		
	}
	
	public function getRequestedBy()
	{
		return $this->requestedBy;
	}
	
	public function setRequestedBy($requestedBy)
	{
		$this->requestedBy = $requestedBy;
		return $this;
		
	}
	
	public function getModifiedBy()
	{
		return $this->modifiedBy;
	}
	
	public function setModifiedBy($modifiedBy)
	{
		$this->modifiedBy = $modifiedBy;
		return $this;
		
	}
	
	public function getFilledBy()
	{
		return $this->filledBy;
	}
	
	public function setFilledBy($filledBy)
	{
		$this->filledBy = $filledBy;
		return $this;
		
	}
	
	public function getCompletedBy()
	{
		return $this->completedBy;
	}
	
	public function setCompletedBy($completedBy)
	{
		$this->completedBy = $completedBy;
		return $this;
	}
	
	public function getHospital()
	{
		return $this->hospital;
	}
	
	public function setHospital($hospital)
	{
		$this->hospital = $hospital;
		return $this;
	}
	
	public function setRefillable($refillable)
	{
		$this->refillable = $refillable;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getRefillable()
	{
		return $this->refillable;
	}
	
	
	/**
	 * @return mixed
	 */
	public function getExternalSource()
	{
		return $this->externalSource;
	}
	
	/**
	 * @param mixed $externalSource
	 *
	 * @return PrescriptionData
	 */
	public function setExternalSource($externalSource)
	{
		$this->externalSource = $externalSource;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getBill()
	{
		return $this->bill;
	}
	
	/**
	 * @param mixed $bill
	 *
	 * @return PrescriptionData
	 */
	public function setBill($bill)
	{
		$this->bill = $bill;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getRelated()
	{
		return $this->related;
	}
	
	/**
	 * @param mixed $related
	 *
	 * @return PrescriptionData
	 */
	public function setRelated($related)
	{
		$this->related = $related;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getSubstitutedOn()
	{
		return $this->substituted_on;
	}
	
	/**
	 * @param mixed $substituted_on
	 *
	 * @return PrescriptionData
	 */
	public function setSubstitutedOn($substituted_on)
	{
		$this->substituted_on = $substituted_on;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getSubstitutedBy()
	{
		return $this->substituted_by;
	}
	
	/**
	 * @param mixed $substituted_by
	 *
	 * @return PrescriptionData
	 */
	public function setSubstitutedBy($substituted_by)
	{
		$this->substituted_by = $substituted_by;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getSubstitutionReason()
	{
		return $this->substitution_reason;
	}
	
	/**
	 * @param mixed $substitution_reason
	 *
	 * @return PrescriptionData
	 */
	public function setSubstitutionReason($substitution_reason)
	{
		$this->substitution_reason = $substitution_reason;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDiagnosis()
	{
		return $this->diagnosis;
	}
	
	/**
	 * @param mixed $diagnosis
	 *
	 * @return PrescriptionData
	 */
	public function setDiagnosis($diagnosis)
	{
		$this->diagnosis = $diagnosis;
		return $this;
	}
	
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	function add($pdo=NULL){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
		
		return (new PrescriptionDataDAO())->addPrescriptionData([$this], $pdo);
	}
	
	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			//todo complete the other properties to be updated
			$refillable = $this->getRefillable() ? var_export($this->getRefillable(), true) : 'FALSE';
			$refillNumber = $this->getRefillNumber() ? $this->getRefillNumber() : 0;
			$refillDate = $this->getRefillDate() ? quote_esc_str($this->getRefillDate()) : 'null';
			$parent  = $this->getRelated() ? $this->getRelated()->getId() : "NULL";
			$status = quote_esc_str($this->getStatus());
			$reason = !is_blank($this->getSubstitutionReason()) ? quote_esc_str($this->getSubstitutionReason()) : "NULL";
			$substituted_by = $this->getSubstitutedBy() ? $this->getSubstitutedBy()->getId() : "NULL";
			$substituted_on = !is_blank($this->getSubstitutedOn()) ? quote_esc_str($this->getSubstitutedOn()) : "NULL";
			$sql = "UPDATE patient_regimens_data SET `status`=$status, related_id=$parent, substituted_on=$substituted_on, substituted_by=$substituted_by, substitution_reason=$reason, refillable=$refillable, refill_number=$refillNumber, refill_date=$refillDate WHERE id={$this->getId()}";
			
			//error_log($sql);
			
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return $this;
			}
			return null;
			
		} catch (PDOException $exception) {
			errorLog($exception);
			return null;
		}
	}
}
