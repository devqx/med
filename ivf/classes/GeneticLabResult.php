<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/17/16
 * Time: 4:23 PM
 */
class GeneticLabResult implements JsonSerializable
{
	private $id;
	private $request;
	private $note;
	private $user;
	private $time_entered;

	/**
	 * GeneticLabResult constructor.
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
	 * @return GeneticLabResult
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @param mixed $request
	 * @return GeneticLabResult
	 */
	public function setRequest($request)
	{
		$this->request = $request;
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
	 * @return GeneticLabResult
	 */
	public function setNote($note)
	{
		$this->note = $note;
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
	 * @return GeneticLabResult
	 */
	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTimeEntered()
	{
		return $this->time_entered;
	}

	/**
	 * @param mixed $time_entered
	 * @return GeneticLabResult
	 */
	public function setTimeEntered($time_entered)
	{
		$this->time_entered = $time_entered;
		return $this;
	}

	function add($pdo = null)
	{
		try {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$genetic_lab_request_id = $this->getRequest() ? $this->getRequest()->getId() : "NULL";
			$note = $this->getNote() ? quote_esc_str($this->getNote()) : "NULL";
			$user_id = $this->getUser() ? $this->getUser()->getId() : $_SESSION['staffID'];
			$time_entered = $this->getTimeEntered() ? quote_esc_str($this->getTimeEntered()) : "NOW()";

			$sql = "INSERT INTO genetic_lab_result (genetic_lab_request_id, note, user_id, time_entered) VALUES ($genetic_lab_request_id, $note, $user_id, $time_entered)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function update($pdo = null)
	{
		try {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$genetic_lab_request_id = $this->getRequest() ? $this->getRequest()->getId() : "NULL";
			$note = $this->getNote() ? quote_esc_str($this->getNote()) : "NULL";
			$user_id = $this->getUser() ? $this->getUser()->getId() : $_SESSION['staffID'];
			$time_entered = $this->getTimeEntered() ? quote_esc_str($this->getTimeEntered()) : "NOW()";

			$sql = "UPDATE genetic_lab_result SET genetic_lab_request_id=$genetic_lab_request_id, note=$note, user_id=$user_id, time_entered=$time_entered WHERE id={$this->getId()}";
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
}