<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/6/16
 * Time: 9:54 AM
 */
class EggCollection implements JsonSerializable
{
	private $id;
	private $instance;
	private $timeEntered;
	private $user;
	private $collectionTime;
	private $method;
	private $data;
	private $totalLeft;
	private $totalRight;
	private $witnesses;
	private $comment;
	private $doneBy;

	/**
	 * EggCollection constructor.
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
	 * @return EggCollection
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
	 * @return EggCollection
	 */
	public function setInstance($instance)
	{
		$this->instance = $instance;
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
	 * @return EggCollection
	 */
	public function setTimeEntered($timeEntered)
	{
		$this->timeEntered = $timeEntered;
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
	 * @return EggCollection
	 */
	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCollectionTime()
	{
		return $this->collectionTime;
	}

	/**
	 * @param mixed $collectionTime
	 * @return EggCollection
	 */
	public function setCollectionTime($collectionTime)
	{
		$this->collectionTime = $collectionTime;
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
	 * @return EggCollection
	 */
	public function setMethod($method)
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param mixed $data
	 * @return EggCollection
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getTotalLeft()
	{
		return $this->totalLeft;
	}

	/**
	 * @param mixed $totalLeft
	 * @return EggCollection
	 */
	public function setTotalLeft($totalLeft)
	{
		$this->totalLeft = $totalLeft;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTotalRight()
	{
		return $this->totalRight;
	}

	/**
	 * @param mixed $totalRight
	 * @return EggCollection
	 */
	public function setTotalRight($totalRight)
	{
		$this->totalRight = $totalRight;
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
	 * @return EggCollection
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
	 * @return EggCollection
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
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
	 * @return EggCollection
	 */
	public function setDoneBy($doneBy)
	{
		$this->doneBy = $doneBy;
		return $this;
	}


	function add($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			//$pdo->beginTransaction();
			$instance = $this->getInstance() ? $this->getInstance()->getId() : 'NULL';
			$timeEntered = $this->getTimeEntered() ? quote_esc_str($this->getTimeEntered()) : 'null';
			$userId = $this->getUser() ? $this->getUser()->getId() : 'null';
			$collectionTime = $this->getCollectionTime() ? quote_esc_str($this->getCollectionTime()) : 'null';
			$method = $this->getMethod() ? $this->getMethod()->getId() : 'null';
			$totalLeft = $this->getTotalLeft() ? $this->getTotalLeft() : 0;
			$totalRight = $this->getTotalRight() ? $this->getTotalRight() : 0;
			$witnesses = count($this->getWitnesses())> 0 ? quote_esc_str(implode(',',$this->getWitnesses())) : 'NULL';
			$comment = !is_blank($this->getComment()) ? quote_esc_str($this->getComment()) : 'null';
			$doneBy = $this->getDoneBy() ? $this->getDoneBy()->getId() : 'NULL';
			$sql = "INSERT INTO ivf_egg_collection (instance_id, time_entered, user_id, collection_time, method_id, done_by_id, total_left, total_right, witness_ids, `comment`) VALUES ($instance, $timeEntered, $userId, $collectionTime, $method, $doneBy, $totalLeft, $totalRight, $witnesses, $comment)";
			//error_log($sql  );
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$this->setId($pdo->lastInsertId());

				//foreach ($this->getData() as $data) {
				//	//$data = new EggCollectionFollicleData();
				//	$data->setEggCollection($this);
				//	if($data->add($pdo)==null){
				//		if($pdo->inTransaction()){$pdo->rollBack();}
				//		return null;
				//	}
				//}

				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
}