<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/25/17
 * Time: 4:10 PM
 */
class ClinicalTaskComboData implements JsonSerializable
{
	private $id;
	private $clinicalTaskCombo;
	private $type;
	private $description;
	private $frequency;
	private $interval;
	private $taskCount;
	
	/**
	 * ClinicalTaskComboData constructor.
	 *
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
	 *
	 * @return ClinicalTaskComboData
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getClinicalTaskCombo()
	{
		return $this->clinicalTaskCombo;
	}
	
	/**
	 * @param mixed $clinicalTaskCombo
	 *
	 * @return ClinicalTaskComboData
	 */
	public function setClinicalTaskCombo($clinicalTaskCombo)
	{
		$this->clinicalTaskCombo = $clinicalTaskCombo;
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
	 *
	 * @return ClinicalTaskComboData
	 */
	public function setType($type)
	{
		$this->type = $type;
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
	 *
	 * @return ClinicalTaskComboData
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getFrequency()
	{
		return $this->frequency;
	}
	
	/**
	 * @param mixed $frequency
	 *
	 * @return ClinicalTaskComboData
	 */
	public function setFrequency($frequency)
	{
		$this->frequency = $frequency;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getInterval()
	{
		return $this->interval;
	}
	
	/**
	 * @param mixed $interval
	 *
	 * @return ClinicalTaskComboData
	 */
	public function setInterval($interval)
	{
		$this->interval = $interval;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getTaskCount()
	{
		return $this->taskCount;
	}
	
	/**
	 * @param mixed $taskCount
	 *
	 * @return ClinicalTaskComboData
	 */
	public function setTaskCount($taskCount)
	{
		$this->taskCount = $taskCount;
		return $this;
	}
	
	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO():$pdo;
			$clinicalTaskCombo = $this->getClinicalTaskCombo()->getId();
			$type = quote_esc_str($this->getType());
			$description = !is_blank($this->getDescription()) ? quote_esc_str($this->getDescription()) : 'NULL';
			$frequency = quote_esc_str($this->getFrequency());
			$interval = quote_esc_str($this->getInterval());
			$taskCount = $this->getTaskCount();
			$sql = "INSERT INTO clinical_task_combo_data SET clinical_task_combo_id=$clinicalTaskCombo, type=$type, description=$description, frequency=$frequency, `interval`=$interval,task_count=$taskCount ON DUPLICATE KEY UPDATE type=$type, description=$description, frequency=$frequency, `interval`=$interval,task_count=$taskCount";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()>0){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		}catch (PDOException $exception){
			errorLog($exception);
			return null;
		}
	}
	
	function delete($pdo =null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO():$pdo;
			
			$sql = "DELETE FROM clinical_task_combo_data WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()>0){
				return TRUE;
			}
			return FALSE;
		}catch (PDOException $exception){
			errorLog($exception);
			return FALSE;
		}
	}
	
	
}