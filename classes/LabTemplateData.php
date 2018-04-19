<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LabTemplateData
 *
 * @author pauldic
 */
class LabTemplateData implements JsonSerializable
{
	private $id;
	private $method;
	private $labTemplate;
	private $reference;
	
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
	 * @return LabTemplateData
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 *
	 * @return LabTemplateData
	 */
	public function setMethod($method)
	{
		$this->method = $method;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getLabTemplate()
	{
		return $this->labTemplate;
	}
	
	/**
	 * @param mixed $labTemplate
	 *
	 * @return LabTemplateData
	 */
	public function setLabTemplate($labTemplate)
	{
		$this->labTemplate = $labTemplate;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getReference()
	{
		return $this->reference;
	}
	
	/**
	 * @param mixed $reference
	 *
	 * @return LabTemplateData
	 */
	public function setReference($reference)
	{
		$this->reference = $reference;
		return $this;
	}
	
	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/Connections/MyDBConnector.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$templateId = $this->getLabTemplate()->getId();
			$methodId = $this->getMethod()->getId();
			$reference = quote_esc_str($this->getReference());
			$sql = "INSERT INTO lab_template_data (lab_template_id, lab_method_id, reference) VALUES ($templateId, $methodId, $reference)";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if($stmt->rowCount()==1){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function update($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/Connections/MyDBConnector.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$templateId = $this->getLabTemplate()->getId();
			$methodId = $this->getMethod()->getId();
			$reference = quote_esc_str($this->getReference());
			$sql = "UPDATE lab_template_data SET lab_template_id=$templateId, lab_method_id=$methodId, reference=$reference WHERE id=".$this->getId();
			// error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if($stmt->rowCount()>=0){
				return $this;
			}
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
}
