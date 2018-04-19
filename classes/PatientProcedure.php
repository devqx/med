<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/15/14
 * Time: 12:05 PM
 */
class PatientProcedure implements JsonSerializable
{
	private $id;
	private $patient;
	private $request_code;
	private $request_note;
	private $billed;
	private $billId; // new
	private $appointmentId;
	private $procedure;
	private $request_date;
	private $conditions;
	private $requested_by;
	private $status;
	private $theatre;
	
	private $has_anesthesiologist;
	private $has_surgeon;
	private $has_theatre;
	
	private $anesthesiologist;
	private $surgeon;

	private $resources;
	private $tasks;
	private $notes;
	private $attachments;

	private $regimens;
	private $reports;

	private $items;
	private $nursingServices;
	private $actionList;
	private $inPatient;

	private $referral;
	private $serviceCentre;
	private $bodyPart;

	private $source;
	private $sourceInstanceId;
	
	private $scheduledResources;
	private $time_start;
	private $time_stop;
	private $time_started;
	private $scheduled_on;
	private $scheduled_by;
	
	/**
	 * @return mixed
	 */
	public function getBodyPart()
	{
		return $this->bodyPart;
	}

	/**
	 * @param mixed $bodyPart
	 * @return PatientProcedure
	 */
	public function setBodyPart($bodyPart)
	{
		$this->bodyPart = $bodyPart;
		return $this;
	}

