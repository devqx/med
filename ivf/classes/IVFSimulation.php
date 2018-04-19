<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/22/16
 * Time: 11:37 AM
 */
class IVFSimulation implements JsonSerializable
{
	private $id;
	private $enrolment;
	private $recordDate;
	private $recordedBy;
	private $day;
	private $endo;
	private $e2Level;
	private $gnrha;
	private $ant;
	private $fsh;
	private $hmg;
	private $remarks;
	private $simulation;
	private $data;
	private $total;

	/**
	 * IVFSimulation constructor.
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
	 * @return IVFSimulation
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEnrolment()
	{
		return $this->enrolment;
	}

	/**
	 * @param mixed $enrolment
	 * @return IVFSimulation
	 */
	public function setEnrolment($enrolment)
	{
		$this->enrolment = $enrolment;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRecordDate()
	{
		return $this->recordDate;
	}

	/**
	 * @param mixed $recordDate
	 * @return IVFSimulation
	 */
	public function setRecordDate($recordDate)
	{
		$this->recordDate = $recordDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRecordedBy()
	{
		return $this->recordedBy;
	}

	/**
	 * @param mixed $recordedBy
	 * @return IVFSimulation
	 */
	public function setRecordedBy($recordedBy)
	{
		$this->recordedBy = $recordedBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDay()
	{
		return $this->day;
	}

	/**
	 * @param mixed $day
	 * @return IVFSimulation
	 */
	public function setDay($day)
	{
		$this->day = $day;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEndo()
	{
		return $this->endo;
	}

	/**
	 * @param mixed $endo
	 * @return IVFSimulation
	 */
	public function setEndo($endo)
	{
		$this->endo = $endo;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getE2Level()
	{
		return $this->e2Level;
	}

	/**
	 * @param mixed $e2Level
	 * @return IVFSimulation
	 */
	public function setE2Level($e2Level)
	{
		$this->e2Level = $e2Level;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getGnrha()
	{
		return $this->gnrha;
	}

	/**
	 * @param mixed $gnrha
	 * @return IVFSimulation
	 */
	public function setGnrha($gnrha)
	{
		$this->gnrha = $gnrha;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAnt()
	{
		return $this->ant;
	}

	/**
	 * @param mixed $ant
	 * @return IVFSimulation
	 */
	public function setAnt($ant)
	{
		$this->ant = $ant;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getFsh()
	{
		return $this->fsh;
	}

	/**
	 * @param mixed $fsh
	 * @return IVFSimulation
	 */
	public function setFsh($fsh)
	{
		$this->fsh = $fsh;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getHmg()
	{
		return $this->hmg;
	}

	/**
	 * @param mixed $hmg
	 * @return IVFSimulation
	 */
	public function setHmg($hmg)
	{
		$this->hmg = $hmg;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRemarks()
	{
		return $this->remarks;
	}

	/**
	 * @param mixed $remarks
	 * @return IVFSimulation
	 */
	public function setRemarks($remarks)
	{
		$this->remarks = $remarks;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSimulation()
	{
		return $this->simulation;
	}

	/**
	 * @param mixed $simulation
	 * @return IVFSimulation
	 */
	public function setSimulation($simulation)
	{
		$this->simulation = $simulation;
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
	 * @return IVFSimulation
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTotal()
	{
		return $this->total;
	}

	/**
	 * @param mixed $total
	 * @return IVFSimulation
	 */
	public function setTotal($total)
	{
		$this->total = $total;
		return $this;
	}

	/**
	 * @param null $pdo
	 * @return IVFSimulation
	 */
	function add($pdo=null){
		@session_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

		$enrolment_id = $this->getEnrolment() ? $this->getEnrolment()->getId() : 'NULL';
		$recordDate = $this->getRecordDate() ? quote_esc_str($this->getRecordDate()) : 'NOW()';
		$recordedBy = $this->getRecordedBy() ? $this->getRecordedBy()->getId() : $_SESSION['staffID'];
		$day = $this->getDay() ? $this->getDay() : 'NULL'; // or 0?
		$endo = $this->getEndo() ? quote_esc_str($this->getEndo()) : 'NULL';
		$e2Level = $this->getE2Level() ? quote_esc_str($this->getE2Level()) : 'NULL';
		$gnrha = $this->getGnrha() ? quote_esc_str($this->getGnrha()) : 'NULL';
		$ant = $this->getAnt() ? quote_esc_str($this->getAnt()) : 'NULL';
		$fsh = $this->getFsh() ? quote_esc_str($this->getFsh()) : 'NULL';
		$hmg = $this->getHmg() ? quote_esc_str($this->getHmg()) : 'NULL';
		$remarks = !is_blank($this->getRemarks()) ? quote_esc_str($this->getRemarks()) : 'NULL';

		try {
			$pdo = $pdo===null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "INSERT INTO ivf_simulation (enrolment_id, record_date, recorded_by_id, `day`, endo, e2, gnrha, ant, fsh, hmg, remarks) VALUES ($enrolment_id, $recordDate, $recordedBy, $day, $endo, $e2Level, $gnrha, $ant, $fsh, $hmg, $remarks)";
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