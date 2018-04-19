<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/7/16
 * Time: 1:13 PM
 */
class ProcedureActionList implements JsonSerializable
{
	private $id;
	private $patientProcedure;
	private $description;
	private $timeEntered;
	private $enteredBy;
	private $done;
	private $doneBy;
	private $doneOn;

	/**
	 * ProcedureActionList constructor.
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 * @return ProcedureActionList
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPatientProcedure()
	{
		return $this->patientProcedure;
	}

	/**
	 * @param mixed $patientProcedure
	 * @return ProcedureActionList
	 */
	public function setPatientProcedure($patientProcedure)
	{
		$this->patientProcedure = $patientProcedure;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param mixed $description
	 * @return ProcedureActionList
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTimeEntered()
	{
		return $this->timeEntered;
	}

	/**
	 * @param mixed $timeEntered
	 * @return ProcedureActionList
	 */
	public function setTimeEntered($timeEntered)
	{
		$this->timeEntered = $timeEntered;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEnteredBy()
	{
		return $this->enteredBy;
	}

	/**
	 * @param mixed $enteredBy
	 * @return ProcedureActionList
	 */
	public function setEnteredBy($enteredBy)
	{
		$this->enteredBy = $enteredBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDone()
	{
		return $this->done;
	}

	/**
	 * @param mixed $done
	 * @return ProcedureActionList
	 */
	public function setDone($done)
	{
		$this->done = $done;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDoneBy()
	{
		return $this->doneBy;
	}

	/**
	 * @param mixed $doneBy
	 * @return ProcedureActionList
	 */
	public function setDoneBy($doneBy)
	{
		$this->doneBy = $doneBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDoneOn()
	{
		return $this->doneOn;
	}

	/**
	 * @param mixed $doneOn
	 * @return ProcedureActionList
	 */
	public function setDoneOn($doneOn)
	{
		$this->doneOn = $doneOn;
		return $this;
	}

	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}

	function add($pdo = null)
	{
		try {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			$pdo = ($pdo === null) ? (new MyDBConnector())->getPDO() : $pdo;
			$procedure = $this->getPatientProcedure() ? $this->getPatientProcedure()->getId() : "NULL";
			$note = !is_blank($this->getDescription()) ? quote_esc_str($this->getDescription()) : "NULL";
			$time = $this->getTimeEntered() ? quote_esc_str($this->getTimeEntered()) : "NOW()";
			$by = $this->getEnteredBy() ? $this->getEnteredBy()->getId() : $_SESSION['staffID'];
			$done = var_export($this->getDone(), true);
			$doneOn = $this->getDoneOn() ? quote_esc_str($this->getDoneOn()) : "NULL";
			$doneBy = $this->getDoneBy() ? $this->getDoneBy()->getId() : "NULL";

			$sql = "INSERT INTO procedure_action_list (patient_procedure_id, note, time_entered, entered_by, done, done_by, done_on) VALUES ($procedure, $note, $time, $by, $done, $doneBy, $doneOn)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if($stmt->rowCount() == 1){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	function update($pdo = null)
	{
		try {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			$pdo = ($pdo === null) ? (new MyDBConnector())->getPDO() : $pdo;
			$procedure = $this->getPatientProcedure() ? $this->getPatientProcedure()->getId() : "NULL";
			$note = !is_blank($this->getDescription()) ? quote_esc_str($this->getDescription()) : "NULL";
			$done = var_export($this->getDone(), true);
			$doneOn = $this->getDoneOn() ? quote_esc_str($this->getDoneOn()) : "NULL";
			$doneBy = $this->getDoneBy() ? $this->getDoneBy()->getId() : "NULL";

			$sql = "UPDATE procedure_action_list SET patient_procedure_id=$procedure, note=$note, done=$done, done_by=$doneBy, done_on=$doneOn WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if($stmt->rowCount() == 1){
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