<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExamRoom
 *
 * @author pauldic
 */
class ExamRoom implements JsonSerializable
{
	private $id;
	private $name;
	private $available;
	private $consultant;
	private $specialization;
	
	function __construct($id = null)
	{
		$this->id = $id;
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
	 * @return ExamRoom
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
	 * @return ExamRoom
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAvailable()
	{
		return $this->available;
	}
	
	/**
	 * @param mixed $available
	 *
	 * @return ExamRoom
	 */
	public function setAvailable($available)
	{
		$this->available = $available;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getConsultant()
	{
		return $this->consultant;
	}
	
	/**
	 * @param mixed $consultant
	 *
	 * @return ExamRoom
	 */
	public function setConsultant($consultant)
	{
		$this->consultant = $consultant;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getSpecialization()
	{
		return $this->specialization;
	}
	
	/**
	 * @param mixed $specialization
	 *
	 * @return ExamRoom
	 */
	public function setSpecialization($specialization)
	{
		$this->specialization = $specialization;
		return $this;
	}
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	function add($pdo = null)
	{
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$name = quote_esc_str($this->getName());
			$sql = "INSERT INTO exam_rooms SET room_name=$name";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()==1){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return $this;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	
	function update($pdo = null)
	{
		try {
			$name = quote_esc_str($this->getName());
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "UPDATE exam_rooms SET room_name=$name/*, available='" . $this->getAvailable() . "'*/ WHERE room_id=" . $this->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() <= 1)
				{return $this;}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
}
