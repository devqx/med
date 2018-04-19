<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AntenatalEnrollment
 *
 * @author pauldic
 */
class AntenatalEnrollment implements JsonSerializable
{
	private $id;
	private $requestCode;
	private $active;
	private $patient;
	private $enrolledAt;
	private $enrolledOn;
	private $enrolledBy;
	private $bookingIndication;
	private $complicationNote;
	private $recommendation;
	private $obgyn;
	private $lmpDate;
	private $lmpSource;
	private $edDate;
	private $babyFatherName;
	private $babyFatherPhone;
	private $babyFatherBloodGroup;

	private $gravida;
	private $para;
	private $alive;
	private $abortions;

	private $package;


	private $dateClosed;
	private $closeNote;
	private $closedBy;

	private $notes;
	private $serviceCenter;

	function __construct($id = null)
	{
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * @param mixed $active
	 * @return AntenatalEnrollment
	 */
	public function setActive($active)
	{
		$this->active = $active;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCloseNote()
	{
		return $this->closeNote;
	}

	/**
	 * @param mixed $closeNote
	 * @return AntenatalEnrollment
	 */
	public function setCloseNote($closeNote)
	{
		$this->closeNote = $closeNote;
		return $this;
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
	 * @return AntenatalEnrollment
	 */
	public function setServiceCenter($serviceCenter)
	{
		$this->serviceCenter = $serviceCenter;
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
	 * @return AntenatalEnrollment
	 */
	public function setRequestCode($requestCode)
	{
		$this->requestCode = $requestCode;
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
	 * @return $this
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
	 * @return $this
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEnrolledAt()
	{
		return $this->enrolledAt;
	}

	/**
	 * @param mixed $enrolledAt
	 * @return $this
	 */
	public function setEnrolledAt($enrolledAt)
	{
		$this->enrolledAt = $enrolledAt;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEnrolledOn()
	{
		return $this->enrolledOn;
	}

	/**
	 * @param mixed $enrolledOn
	 * @return $this
	 */
	public function setEnrolledOn($enrolledOn)
	{
		$this->enrolledOn = $enrolledOn;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEnrolledBy()
	{
		return $this->enrolledBy;
	}

	/**
	 * @param mixed $enrolledBy
	 * @return $this
	 */
	public function setEnrolledBy($enrolledBy)
	{
		$this->enrolledBy = $enrolledBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBookingIndication()
	{
		return $this->bookingIndication;
	}

	/**
	 * @param mixed $bookingIndication
	 * @return $this
	 */
	public function setBookingIndication($bookingIndication)
	{
		$this->bookingIndication = $bookingIndication;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getComplicationNote()
	{
		return $this->complicationNote;
	}

	/**
	 * @param mixed $complicationNote
	 * @return $this
	 */
	public function setComplicationNote($complicationNote)
	{
		$this->complicationNote = $complicationNote;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getObgyn()
	{
		return $this->obgyn;
	}

	/**
	 * @param mixed $obgyn
	 * @return $this
	 */
	public function setObgyn($obgyn)
	{
		$this->obgyn = $obgyn;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLmpDate()
	{
		return $this->lmpDate;
	}

	/**
	 * @param mixed $lmpDate
	 * @return $this
	 */
	public function setLmpDate($lmpDate)
	{
		$this->lmpDate = $lmpDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLmpSource()
	{
		return $this->lmpSource;
	}

	/**
	 * @param mixed $lmpSource
	 * @return $this
	 */
	public function setLmpSource($lmpSource)
	{
		$this->lmpSource = $lmpSource;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEdDate()
	{
		return $this->edDate;
	}

	/**
	 * @param mixed $edDate
	 * @return $this
	 */
	public function setEdDate($edDate)
	{
		$this->edDate = $edDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBabyFatherName()
	{
		return $this->babyFatherName;
	}

	/**
	 * @param mixed $babyFatherName
	 * @return $this
	 */
	public function setBabyFatherName($babyFatherName)
	{
		$this->babyFatherName = $babyFatherName;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBabyFatherPhone()
	{
		return $this->babyFatherPhone;
	}

	/**
	 * @param mixed $babyFatherPhone
	 * @return $this
	 */
	public function setBabyFatherPhone($babyFatherPhone)
	{
		$this->babyFatherPhone = $babyFatherPhone;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBabyFatherBloodGroup()
	{
		return $this->babyFatherBloodGroup;
	}

	/**
	 * @param mixed $babyFatherBloodGroup
	 * @return AntenatalEnrollment
	 */
	public function setBabyFatherBloodGroup($babyFatherBloodGroup)
	{
		$this->babyFatherBloodGroup = $babyFatherBloodGroup;
		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getGravida()
	{
		return $this->gravida;
	}

	/**
	 * @param mixed $gravida
	 * @return $this
	 */
	public function setGravida($gravida)
	{
		$this->gravida = $gravida;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPara()
	{
		return $this->para;
	}

	/**
	 * @param mixed $para
	 * @return $this
	 */
	public function setPara($para)
	{
		$this->para = $para;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAlive()
	{
		return $this->alive;
	}

	/**
	 * @param mixed $alive
	 * @return $this
	 */
	public function setAlive($alive)
	{
		$this->alive = $alive;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAbortions()
	{
		return $this->abortions;
	}

	/**
	 * @param mixed $abortions
	 */
	public function setAbortions($abortions)
	{
		$this->abortions = $abortions;
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
	 * @return $this
	 */
	public function setNotes($notes)
	{
		$this->notes = $notes;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDateClosed()
	{
		return $this->dateClosed;
	}

	/**
	 * @param mixed $dateClosed
	 * @return $this
	 */
	public function setDateClosed($dateClosed)
	{
		$this->dateClosed = $dateClosed;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPackage()
	{
		return $this->package;
	}

	/**
	 * @param mixed $package
	 * @return $this
	 */
	public function setPackage($package)
	{
		$this->package = $package;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRecommendation()
	{
		return $this->recommendation;
	}

	/**
	 * @param mixed $recommendation
	 * @return AntenatalEnrollment
	 */
	public function setRecommendation($recommendation)
	{
		$this->recommendation = $recommendation;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getClosedBy()
	{
		return $this->closedBy;
	}
	
	/**
	 * @param mixed $closedBy
	 *
	 * @return AntenatalEnrollment
	 */
	public function setClosedBy($closedBy)
	{
		$this->closedBy = $closedBy;
		return $this;
	}

	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}

	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;

		$active = var_export(boolval($this->getActive()), true);
		$enrolledOn = $this->getEnrolledOn() ? quote_esc_str($this->getEnrolledOn()) : 'NULL';
		$bookingIndication = !is_blank($this->getBookingIndication()) ? quote_esc_str($this->getBookingIndication()) : 'NULL';
		$complicationNote = !is_blank($this->getComplicationNote()) ? quote_esc_str($this->getComplicationNote()) : 'NULL';
		$obgynId = $this->getObgyn() ? $this->getObgyn()->getId() : 'NULL';
		$lmpDate = !is_blank($this->getLmpDate()) ? quote_esc_str($this->getLmpDate()) : 'NULL';
		$lmpSource = !is_blank($this->getLmpSource()) ? quote_esc_str($this->getLmpSource()) : 'NULL';
		$edDate = !is_blank($this->getEdDate()) ? quote_esc_str($this->getEdDate()) : 'NULL';
		$babyFatherName = !is_blank($this->getBabyFatherName()) ? quote_esc_str($this->getBabyFatherName()) : 'NULL';
		$babyFatherPhone = !is_blank($this->getBabyFatherPhone()) ? quote_esc_str($this->getBabyFatherPhone()) : 'NULL';
		$babyFatherBloodGroup = !is_blank($this->getBabyFatherBloodGroup()) ? quote_esc_str($this->getBabyFatherBloodGroup()) : 'NULL';
		$gravida = !is_blank($this->getGravida()) ? ($this->getGravida()) : 'NULL';
		$para = !is_blank($this->getPara()) ? ($this->getPara()) : 'NULL';
		$alive = !is_blank($this->getAlive()) ? ($this->getAlive()) : 'NULL';
		$abortions = !is_blank($this->getAbortions()) ? ($this->getAbortions()) : 'NULL';
		$dateClosed = !is_blank($this->getDateClosed()) ? quote_esc_str($this->getDateClosed()) : 'NULL';
		$closeNote = !is_blank($this->getCloseNote()) ? quote_esc_str($this->getCloseNote()) : 'NULL';
		$closedBy = $this->getClosedBy() ? $this->getClosedBy()->getId() : 'NULL';
		$recommendations = !is_blank($this->getRecommendation()) ? quote_esc_str($this->getRecommendation()) : 'NULL';
		
		try {
			$sql = "UPDATE enrollments_antenatal SET active=$active, patient_id={$this->getPatient()->getId()}, enrolled_at={$this->getEnrolledAt()->getId()}, enrolled_on=$enrolledOn, enrolled_by={$this->getEnrolledBy()->getId()}, booking_indication=$bookingIndication, complication_note=$complicationNote, obgyn_id=$obgynId, lmp_date=$lmpDate, lmp_at_enrollment=lmp_at_enrollment, lmp_source=$lmpSource, ed_date=$edDate, baby_father_name=$babyFatherName, baby_father_phone=$babyFatherPhone, baby_father_blood_group=$babyFatherBloodGroup, gravida=$gravida, para=$para, alive=$alive, abortions=$abortions, date_closed=$dateClosed, close_note=$closeNote, recommendation=$recommendations, closed_by=$closedBy WHERE id={$this->getId()}";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

}
