<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LabResultData
 *
 * @author pauldic
 */
class LabResultData implements JsonSerializable
{
	private $labResult;
	private $labTemplateData;
	private $value;
	
	function __construct($labResult = null, $labTemplateData = null)
	{
		$this->labResult = $labResult;
		$this->labTemplateData = $labTemplateData;
	}
	
	function getLabResult()
	{
		return $this->labResult;
	}
	
	function getLabTemplateData()
	{
		return $this->labTemplateData;
	}
	
	function getValue()
	{
		return $this->value;
	}
	
	function setLabResult($labResult)
	{
		$this->labResult = $labResult;
	}
	
	function setLabTemplateData($labTemplateData)
	{
		$this->labTemplateData = $labTemplateData;
	}
	
	function setValue($value)
	{
		$this->value = $value;
	}
	
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
}
