<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/26/16
 * Time: 6:54 PM
 */
class Encounter implements JsonSerializable
{
	private $id;
	private $startDate;
	private $initiator;
	private $department;
	private $patient;
	private $specialization;
	private $status;
	private $followUp;
	private $open;
	private $canceled;
	private $scheme;

	private $labs;
	private $notes;
	private $scans;
	private $prescriptions;
	private $procedures;
	private $diagnoses;
	private $medicalHistory;
	private $drugHistory;
	private $systems_reviews;
	private $bills;
	private $examinations;
	private $examNotes;
	private $addenda;

	private $presentingComplaints;
	private $plan;
	private $investigations;
	private $socialHistory;
	private $allergies;
	private $documents;

	private $claimed;

	private $signedBy;
	private $signedOn;
	private $triaged_by;
	private $triaged_on;
	
	private $bill;
	private $referrer;
	
	public static $useLight = true;
	public static $requireSpecialty = true;

	/**
	 * Encounter constructor.
	 * @param $id
	 */
	public function __construct($id = null)
	{
		$this->id = $id;
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
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
	 * @return Encounter
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getStartDate()
	{
		return $this->startDate;
	}

	/**
	 * @param mixed $startDate
	 * @return Encounter
	 */
	public function setStartDate($startDate)
	{
		$this->startDate = $startDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getInitiator()
	{
		return $this->initiator;
	}

	/**
	 * @param mixed $initiator
	 * @return Encounter
	 */
	public function setInitiator($initiator)
	{
		$this->initiator = $initiator;
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
	 * @return Encounter
	 */
	public function setDepartment($department)
	{
		$this->department = $department;
		return $this;
	}

	//get the scheme id

    public  function getScheme(){
	    return $this->scheme;
    }


    public function setScheme($scheme){

        $this->scheme = $scheme;

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
	 * @return Encounter
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
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
	 * @return Encounter
	 */
	public function setSpecialization($specialization)
	{
		$this->specialization = $specialization;
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
	 * @return Encounter
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
	 * @return Encounter
	 */
	public function setFollowUp($followUp)
	{
		$this->followUp = $followUp;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLabs()
	{
		return $this->labs;
	}

	/**
	 * @param mixed $labs
	 * @return Encounter
	 */
	public function setLabs($labs)
	{
		$this->labs = $labs;
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
	 * @return Encounter
	 */
	public function setNotes($notes)
	{
		$this->notes = $notes;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getScans()
	{
		return $this->scans;
	}

	/**
	 * @param mixed $scans
	 * @return Encounter
	 */
	public function setScans($scans)
	{
		$this->scans = $scans;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPrescriptions()
	{
		return $this->prescriptions;
	}

	/**
	 * @param mixed $prescriptions
	 * @return Encounter
	 */
	public function setPrescriptions($prescriptions)
	{
		$this->prescriptions = $prescriptions;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getProcedures()
	{
		return $this->procedures;
	}

	/**
	 * @param mixed $procedures
	 * @return Encounter
	 */
	public function setProcedures($procedures)
	{
		$this->procedures = $procedures;
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
	 * @return Encounter
	 */
	public function setDiagnoses($diagnoses)
	{
		$this->diagnoses = $diagnoses;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMedicalHistory()
	{
		return $this->medicalHistory;
	}

	/**
	 * @param mixed $medicalHistory
	 * @return Encounter
	 */
	public function setMedicalHistory($medicalHistory)
	{
		$this->medicalHistory = $medicalHistory;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSystemsReviews()
	{
		return $this->systems_reviews;
	}

	/**
	 * @param mixed $systems_reviews
	 * @return Encounter
	 */
	public function setSystemsReviews($systems_reviews)
	{
		$this->systems_reviews = $systems_reviews;
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
	 * @return Encounter
	 */
	public function setBills($bills)
	{
		$this->bills = $bills;
		return $this;
	}

	function __toString()
	{
		// Implement __toString() method.
		return date(MainConfig::$dateTimeFormat, strtotime($this->getStartDate())) . ": " . ($this->getSpecialization() ? $this->getSpecialization()->getName(): 'No Specialty');
	}
	
	/**
	 * @return mixed
	 */
	public function getCanceled()
	{
		return $this->canceled;
	}
	
	/**
	 * @param mixed $canceled
	 *
	 * @return Encounter
	 */
	public function setCanceled($canceled)
	{
		$this->canceled = $canceled;
		return $this;
	}
	

	/**
	 * @return mixed
	 */
	public function getPresentingComplaints()
	{
		return $this->presentingComplaints;
	}

	/**
	 * @param mixed $presentingComplaints
	 * @return Encounter
	 */
	public function setPresentingComplaints($presentingComplaints)
	{
		$this->presentingComplaints = $presentingComplaints;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPlan()
	{
		return $this->plan;
	}

	/**
	 * @param mixed $plan
	 * @return Encounter
	 */
	public function setPlan($plan)
	{
		$this->plan = $plan;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getInvestigations()
	{
		return $this->investigations;
	}

	/**
	 * @param mixed $investigations
	 * @return Encounter
	 */
	public function setInvestigations($investigations)
	{
		$this->investigations = $investigations;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getSocialHistory()
	{
		return $this->socialHistory;
	}
	
	/**
	 * @param mixed $socialHistory
	 *
	 * @return Encounter
	 */
	public function setSocialHistory($socialHistory)
	{
		$this->socialHistory = $socialHistory;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAllergies()
	{
		return $this->allergies;
	}
	
	/**
	 * @param mixed $allergies
	 *
	 * @return Encounter
	 */
	public function setAllergies($allergies)
	{
		$this->allergies = $allergies;
		return $this;
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
	 * @return Encounter
	 */
	public function setClaimed($claimed)
	{
		$this->claimed = $claimed;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getOpen()
	{
		return $this->open;
	}

	/**
	 * @param mixed $open
	 * @return Encounter
	 */
	public function setOpen($open)
	{
		$this->open = $open;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSignedBy()
	{
		return $this->signedBy;
	}

	/**
	 * @param mixed $signedBy
	 * @return Encounter
	 */
	public function setSignedBy($signedBy)
	{
		$this->signedBy = $signedBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSignedOn()
	{
		return $this->signedOn;
	}

	/**
	 * @return mixed
	 */
	public function getExaminations()
	{
		return $this->examinations;
	}

	/**
	 * @param mixed $examinations
	 * @return Encounter
	 */
	public function setExaminations($examinations)
	{
		$this->examinations = $examinations;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getExamNotes()
	{
		return $this->examNotes;
	}

	/**
	 * @param mixed $examNotes
	 * @return Encounter
	 */
	public function setExamNotes($examNotes)
	{
		$this->examNotes = $examNotes;
		return $this;
	}

	/**
	 * @param mixed $signedOn
	 * @return Encounter
	 */
	public function setSignedOn($signedOn)
	{
		$this->signedOn = $signedOn;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAddenda()
	{
		return $this->addenda;
	}

	/**
	 * @param mixed $addenda
	 * @return Encounter
	 */
	public function setAddenda($addenda)
	{
		$this->addenda = $addenda;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDrugHistory()
	{
		return $this->drugHistory;
	}

	/**
	 * @param mixed $drugHistory
	 * @return Encounter
	 */
	public function setDrugHistory($drugHistory)
	{
		$this->drugHistory = $drugHistory;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTriagedBy()
	{
		return $this->triaged_by;
	}

	/**
	 * @param mixed $triaged_by
	 * @return Encounter
	 */
	public function setTriagedBy($triaged_by)
	{
		$this->triaged_by = $triaged_by;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTriagedOn()
	{
		return $this->triaged_on;
	}

	/**
	 * @param mixed $triaged_on
	 * @return Encounter
	 */
	public function setTriagedOn($triaged_on)
	{
		$this->triaged_on = $triaged_on;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDocuments()
	{
		return $this->documents;
	}
	
	/**
	 * @param mixed $documents
	 *
	 * @return Encounter
	 */
	public function setDocuments($documents)
	{
		$this->documents = $documents;
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
	 * @return Encounter
	 */
	public function setBill($bill)
	{
		$this->bill = $bill;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getReferrer()
	{
		return $this->referrer;
	}
	
	/**
	 * @param mixed $referrer
	 *
	 * @return Encounter
	 */
	public function setReferrer($referrer)
	{
		$this->referrer = $referrer;
		return $this;
	}
	

	public function update($pdo = null)
	{
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$billIds = [];
			if(is_array($this->getBill())){
				foreach ($this->getBill() as $b){
					$billIds[] =  $b ?  $b->getId() : ''  ;
				}
			}
			
			$billId = $this->getBill() ? ( is_array($this->getBill()) ? "'". implode(", ", $billIds) ."'": $this->getBill()->getId() ): "NULL";
			
			$sql = "UPDATE `encounter` SET start_date='" . $this->getStartDate() . "', initiator_id=" . ($this->getInitiator() ? $this->getInitiator()->getId() : "NULL") . ", department_id=" . ($this->getDepartment() ? $this->getDepartment()->getId() : "NULL") . ", `patient_id`={$this->getPatient()->getId()}, specialization_id=" . ($this->getSpecialization() ? $this->getSpecialization()->getId() : "NULL") . ", `open`=" . var_export((bool)$this->getOpen(), true) . ", `canceled`=" . var_export((bool)$this->getCanceled(), true) . ", follow_up=" . var_export((bool)$this->getFollowUp(), true) . ", `claimed`=" . var_export((bool)$this->getClaimed(), true) . ", signed_by=" . ($this->getSignedBy() != null ? $this->getSignedBy()->getId() : "NULL") . ", signed_on=" . ($this->getSignedOn() ? "'" . $this->getSignedOn() . "'" : "NULL") . ", bill_line_id=$billId WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}
