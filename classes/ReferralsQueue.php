<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/7/16
 * Time: 8:34 AM
 */
class ReferralsQueue implements JsonSerializable
{
	private $id;
	private $patient;
	private $doctor;
	private $when;
	private $acknowledged;
	private $note;
	private $external;
	private $specialization;

	/**
	 * ReferralsQueue constructor.
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
	 * @return ReferralsQueue
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
	 * @return ReferralsQueue
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDoctor()
	{
		return $this->doctor;
	}

	/**
	 * @param mixed $doctor
	 * @return ReferralsQueue
	 */
	public function setDoctor($doctor)
	{
		$this->doctor = $doctor;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getWhen()
	{
		return $this->when;
	}

	/**
	 * @param mixed $when
	 * @return ReferralsQueue
	 */
	public function setWhen($when)
	{
		$this->when = $when;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAcknowledged()
	{
		return $this->acknowledged;
	}

	/**
	 * @param mixed $acknowledged
	 * @return ReferralsQueue
	 */
	public function setAcknowledged($acknowledged)
	{
		$this->acknowledged = $acknowledged;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getNote()
	{
		return $this->note;
	}

	/**
	 * @param mixed $note
	 * @return ReferralsQueue
	 */
	public function setNote($note)
	{
		$this->note = $note;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getExternal()
	{
		return $this->external;
	}

	/**
	 * @param mixed $external
	 * @return ReferralsQueue
	 */
	public function setExternal($external)
	{
		$this->external = $external;
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
	 * @return ReferralsQueue
	 */
	public function setSpecialization($specialization)
	{
		$this->specialization = $specialization;
		return $this;
	}


	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}


	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$patient_id = $this->getPatient() ? $this->getPatient()->getId() : "NULL";
		$doctor_id = $this->getDoctor() ? $this->getDoctor()->getId() : "NULL";
		$datetime = !is_blank($this->getWhen()) ? quote_esc_str($this->getWhen()) : "NOW()";
		$acknowledged = var_export($this->getAcknowledged(), true);
		$external =  var_export($this->getExternal(), true);
		$specialization = $this->getSpecialization() ? $this->getSpecialization()->getId() : "NULL";
		$note = quote_esc_str($this->getNote());

		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "INSERT INTO referrals_queue (patient_id, doctor_id, `datetime`, acknowledged, note, external, specialization_id) VALUES ($patient_id, $doctor_id, $datetime, $acknowledged, $note, $external, $specialization)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$patient_id = $this->getPatient() ? $this->getPatient()->getId() : "NULL";
		$doctor_id = $this->getDoctor() ? $this->getDoctor()->getId() : "NULL";
		$datetime = !is_blank($this->getWhen()) ? quote_esc_str($this->getWhen()) : "NOW()";
		$acknowledged = var_export($this->getAcknowledged(), true);
		$external =  var_export($this->getExternal(), true);
		$specialization = $this->getSpecialization() ? $this->getSpecialization()->getId() : "NULL";
		$note = quote_esc_str($this->getNote());

		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "UPDATE referrals_queue SET patient_id=$patient_id, doctor_id=$doctor_id, `datetime`=$datetime, acknowledged=$acknowledged, note=$note, external=$external, specialization_id='". $specialization ."' WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}