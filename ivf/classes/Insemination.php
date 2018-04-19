<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/11/16
 * Time: 4:15 PM
 */
class Insemination implements JsonSerializable
{
	private $id;
	private $user;
	private $timeEntered;
	private $instance;
	private $method;
	private $source;
	private $totalEggs;
	private $totalSperm;
	private $comment;
	private $witnesses;

	/**
	 * Insemination constructor.
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
	 * @return Insemination
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 * @return Insemination
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
		return $this->timeEntered;
	}

	/**
	 * @param mixed $timeEntered
	 * @return Insemination
	 */
	public function setTimeEntered($timeEntered)
	{
		$this->timeEntered = $timeEntered;
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
	 * @return Insemination
	 */
	public function setInstance($instance)
	{
		$this->instance = $instance;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @param mixed $method
	 * @return Insemination
	 */
	public function setMethod($method)
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * @param mixed $source
	 * @return Insemination
	 */
	public function setSource($source)
	{
		$this->source = $source;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTotalEggs()
	{
		return $this->totalEggs;
	}

	/**
	 * @param mixed $totalEggs
	 * @return Insemination
	 */
	public function setTotalEggs($totalEggs)
	{
		$this->totalEggs = $totalEggs;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTotalSperm()
	{
		return $this->totalSperm;
	}

	/**
	 * @param mixed $totalSperm
	 * @return Insemination
	 */
	public function setTotalSperm($totalSperm)
	{
		$this->totalSperm = $totalSperm;
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
	 * @return Insemination
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getWitnesses()
	{
		return $this->witnesses;
	}

	/**
	 * @param mixed $witnesses
	 * @return Insemination
	 */
	public function setWitnesses($witnesses)
	{
		$this->witnesses = $witnesses;
		return $this;
	}

	function add($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
		try {
			$user = $this->getUser() ? $this->getUser()->getId() : 'null';
			$timeEntered = $this->getTimeEntered() ? quote_esc_str($this->getTimeEntered()) : 'now()';
			$instance = $this->getInstance() ? $this->getInstance()->getId() : 'null';
			$method = $this->getMethod() ? $this->getMethod()->getId() : 'null';
			$source = $this->getSource() ? $this->getSource()->getId() : 'null';
			$totalEggs = $this->getTotalEggs() ? $this->getTotalEggs() : 0;
			$totalSperm = $this->getTotalSperm() ? $this->getTotalSperm() : 0;
			$comment = !is_blank($this->getComment()) ? quote_esc_str($this->getComment()) : 'null';
			$witnesses = count($this->getWitnesses()) > 0 ? quote_esc_str(implode(',', $this->getWitnesses())) : 'NULL';
			$sql = "INSERT INTO ivf_insemination (user_id, instance_id, time_entered, method_id, source_id, total_eggs, total_sperm, `comment`, witness_ids) VALUES ($user, $instance, $timeEntered, $method, $source, $totalEggs, $totalSperm, $comment, $witnesses)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()){
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