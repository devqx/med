<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/19/16
 * Time: 4:25 PM
 */
class FluidChart implements JsonSerializable
{
	private $id;
	private $patient;
	private $route;
	private $volume;
	private $type;
	private $user;
	private $timeEntered;
	private $inPatient;

	/**
	 * FluidChart constructor.
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }

	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 * @return FluidChart
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
	 * @return FluidChart
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRoute()
	{
		return $this->route;
	}

	/**
	 * @param mixed $route
	 * @return FluidChart
	 */
	public function setRoute($route)
	{
		$this->route = $route;
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
	 * @return FluidChart
	 */
	public function setVolume($volume)
	{
		$this->volume = $volume;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param mixed $type
	 * @return FluidChart
	 */
	public function setType($type)
	{
		$this->type = $type;
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
	 * @return FluidChart
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
	 * @return FluidChart
	 */
	public function setTimeEntered($timeEntered)
	{
		$this->timeEntered = $timeEntered;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getInPatient()
	{
		return $this->inPatient;
	}

	/**
	 * @param mixed $inPatient
	 * @return FluidChart
	 */
	public function setInPatient($inPatient)
	{
		$this->inPatient = $inPatient;
		return $this;
	}

	function add($pdo=null){
		try {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$patient_id = $this->getPatient() ? $this->getPatient()->getId() : "NULL";
			$in_patient_id = $this->getInPatient() ? $this->getInPatient()->getId() : "NULL";
			$route_id = $this->getRoute() ? $this->getRoute()->getId() : "NULL";
			$type = $this->getRoute() ? quote_esc_str($this->getRoute()->getType())  : quote_esc_str("input");
			$volume = $this->getVolume() ? $this->getVolume() : 0;
			$vol = ($type == "output") ? $volume * -1 : $volume;
			$user_id = $this->getUser() ? $this->getUser()->getId() : $_SESSION['staffID'];
			$time_entered = $this->getTimeEntered() ? quote_esc_str($this->getTimeEntered()) : "NOW()";

			$sql = "INSERT INTO fluid_chart (patient_id, in_patient_id, route_id, vol, type, user_id, time_entered) VALUES ($patient_id, $in_patient_id, $route_id, $vol, $type, $user_id, $time_entered)";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()==1){
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