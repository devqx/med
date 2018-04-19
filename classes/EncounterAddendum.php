<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/23/16
 * Time: 11:53 AM
 */
class EncounterAddendum implements JsonSerializable
{
	private $id;
	private $encounter;
	private $date;
	private $user;
	private $note;

	/**
	 * EncounterAddendum constructor.
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
	 * @return EncounterAddendum
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 * @return EncounterAddendum
	 */
	public function setEncounter($encounter)
	{
		$this->encounter = $encounter;
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
	 * @return EncounterAddendum
	 */
	public function setDate($date)
	{
		$this->date = $date;
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
	 * @return EncounterAddendum
	 */
	public function setUser($user)
	{
		$this->user = $user;
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
	 * @return EncounterAddendum
	 */
	public function setNote($note)
	{
		$this->note = $note;
		return $this;
	}

	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT']. '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT']. '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO():$pdo;
			$encounter = $this->getEncounter() ? $this->getEncounter()->getId() : "NULL";
			$date = $this->getDate() ? quote_esc_str($this->getDate()) : "NULL";
			$userId = $this->getUser() ? $this->getUser()->getId() : $_SESSION['staffID'];
			$note = !is_blank($this->getNote()) ? quote_esc_str($this->getNote()) : "NULL";
			$sql = "INSERT INTO encounter_addendum (encounter_id, `date`, user_id, note) VALUES ($encounter, $date, $userId, $note)";
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
	function update($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT']. '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT']. '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO():$pdo;
			$encounter = $this->getEncounter() ? $this->getEncounter()->getId() : "NULL";
			$date = $this->getDate() ? quote_esc_str($this->getDate()) : "NULL";
			$userId = $this->getUser() ? $this->getUser()->getId() : $_SESSION['staffID'];
			$note = !is_blank($this->getNote()) ? quote_esc_str($this->getNote()) : "NULL";
			$sql = "UPDATE encounter_addendum SET encounter_id=$encounter, `date`=$date, user_id=$userId, note=$note WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
}