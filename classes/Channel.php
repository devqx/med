<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Channel
 *
 * @author pauldic
 */
class Channel implements JsonSerializable
{
	private $id;
	private $name;
	private $description;
	private $enabled;
	
	function __construct($id, $name, $description, $enabled = true)
	{
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
		$this->enabled = $enabled;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	public function getDescription()
	{
		return $this->description;
	}
	
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}
	
	/**
	 * @param mixed $enabled
	 */
	public function setEnabled($enabled)
	{
		$this->enabled = $enabled;
	}
	
	/**
	 * @return mixed
	 */
	public function getEnabled()
	{
		return $this->enabled;
	}
	
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	
}

?>
