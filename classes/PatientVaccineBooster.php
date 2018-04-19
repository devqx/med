<?php

/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 4/2/15
 * Time: 9:00 AM
 */
class PatientVaccineBooster implements JsonSerializable
{
	private $id;
	private $patient;
	private $vaccine_booster;
	private $start_date;
	private $next_due_date;
	private $last_taken;
	private $charged;

	/**
	 * PatientVaccineBooster constructor.
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }

	/**
	 * @return null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param null $id
	 * @return PatientVaccineBooster
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
	 * @return PatientVaccineBooster
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getVaccineBooster()
	{
		return $this->vaccine_booster;
	}

	/**
	 * @param mixed $vaccine_booster
	 * @return PatientVaccineBooster
	 */
	public function setVaccineBooster($vaccine_booster)
	{
		$this->vaccine_booster = $vaccine_booster;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getStartDate()
	{
		return $this->start_date;
	}

	/**
	 * @param mixed $start_date
	 * @return PatientVaccineBooster
	 */
	public function setStartDate($start_date)
	{
		$this->start_date = $start_date;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getNextDueDate()
	{
		return $this->next_due_date;
	}

	/**
	 * @param mixed $next_due_date
	 * @return PatientVaccineBooster
	 */
	public function setNextDueDate($next_due_date)
	{
		$this->next_due_date = $next_due_date;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLastTaken()
	{
		return $this->last_taken;
	}

	/**
	 * @param mixed $last_taken
	 * @return PatientVaccineBooster
	 */
	public function setLastTaken($last_taken)
	{
		$this->last_taken = $last_taken;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCharged()
	{
		return $this->charged;
	}

	/**
	 * @param mixed $charged
	 * @return PatientVaccineBooster
	 */
	public function setCharged($charged)
	{
		$this->charged = $charged;
		return $this;
	}

	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$charged = var_export($this->getCharged(), true);
		$lastTaken = $this->getLastTaken() ? quote_esc_str($this->getLastTaken()) : "NULL";
		$nextDueDate = $this->getNextDueDate() ? quote_esc_str($this->getNextDueDate()) : "NULL";
		$startDate = $this->getStartDate() ? quote_esc_str($this->getStartDate()) : "NULL";
		$vaccineBooster = $this->getVaccineBooster() ? $this->getVaccineBooster()->getId() : "NULL" ;
		$patient = $this->getPatient() ? $this->getPatient()->getId() : "NULL";
		$sql = "UPDATE patient_vaccine_booster SET charged = $charged, last_taken=$lastTaken, next_due_date=$nextDueDate, start_date=$startDate, vaccinebooster_id=$vaccineBooster, patient_id=$patient WHERE id={$this->getId()}";
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() >= 0){
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
}