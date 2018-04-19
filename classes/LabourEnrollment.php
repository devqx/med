<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/15/15
 * Time: 11:23 AM
 */
class LabourEnrollment implements JsonSerializable
{
	private $id;
	private $active;
	private $patient;
	private $enrolledAt;
	private $enrolledOn;
	private $enrolledBy;
	private $dateClosed;

	private $lmpDate;
	private $babyFatherName;
	private $babyFatherPhone;
	private $babyFatherBloodGroup;
	private $gravida;
	private $para;
	private $alive;
	private $abortions;
	private $currentPregnancy;

	/**
	 * LabourEnrollment constructor.
	 * @param $id
	 */
	public function __construct($id = null)
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
	 * @return LabourEnrollment
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
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
	 * @return LabourEnrollment
	 */
	public function setActive($active)
	{
		$this->active = $active;
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
	 * @return LabourEnrollment
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
	 * @return LabourEnrollment
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
	 * @return LabourEnrollment
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
	 * @return LabourEnrollment
	 */
	public function setEnrolledBy($enrolledBy)
	{
		$this->enrolledBy = $enrolledBy;
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
	 * @return LabourEnrollment
	 */
	public function setDateClosed($dateClosed)
	{
		$this->dateClosed = $dateClosed;
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
	 * @return LabourEnrollment
	 */
	public function setLmpDate($lmpDate)
	{
		$this->lmpDate = $lmpDate;
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
	 * @return LabourEnrollment
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
	 * @return LabourEnrollment
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
	 * @return LabourEnrollment
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
	 * @return LabourEnrollment
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
	 * @return LabourEnrollment
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
	 * @return LabourEnrollment
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
	 * @return LabourEnrollment
	 */
	public function setAbortions($abortions)
	{
		$this->abortions = $abortions;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCurrentPregnancy()
	{
		return $this->currentPregnancy;
	}

	/**
	 * @param mixed $currentPregnancy
	 * @return LabourEnrollment
	 */
	public function setCurrentPregnancy($currentPregnancy)
	{
		$this->currentPregnancy = $currentPregnancy;
		return $this;
	}


	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}

	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		try {
			$patient_id = $this->getPatient()->getId();
			$enrolled_at = $this->getEnrolledAt() ? $this->getEnrolledAt()->getId() : "1";
			$enrolled_on = $this->getEnrolledOn() ? quote_esc_str($this->getEnrolledOn()) : "NOW()";
			$enrolled_by = $this->getEnrolledBy() ? $this->getEnrolledBy()->getId() : $_SESSION['staffID'];
			$dateClosed = $this->getDateClosed() ? quote_esc_str($this->getDateClosed()) : "NULL";
			$lmp = $this->getLmpDate() ? quote_esc_str($this->getLmpDate()) : "NULL";
			$babyFatherName = $this->getBabyFatherName() ? quote_esc_str($this->getBabyFatherName()) : "NULL";
			$babyFatherPhone = $this->getBabyFatherPhone() ? quote_esc_str($this->getBabyFatherPhone()) : "NULL";
			$babyFatherBlood = $this->getBabyFatherBloodGroup() ? quote_esc_str($this->getBabyFatherBloodGroup()) : "NULL";
			$gravida = $this->getGravida() ? quote_esc_str($this->getGravida()) : "NULL";
			$para = $this->getPara() ? quote_esc_str($this->getPara()) : "NULL";
			$alive = $this->getAlive() ? quote_esc_str($this->getAlive()) : "NULL";
			$abortions = $this->getAbortions() ? quote_esc_str($this->getAbortions()) : "NULL";
			$currentPregnancy = $this->getCurrentPregnancy() ? quote_esc_str($this->getCurrentPregnancy()) : "NULL";

			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "INSERT IGNORE INTO enrollments_labour (patient_id, enrolled_at, enrolled_on, enrolled_by, date_closed, lmpDate, baby_father_name, baby_father_phone, baby_father_blood_group, gravida, para, alive, abortions, current_pregnancy) VALUES ($patient_id, $enrolled_at, $enrolled_on, $enrolled_by, $dateClosed, $lmp, $babyFatherName, $babyFatherPhone, $babyFatherBlood, $gravida, $para, $alive, $abortions, $currentPregnancy)";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				return $this;
			} else if ($stmt->rowCount() == 0) {
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

}