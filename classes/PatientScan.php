<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/13/14
 * Time: 2:42 PM
 */
class PatientScan implements JsonSerializable
{
	private $id;
	private $patient;
	private $scan;
	private $requestNote;
	private $requested_by;
	private $request_date;
	private $approved;
	private $approved_by;
	private $date_last_modified;
	private $referral;

	private $requestCode;
	private $attachments;
	private $notes;

	private $status;
	private $approvedDate;

	private $cancelled;
	private $canceled_by;
	private $date_canceled;
	private $captured;
	private $capturedBy;
	private $capturedDate;
	private $encounter;

	private $serviceCentre;
	
	private $resource;
	private $schedule_date_start;
	private $schedule_date_end;
	private $scheduled_on;
	private $scheduled_by;
	private $bill;
	private $appointment;

	function __construct($id = null)
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
	 * @return PatientScan
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
	 * @return PatientScan
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
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
	 * @param mixed $scans
	 * @return PatientScan
	 */
	public function setScan($scans)
	{
		$this->scan = $scans;
		return $this;
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
	 * @return PatientScan
	 */
	public function setRequestNote($requestNote)
	{
		$this->requestNote = $requestNote;
		return $this;
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
	 * @return PatientScan
	 */
	public function setRequestedBy($requested_by)
	{
		$this->requested_by = $requested_by;
		return $this;
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
	 * @return PatientScan
	 */
	public function setRequestDate($request_date)
	{
		$this->request_date = $request_date;
		return $this;
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
	 * @return PatientScan
	 */
	public function setApproved($approved)
	{
		$this->approved = $approved;
		return $this;
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
	 * @return PatientScan
	 */
	public function setApprovedBy($approved_by)
	{
		$this->approved_by = $approved_by;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCaptured()
	{
		return $this->captured;
	}

	/**
	 * @param mixed $captured
	 * @return PatientScan
	 */
	public function setCaptured($captured)
	{
		$this->captured = $captured;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCapturedBy()
	{
		return $this->capturedBy;
	}

	/**
	 * @param mixed $capturedBy
	 * @return PatientScan
	 */
	public function setCapturedBy($capturedBy)
	{
		$this->capturedBy = $capturedBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCapturedDate()
	{
		return $this->capturedDate;
	}

	/**
	 * @param mixed $capturedDate
	 * @return PatientScan
	 */
	public function setCapturedDate($capturedDate)
	{
		$this->capturedDate = $capturedDate;
		return $this;
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
	 * @return PatientScan
	 */
	public function setDateLastModified($date_last_modified)
	{
		$this->date_last_modified = $date_last_modified;
		return $this;
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
	 * @return PatientScan
	 */
	public function setReferral($referral)
	{
		$this->referral = $referral;
		return $this;
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
	 * @return PatientScan
	 */
	public function setRequestCode($requestCode)
	{
		$this->requestCode = $requestCode;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAttachments()
	{
		return $this->attachments;
	}

	/**
	 * @param mixed $attachments
	 * @return PatientScan
	 */
	public function setAttachments($attachments)
	{
		$this->attachments = $attachments;
		return $this;
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
	 * @return PatientScan
	 */
	public function setNotes($notes)
	{
		$this->notes = $notes;
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
	 * @return PatientScan
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
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
	 * @return PatientScan
	 */
	public function setApprovedDate($approvedDate)
	{
		$this->approvedDate = $approvedDate;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getCancelled()
	{
		return (bool)$this->cancelled;
	}

	/**
	 * @param mixed $cancelled
	 * @return PatientScan
	 */
	public function setCancelled($cancelled)
	{
		$this->cancelled = $cancelled;
		return $this;
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
	 * @return PatientScan
	 */
	public function setCanceledBy($canceled_by)
	{
		$this->canceled_by = $canceled_by;
		return $this;
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
	 * @return PatientScan
	 */
	public function setDateCanceled($date_canceled)
	{
		$this->date_canceled = $date_canceled;
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
	 * @return PatientScan
	 */
	public function setEncounter($encounter)
	{
		$this->encounter = $encounter;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getServiceCentre()
	{
		return $this->serviceCentre;
	}

	/**
	 * @param mixed $serviceCentre
	 * @return PatientScan
	 */
	public function setServiceCentre($serviceCentre)
	{
		$this->serviceCentre = $serviceCentre;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getResource()
	{
		return $this->resource;
	}
	
	/**
	 * @param mixed $resource
	 *
	 * @return PatientScan
	 */
	public function setResource($resource)
	{
		$this->resource = $resource;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getScheduleDateStart()
	{
		return $this->schedule_date_start;
	}
	
	/**
	 * @param mixed $schedule_date_start
	 *
	 * @return PatientScan
	 */
	public function setScheduleDateStart($schedule_date_start)
	{
		$this->schedule_date_start = $schedule_date_start;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getScheduleDateEnd()
	{
		return $this->schedule_date_end;
	}
	
	/**
	 * @param mixed $schedule_date_end
	 *
	 * @return PatientScan
	 */
	public function setScheduleDateEnd($schedule_date_end)
	{
		$this->schedule_date_end = $schedule_date_end;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getScheduledOn()
	{
		return $this->scheduled_on;
	}
	
	/**
	 * @param mixed $scheduled_on
	 *
	 * @return PatientScan
	 */
	public function setScheduledOn($scheduled_on)
	{
		$this->scheduled_on = $scheduled_on;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getScheduledBy()
	{
		return $this->scheduled_by;
	}
	
	/**
	 * @param mixed $scheduled_by
	 *
	 * @return PatientScan
	 */
	public function setScheduledBy($scheduled_by)
	{
		$this->scheduled_by = $scheduled_by;
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
	 * @return PatientScan
	 */
	public function setBill($bill)
	{
		$this->bill = $bill;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAppointment()
	{
		return $this->appointment;
	}
	
	/**
	 * @param mixed $appointment
	 *
	 * @return PatientScan
	 */
	public function setAppointment($appointment)
	{
		$this->appointment = $appointment;
		return $this;
	}
	
	
	function update($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$approved = var_export($this->getApproved(), true);
		$approvedBy = $this->getApprovedBy() ? $this->getApprovedBy()->getId() : 'NULL';
		$approvedDate = $this->getApprovedDate() ? quote_esc_str($this->getApprovedDate()) : 'NULL';
		$dateLastModified = 'NOW()';
		$status = var_export($this->getStatus(), true);
		$cancelled = var_export($this->getCancelled(), true);
		$cancelDate = $this->getDateCanceled() ? quote_esc_str($this->getDateCanceled()) : 'null';
		$cancelBy = $this->getCanceledBy() ? $this->getCanceledBy()->getId() : 'null';
		$captured = var_export($this->getCaptured(), true);
		$capturedDate = $this->getCapturedDate() ? quote_esc_str($this->getCapturedDate()) : 'null';
		$capturedBy = $this->getCapturedBy() ? $this->getCapturedBy() : 'null';
		
		$resource = $this->getResource() ? $this->getResource()->getId() : 'null';
		$scheduleDateStart = $this->getScheduleDateStart() ? quote_esc_str($this->getScheduleDateStart()): 'NULL';
		$scheduleDateEnd = $this->getScheduleDateEnd() ? quote_esc_str($this->getScheduleDateEnd()): 'NULL';
		$scheduledON = $this->getScheduledOn() ? quote_esc_str($this->getScheduledOn()): 'NULL';
		$scheduledBy = $this->getScheduledBy() ? $this->getScheduledBy()->getId() : 'null';
		$appointment = $this->getAppointment() ?  $this->getAppointment()->getId() : 'NULL';
		
		$sql = "UPDATE patient_scan SET approved=$approved,approved_by_id=$approvedBy, approved_date=$approvedDate, date_last_modified=$dateLastModified,`status`=$status,cancelled=$cancelled,cancel_date=$cancelDate,canceled_by_id=$cancelBy,captured=$captured,captured_date=$capturedDate,captured_by_id=$capturedBy,resource_id=$resource,schedule_date_start=$scheduleDateStart,schedule_date_end=$scheduleDateEnd,scheduled_on=$scheduledON,scheduled_by_id=$scheduledBy,appointment_id=$appointment WHERE id={$this->getId()}";
		//error_log($sql);
		$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
} 