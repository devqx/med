<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/12/16
 * Time: 2:30 PM
 */
class Fertilization implements JsonSerializable
{
	private $id;
	private $instance;
	private $user;
	private $timeEntered;
	private $method;
	private $zygoteType;
	private $cellNo;
	private $witnesses;
	private $comment;

	/**
	 * Fertilization constructor.
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
	 * @return Fertilization
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 * @return Fertilization
	 */
	public function setInstance($instance)
	{
		$this->instance = $instance;
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
	 * @return Fertilization
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
	 * @return Fertilization
	 */
	public function setTimeEntered($timeEntered)
	{
		$this->timeEntered = $timeEntered;
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
	 * @return Fertilization
	 */
	public function setMethod($method)
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getZygoteType()
	{
		return $this->zygoteType;
	}

	/**
	 * @param mixed $zygoteType
	 * @return Fertilization
	 */
	public function setZygoteType($zygoteType)
	{
		$this->zygoteType = $zygoteType;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCellNo()
	{
		return $this->cellNo;
	}

	/**
	 * @param mixed $cellNo
	 * @return Fertilization
	 */
	public function setCellNo($cellNo)
	{
		$this->cellNo = $cellNo;
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
	 * @return Fertilization
	 */
	public function setWitnesses($witnesses)
	{
		$this->witnesses = $witnesses;
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
	 * @return Fertilization
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
		return $this;
	}


	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}

	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$instance = $this->getInstance() ? $this->getInstance()->getId() : 'null';
		$user = $this->getUser() ? $this->getUser()->getId() : 'null';
		$method = $this->getMethod() ? $this->getMethod()->getId() : 'null';
		$zygoteType = !is_blank($this->getZygoteType()) ? quote_esc_str($this->getZygoteType()) : 'null';
		$cellNo = !is_blank($this->getCellNo()) ? $this->getCellNo() : 0;
		$witnesses = count($this->getWitnesses()) > 0 ? quote_esc_str(implode(',', $this->getWitnesses())) : 'NULL';
		$comment = !is_blank($this->getComment()) ? quote_esc_str($this->getComment()) : 'null';

		try {
			$sql = "INSERT INTO ivf_fertilization (instance_id, user_id, method_id, zygote_type, cell_no, witness_ids, `comment`) VALUES ($instance, $user, $method, $zygoteType, $cellNo, $witnesses, $comment)";
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
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