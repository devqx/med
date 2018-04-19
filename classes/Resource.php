<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Resource
 *
 * @author pauldic
 */
class Resource implements JsonSerializable
{
	private $id;
	private $name;
	private $type;
	private $aeTitle;
	private $modality;
	private $stationName;
	
	/**
	 * Resource constructor.
	 *
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
	 *
	 * @return Resource
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
	 * @return Resource
	 */
	public function setName($name)
	{
		$this->name = $name;
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
	 * @return Resource
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAeTitle()
	{
		return $this->aeTitle;
	}
	
	/**
	 * @param mixed $aeTitle
	 *
	 * @return Resource
	 */
	public function setAeTitle($aeTitle)
	{
		$this->aeTitle = $aeTitle;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getModality()
	{
		return $this->modality;
	}
	
	/**
	 * @param mixed $modality
	 *
	 * @return Resource
	 */
	public function setModality($modality)
	{
		$this->modality = $modality;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getStationName()
	{
		return $this->stationName;
	}
	
	/**
	 * @param mixed $stationName
	 *
	 * @return Resource
	 */
	public function setStationName($stationName)
	{
		$this->stationName = $stationName;
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
		
		$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
		$name = quote_esc_str($this->getName());
		$type=quote_esc_str($this->getType());
		$modality = !is_blank($this->getModality()) ? quote_esc_str($this->getModality()) : 'NULL';
		$aeTitle = !is_blank($this->getAeTitle()) ? quote_esc_str($this->getAeTitle()) : 'NULL';
		$stationName = !is_blank($this->getStationName()) ? quote_esc_str($this->getStationName()) : 'NULL';
		
		$sql = "INSERT INTO resource SET `name`=$name, type=$type, modality=$modality, ae_title=$aeTitle, station_name=$stationName";
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()>0){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function update($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		
		$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
		$name = quote_esc_str($this->getName());
		$type=quote_esc_str($this->getType());
		$modality = !is_blank($this->getModality()) ? quote_esc_str($this->getModality()) : 'NULL';
		$aeTitle = !is_blank($this->getAeTitle()) ? quote_esc_str($this->getAeTitle()) : 'NULL';
		$stationName = !is_blank($this->getStationName()) ? quote_esc_str($this->getStationName()) : 'NULL';
		
		$sql = "UPDATE resource SET `name`=$name, type=$type, modality=$modality, ae_title=$aeTitle, station_name=$stationName WHERE id={$this->getId()}";
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()>=0){
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	
}
