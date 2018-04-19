<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Admission
 *
 * @author pauldic
 */
class InPatient implements JsonSerializable
{
	private $id;
	private $patient;
	private $ward;
	private $bed;
	private $dateAdmitted;
	private $admittedBy;
	private $status;
	private $reason;
	private $dateDischarged;
	private $anticipatedDischargeDate;
	private $dischargeNote;
	private $dischargedBy;
	private $clinic;
	private $billStatus;

	private $patientCareMembers;
	private $clinicalTask;
	private $mCount;
	private $search;
	private $claimed;
	private $diagnoses;
	private $bills;

	private $labourInstance;
	private $nextAppointment;
	private $nextMedication;

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
	 * @return InPatient
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
	 * @return InPatient
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getWard()
	{
		return $this->ward;
	}

	/**
	 * @param mixed $ward
	 * @return InPatient
	 */
	public function setWard($ward)
	{
		$this->ward = $ward;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBed()
	{
		return $this->bed;
	}

	/**
	 * @param mixed $bed
	 * @return InPatient
	 */
	public function setBed($bed)
	{
		$this->bed = $bed;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDateAdmitted()
	{
		return $this->dateAdmitted;
	}

	/**
	 * @param mixed $dateAdmitted
	 * @return InPatient
	 */
	public function setDateAdmitted($dateAdmitted)
	{
		$this->dateAdmitted = $dateAdmitted;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAdmittedBy()
	{
		return $this->admittedBy;
	}

	/**
	 * @param mixed $admittedBy
	 * @return InPatient
	 */
	public function setAdmittedBy($admittedBy)
	{
		$this->admittedBy = $admittedBy;
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
	 * @return InPatient
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getReason()
	{
		return $this->reason;
	}

	/**
	 * @param mixed $reason
	 * @return InPatient
	 */
	public function setReason($reason)
	{
		$this->reason = $reason;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDateDischarged()
	{
		return $this->dateDischarged;
	}

	/**
	 * @param mixed $dateDischarged
	 * @return InPatient
	 */
	public function setDateDischarged($dateDischarged)
	{
		$this->dateDischarged = $dateDischarged;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAnticipatedDischargeDate()
	{
		return $this->anticipatedDischargeDate;
	}

	/**
	 * @param mixed $anticipatedDischargeDate
	 * @return InPatient
	 */
	public function setAnticipatedDischargeDate($anticipatedDischargeDate)
	{
		$this->anticipatedDischargeDate = $anticipatedDischargeDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDischargeNote()
	{
		return $this->dischargeNote;
	}

	/**
	 * @param mixed $dischargeNote
	 * @return InPatient
	 */
	public function setDischargeNote($dischargeNote)
	{
		$this->dischargeNote = $dischargeNote;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDischargedBy()
	{
		return $this->dischargedBy;
	}

	/**
	 * @param mixed $dischargedBy
	 * @return InPatient
	 */
	public function setDischargedBy($dischargedBy)
	{
		$this->dischargedBy = $dischargedBy;
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
	 * @return InPatient
	 */
	public function setClinic($clinic)
	{
		$this->clinic = $clinic;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBillStatus()
	{
		return $this->billStatus;
	}

	/**
	 * @param mixed $billStatus
	 * @return InPatient
	 */
	public function setBillStatus($billStatus)
	{
		$this->billStatus = $billStatus;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPatientCareMembers()
	{
		return $this->patientCareMembers;
	}

	/**
	 * @param mixed $patientCareMembers
	 * @return InPatient
	 */
	public function setPatientCareMembers($patientCareMembers)
	{
		$this->patientCareMembers = $patientCareMembers;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getClinicalTask()
	{
		return $this->clinicalTask;
	}

	/**
	 * @param mixed $clinicalTask
	 * @return InPatient
	 */
	public function setClinicalTask($clinicalTask)
	{
		$this->clinicalTask = $clinicalTask;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMCount()
	{
		return $this->mCount;
	}

	/**
	 * @param mixed $mCount
	 * @return InPatient
	 */
	public function setMCount($mCount)
	{
		$this->mCount = $mCount;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSearch()
	{
		return $this->search;
	}

	/**
	 * @param mixed $search
	 * @return InPatient
	 */
	public function setSearch($search)
	{
		$this->search = $search;
		return $this;
	}


	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}

	/**
	 * @return mixed
	 */
	public function getClaimed()
	{
		return $this->claimed;
	}

	/**
	 * @param mixed $claimed
	 * @return InPatient
	 */
	public function setClaimed($claimed)
	{
		$this->claimed = $claimed;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDiagnoses()
	{
		return $this->diagnoses;
	}

	/**
	 * @param mixed $diagnoses
	 * @return InPatient
	 */
	public function setDiagnoses($diagnoses)
	{
		$this->diagnoses = $diagnoses;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBills()
	{
		return $this->bills;
	}

	/**
	 * @param mixed $bills
	 * @return InPatient
	 */
	public function setBills($bills)
	{
		$this->bills = $bills;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLabourInstance()
	{
		return $this->labourInstance;
	}

	/**
	 * @param mixed $labourInstance
	 * @return InPatient
	 */
	public function setLabourInstance($labourInstance)
	{
		$this->labourInstance = $labourInstance;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getNextAppointment()
	{
		return $this->nextAppointment;
	}
	
	/**
	 * @param mixed $nextAppointment
	 *
	 * @return InPatient
	 */
	public function setNextAppointment($nextAppointment)
	{
		$this->nextAppointment = $nextAppointment;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getNextMedication()
	{
		return $this->nextMedication;
	}
	
	/**
	 * @param mixed $nextMedication
	 *
	 * @return InPatient
	 */
	public function setNextMedication($nextMedication)
	{
		$this->nextMedication = $nextMedication;
		return $this;
	}

	
	
	
	public function update($pdo=null){
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$labour = $this->getLabourInstance() ? $this->getLabourInstance()->getId() : "NULL";
		$sql = "UPDATE in_patient SET labour_enrollment_id = $labour WHERE id={$this->getId()}";
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function updateWard($pdo = null)
	{
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$wardId = $this->getWard() ? $this->getWard()->getId() : "NULL";
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "UPDATE in_patient SET ward_id = $wardId WHERE id ={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return $this;
			}

			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}
