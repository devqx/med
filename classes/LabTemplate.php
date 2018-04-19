<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LabTemplate
 *
 * @author pauldic
 */
class LabTemplate implements JsonSerializable
{
	private $id;
	private $label;
	
	private $data;
	
	/**
	 * LabTemplate constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
	
	public function __toString()
	{
		return "".$this->label;
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
	 * @return LabTemplate
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getLabel()
	{
		return $this->label;
	}
	
	/**
	 * @param mixed $label
	 *
	 * @return LabTemplate
	 */
	public function setLabel($label)
	{
		$this->label = $label;
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
	 * @return LabTemplate
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}
	
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();
			$label = quote_esc_str($this->getLabel());
			$sql = "INSERT INTO lab_template (label) VALUES ($label)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()==1){
				$this->setId($pdo->lastInsertId());
				foreach ($this->getData() as $datum){ //$datum=new LabTemplateData();
					$datum->setLabTemplate($this);
					$tpData = $datum->add($pdo);
					if($tpData==null){
						$pdo->rollBack();
						return null;
					}
				}
				$pdo->commit();
				return $this;
			}
			$pdo->rollBack();
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function update($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();
			$label = quote_esc_str($this->getLabel());
			$sql = "UPDATE lab_template SET label=$label WHERE id=".$this->getId();
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()>=0){
				foreach ($this->getData() as $datum){ //$datum=new LabTemplateData();
					//error_log(json_encode($datum));
					if($datum->getId() != null){
						$tpData = $datum->update($pdo);
					} else {
						$datum->setLabTemplate($this);
						$tpData = $datum->add($pdo);
					}
					
					if($tpData==null){
						$pdo->rollBack();
						return null;
					}
				}
				$pdo->commit();
				return $this;
			}
			$pdo->rollBack();
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
}
