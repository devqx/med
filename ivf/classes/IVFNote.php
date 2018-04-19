<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/20/16
 * Time: 2:03 PM
 */
class IVFNote implements JsonSerializable
{
	private $id;
	private $note;
	private $date;
	private $user;
	private $instance;

	/**
	 * IVFNote constructor.
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
	 * @return IVFNote
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 * @return IVFNote
	 */
	public function setNote($note)
	{
		$this->note = $note;
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
	 * @return IVFNote
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
	 * @return IVFNote
	 */
	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getInstance()
	{
		return $this->instance;
	}

	/**
	 * @param mixed $instance
	 * @return IVFNote
	 */
	public function setInstance($instance)
	{
		$this->instance = $instance;
		return $this;
	}

	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}

	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$instance = $this->getInstance() ? $this->getInstance()->getId() : 'NULL';
		$note = !is_blank($this->getNote()) ? quote_esc_str($this->getNote()) : 'NULL';
		$date = $this->getDate() ? quote_esc_str($this->getDate()) : 'NOW()';
		$user = $this->getUser() ? $this->getUser()->getId() : $_SESSION['staffID'];
		try {
			$pdo = $pdo===null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "INSERT INTO ivf_note (instance_id, note, `date`, user_id) VALUES ($instance, $note, $date, $user)";
			error_log($sql);
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