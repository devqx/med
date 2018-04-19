<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/18/16
 * Time: 3:54 PM
 */
class IVFTreatment implements JsonSerializable
{
	private $id;
	private $enrolment;
	private $date;
	private $dayOfCycle;
	private $drug;
	private $value;
	//private $buserelin;
	//private $guserelin;
	private $findings;
	private $comment;
	private $user;
	private $duration;

	/**
	 * IVFTreatment constructor.
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }

	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
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
	 * @return IVFTreatment
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEnrolment()
	{
		return $this->enrolment;
	}

	/**
	 * @param mixed $enrolment
	 * @return IVFTreatment
	 */
	public function setEnrolment($enrolment)
	{
		$this->enrolment = $enrolment;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @param mixed $date
	 * @return IVFTreatment
	 */
	public function setDate($date)
	{
		$this->date = $date;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDayOfCycle()
	{
		return $this->dayOfCycle;
	}

	/**
	 * @param mixed $dayOfCycle
	 * @return IVFTreatment
	 */
	public function setDayOfCycle($dayOfCycle)
	{
		$this->dayOfCycle = $dayOfCycle;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDrug()
	{
		return $this->drug;
	}
	
	/**
	 * @param mixed $drug
	 *
	 * @return IVFTreatment
	 */
	public function setDrug($drug)
	{
		$this->drug = $drug;
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
	 * @return IVFTreatment
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getFindings()
	{
		return $this->findings;
	}

	/**
	 * @param mixed $findings
	 * @return IVFTreatment
	 */
	public function setFindings($findings)
	{
		$this->findings = $findings;
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
	 * @return IVFTreatment
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param mixed $user
	 * @return IVFTreatment
	 */
	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDuration()
	{
		return $this->duration;
	}
	
	/**
	 * @param mixed $duration
	 *
	 * @return IVFTreatment
	 */
	public function setDuration($duration)
	{
		$this->duration = $duration;
		return $this;
	}
	

	function add($pdo=null){
		@session_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
			$enrolment = $this->getEnrolment() ? $this->getEnrolment()->getId() : 'NULL';
			$date = $this->getDate() ? quote_esc_str($this->getDate()) : 'NOW()';
			$day = $this->getDayOfCycle() ? $this->getDayOfCycle() : 'NULL';
			$drug = $this->getDrug() ? $this->getDrug()->getId() : 'NULL';
			$value = $this->getValue() ? quote_esc_str($this->getValue()) : 'NULL';
			$duration = $this->getDuration() ? quote_esc_str($this->getDuration()) : 'NULL';
			$findings = $this->getFindings() ? quote_esc_str($this->getFindings()) : 'NULL';
			$comment = $this->getComment() ? quote_esc_str($this->getComment()) : 'NULL';
			$user = $this->getUser() ? $this->getUser()->getId() : $_SESSION['staffID'];
			$sql = "INSERT INTO ivf_treatment (enrollment_id, `date`, day_of_cycle, drug_id, `value`, findings, duration, `comment`, user_id) VALUES ($enrolment, $date, $day, $drug, $value, $findings, $duration, $comment, $user)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}

	}
}