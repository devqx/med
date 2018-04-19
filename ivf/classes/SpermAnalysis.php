<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/11/16
 * Time: 10:47 AM
 */
class SpermAnalysis implements JsonSerializable
{
	private $id;
	private $user;
	private $timeEntered;
	private $instance;
	private $volume;
	private $cellNo;
	private $density;
	private $motility;
	private $prog;
	private $abnormal;
	private $mar;
	private $aggl;
	private $comment;
	private $witnesses;

	/**
	 * SpermAnalysis constructor.
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
	 * @return SpermAnalysis
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
	 * @return SpermAnalysis
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
	 * @return SpermAnalysis
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
	 * @return SpermAnalysis
	 */
	public function setInstance($instance)
	{
		$this->instance = $instance;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getVolume()
	{
		return $this->volume;
	}

	/**
	 * @param mixed $volume
	 * @return SpermAnalysis
	 */
	public function setVolume($volume)
	{
		$this->volume = $volume;
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
	 * @return SpermAnalysis
	 */
	public function setCellNo($cellNo)
	{
		$this->cellNo = $cellNo;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDensity()
	{
		return $this->density;
	}

	/**
	 * @param mixed $density
	 * @return SpermAnalysis
	 */
	public function setDensity($density)
	{
		$this->density = $density;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMotility()
	{
		return $this->motility;
	}

	/**
	 * @param mixed $motility
	 * @return SpermAnalysis
	 */
	public function setMotility($motility)
	{
		$this->motility = $motility;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getProg()
	{
		return $this->prog;
	}

	/**
	 * @param mixed $prog
	 * @return SpermAnalysis
	 */
	public function setProg($prog)
	{
		$this->prog = $prog;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAbnormal()
	{
		return $this->abnormal;
	}

	/**
	 * @param mixed $abnormal
	 * @return SpermAnalysis
	 */
	public function setAbnormal($abnormal)
	{
		$this->abnormal = $abnormal;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMar()
	{
		return $this->mar;
	}

	/**
	 * @param mixed $mar
	 * @return SpermAnalysis
	 */
	public function setMar($mar)
	{
		$this->mar = $mar;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAggl()
	{
		return $this->aggl;
	}

	/**
	 * @param mixed $aggl
	 * @return SpermAnalysis
	 */
	public function setAggl($aggl)
	{
		$this->aggl = $aggl;
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
	 * @return SpermAnalysis
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
	 * @return SpermAnalysis
	 */
	public function setWitnesses($witnesses)
	{
		$this->witnesses = $witnesses;
		return $this;
	}

	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$user = $this->getUser() ? $this->getUser()->getId() : 'NULL';
			$timeEntered = $this->getTimeEntered() ? quote_esc_str($this->getTimeEntered()) : 'NOW()';
			$instance = $this->getInstance() ? $this->getInstance()->getId() : 'null';
			$volume = $this->getVolume() ? $this->getVolume() : 'null';
			$cellNo = $this->getCellNo() ? $this->getCellNo() : 'null';
			$density = $this->getDensity() ? $this->getDensity() : 'null';
			$motility = $this->getMotility() ? $this->getMotility() : 'null';
			$prog = !is_blank($this->getProg()) ? quote_esc_str($this->getProg()) : 'null';
			$abnormal = $this->getAbnormal() ? $this->getAbnormal() : 'null';
			$mar = !is_blank($this->getMar()) ? quote_esc_str($this->getMar()) : 'null';
			$aggl = !is_blank($this->getAggl()) ? quote_esc_str($this->getAggl()) : 'null';
			$comment = !is_blank($this->getComment()) ? quote_esc_str($this->getComment()) : 'null';
			$witnesses = count($this->getWitnesses()) > 0 ? quote_esc_str(implode(',', $this->getWitnesses())) : 'NULL';
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "INSERT INTO ivf_sperm_analysis (user_id, time_entered, instance_id, volume, cell_no, density, motility, prog, abnormal, mar, aggl, `comment`, witness_ids) VALUES ($user, $timeEntered, $instance, $volume, $cellNo, $density, $motility, $prog, $abnormal, $mar, $aggl, $comment, $witnesses)";
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