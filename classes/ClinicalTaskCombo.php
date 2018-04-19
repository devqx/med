<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/24/17
 * Time: 5:42 PM
 */
class ClinicalTaskCombo implements JsonSerializable
{
	private $id;
	private $name;
	private $data;
	
	/**
	 * ClinicalTaskCombo constructor.
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
	 * @return ClinicalTaskCombo
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * @param mixed $name
	 *
	 * @return ClinicalTaskCombo
	 */
	public function setName($name)
	{
		$this->name = $name;
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
	 *
	 * @return ClinicalTaskCombo
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}
	
	function add($pdo = null)
	{
		@session_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$name = quote_esc_str($this->getName());
			$userId = $_SESSION['staffID'];
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			
			$pdo->beginTransaction();
			$sql = "INSERT INTO clinical_task_combo SET `name`=$name, create_time=NOW(), create_user_id=$userId";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$this->setId($pdo->lastInsertId());
				
				// add the combo data
				foreach ($this->getData() as $data) {
					//$data = new ClinicalTaskComboData();
					if ($data->setClinicalTaskCombo($this)->add($pdo) == null) {
						$pdo->rollBack();
						return null;
					}
				}
				$pdo->commit();
				return $this;
			}
			return null;
		} catch (PDOException $exception) {
			errorLog($exception);
			return null;
		}
	}
	function update($pdo = null)
	{
		@session_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$name = quote_esc_str($this->getName());
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			
			$pdo->beginTransaction();
			$sql = "UPDATE clinical_task_combo SET `name`=$name WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				foreach ($this->getData() as $data) {
					//$data = new ClinicalTaskComboData();
					if ($data->setClinicalTaskCombo($this)->add($pdo) == null) {
						continue;//this will not add the existing ones again
					}
				}
				$pdo->commit();
				return $this;
			}
			return null;
		} catch (PDOException $exception) {
			errorLog($exception);
			return null;
		}
	}
	
}