	function __construct($id = null, $pat = null)
	{
		$this->patient = $pat;
		$this->id = $id;
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
	 * @return PatientProcedure
	 */
	public function setServiceCentre($serviceCentre)
	{
		$this->serviceCentre = $serviceCentre;
		return $this;
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
	 * @return PatientProcedure
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return null
	 */
	public function getPatient()
	{
		return $this->patient;
	}

	/**
	 * @param null $patient
	 * @return PatientProcedure
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRequestCode()
	{
		return $this->request_code;
	}

	/**
	 * @param mixed $request_code
	 * @return PatientProcedure
	 */
	public function setRequestCode($request_code)
	{
		$this->request_code = $request_code;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getRequestNote()
	{
		return $this->request_note;
	}
	
	/**
	 * @param mixed $request_note
	 *
	 * @return PatientProcedure
	 */
	public function setRequestNote($request_note)
	{
		$this->request_note = $request_note;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getBilled()
	{
		return $this->billed;
	}
	
	/**
	 * @param mixed $billed
	 *
	 * @return PatientProcedure
	 */
	public function setBilled($billed)
	{
		$this->billed = $billed;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getProcedure()
	{
		return $this->procedure;
	}

	/**
	 * @param mixed $procedure
	 * @return PatientProcedure
	 */
	public function setProcedure($procedure)
	{
		$this->procedure = $procedure;
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
	 * @return PatientProcedure
	 */
	public function setRequestDate($request_date)
	{
		$this->request_date = $request_date;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getConditions()
	{
		return $this->conditions;
	}

	/**
	 * @param mixed $conditions
	 * @return PatientProcedure
	 */
	public function setConditions($conditions)
	{
		$this->conditions = $conditions;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTimeStart()
	{
		return $this->time_start;
	}

	/**
	 * @param mixed $time_start
	 * @return PatientProcedure
	 */
	public function setTimeStart($time_start)
	{
		$this->time_start = $time_start;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTimeStop()
	{
		return $this->time_stop;
	}

	/**
	 * @param mixed $time_stop
	 * @return PatientProcedure
	 */
	public function setTimeStop($time_stop)
	{
		$this->time_stop = $time_stop;
		return $this;
	}

    /**
     * @return mixed
     */
    public function getTimeStarted()
    {
        return $this->time_started;
    }

    /**
     * @param mixed $time_started
     * @return PatientProcedure
     */
    public function setTimeStarted($time_started)
    {
        $this->time_started = $time_started;
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
	 * @return PatientProcedure
	 */
	public function setRequestedBy($requested_by)
	{
		$this->requested_by = $requested_by;
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
	 * @return PatientProcedure
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTheatre()
	{
		return $this->theatre;
	}

	/**
	 * @param mixed $theatre
	 * @return PatientProcedure
	 */
	public function setTheatre($theatre)
	{
		$this->theatre = $theatre;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getHasAnesthesiologist()
	{
		return $this->has_anesthesiologist;
	}

	/**
	 * @param mixed $has_anesthesiologist
	 * @return PatientProcedure
	 */
	public function setHasAnesthesiologist($has_anesthesiologist)
	{
		$this->has_anesthesiologist = $has_anesthesiologist;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAnesthesiologist()
	{
		return $this->anesthesiologist;
	}

	/**
	 * @param mixed $anesthesiologist
	 * @return PatientProcedure
	 */
	public function setAnesthesiologist($anesthesiologist)
	{
		$this->anesthesiologist = $anesthesiologist;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getHasSurgeon()
	{
		return $this->has_surgeon;
	}

	/**
	 * @param mixed $has_surgeon
	 * @return PatientProcedure
	 */
	public function setHasSurgeon($has_surgeon)
	{
		$this->has_surgeon = $has_surgeon;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSurgeon()
	{
		return $this->surgeon;
	}

	/**
	 * @param mixed $surgeon
	 * @return PatientProcedure
	 */
	public function setSurgeon($surgeon)
	{
		$this->surgeon = $surgeon;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getResources()
	{
		return $this->resources;
	}

	/**
	 * @param mixed $resources
	 * @return PatientProcedure
	 */
	public function setResources($resources)
	{
		$this->resources = $resources;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTasks()
	{
		return $this->tasks;
	}

	/**
	 * @param mixed $tasks
	 * @return PatientProcedure
	 */
	public function setTasks($tasks)
	{
		$this->tasks = $tasks;
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
	 * @return PatientProcedure
	 */
	public function setNotes($notes)
	{
		$this->notes = $notes;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRegimens()
	{
		return $this->regimens;
	}

	/**
	 * @param mixed $regimens
	 * @return PatientProcedure
	 */
	public function setRegimens($regimens)
	{
		$this->regimens = $regimens;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getReports()
	{
		return $this->reports;
	}

	/**
	 * @param mixed $reports
	 * @return PatientProcedure
	 */
	public function setReports($reports)
	{
		$this->reports = $reports;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @param mixed $items
	 * @return PatientProcedure
	 */
	public function setItems($items)
	{
		$this->items = $items;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getNursingServices()
	{
		return $this->nursingServices;
	}

	/**
	 * @param mixed $nursingServices
	 * @return PatientProcedure
	 */
	public function setNursingServices($nursingServices)
	{
		$this->nursingServices = $nursingServices;
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
	 *
	 * @return PatientProcedure
	 */
	public function setAttachments($attachments)
	{
		$this->attachments = $attachments;
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
	 * @return PatientProcedure
	 */
	public function setInPatient($inPatient)
	{
		$this->inPatient = $inPatient;
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
	 * @return PatientProcedure
	 */
	public function setReferral($referral)
	{
		$this->referral = $referral;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getActionList()
	{
		return $this->actionList;
	}

	/**
	 * @param mixed $actionList
	 * @return PatientProcedure
	 */
	public function setActionList($actionList)
	{
		$this->actionList = $actionList;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * @param mixed $source
	 * @return PatientProcedure
	 */
	public function setSource($source)
	{
		$this->source = $source;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSourceInstanceId()
	{
		return $this->sourceInstanceId;
	}

	/**
	 * @param mixed $sourceInstanceId
	 * @return PatientProcedure
	 */
	public function setSourceInstanceId($sourceInstanceId)
	{
		$this->sourceInstanceId = $sourceInstanceId;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getScheduledResources()
	{
		return $this->scheduledResources;
	}
	
	/**
	 * @param mixed $scheduledResources
	 *
	 * @return PatientProcedure
	 */
	public function setScheduledResources($scheduledResources)
	{
		$this->scheduledResources = $scheduledResources;
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
	 * @return PatientProcedure
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
	 * @return PatientProcedure
	 */
	public function setScheduledBy($scheduled_by)
	{
		$this->scheduled_by = $scheduled_by;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getBillId()
	{
		return $this->billId;
	}
	
	/**
	 * @param mixed $billId
	 *
	 * @return PatientProcedure
	 */
	public function setBillId($billId)
	{
		$this->billId = $billId;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAppointmentId()
	{
		return $this->appointmentId;
	}
	
	/**
	 * @param mixed $appointmentId
	 *
	 * @return PatientProcedure
	 */
	public function setAppointmentId($appointmentId)
	{
		$this->appointmentId = $appointmentId;
		return $this;
	}
	
	
	
	

	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	
	
	function schedule($pdo=null){
		@session_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		
		$sRes = implode(",", $this->getScheduledResources());
		$timeStart = quote_esc_str($this->getTimeStart());
		$timeStop = quote_esc_str($this->getTimeStop());
		$dateScheduled = quote_esc_str($this->getScheduledOn());
		$scheduledOn = $this->getScheduledBy() ? $this->getScheduledBy()->getId() : $_SESSION['staffID'];
		
		$sql = "UPDATE patient_procedure SET `_status`='scheduled', scheduled_resource_ids='$sRes', time_start=$timeStart, time_stop=$timeStop, scheduled_on=$dateScheduled, scheduled_by=$scheduledOn WHERE id={$this->getId()}";
		
		$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	
	function update_($pdo=null){
		@session_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		
		$appoint_id = $this->getAppointmentId() ? $this->getAppointmentId() : NULL;
		
		$sql = "UPDATE patient_procedure SET appointment_id=$appoint_id WHERE id={$this->getId()}";
		
		$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	
} 