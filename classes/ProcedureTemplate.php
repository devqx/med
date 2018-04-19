<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/3/15
 * Time: 12:27 PM
 */
class ProcedureTemplate implements JsonSerializable
{
	private $id;
	private $category;
	private $content;
	
	/**
	 * ProcedureTemplate constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null)
	{
		$this->id = $id;
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
	 */
	public function setId($id)
	{
		$this->id = $id;
	}
	
	/**
	 * @return mixed
	 */
	public function getCategory()
	{
		return $this->category;
	}
	
	/**
	 * @param mixed $category
	 */
	public function setCategory($category)
	{
		$this->category = $category;
	}
	
	/**
	 * @return mixed
	 */
	public function getContent()
	{
		return $this->content;
	}
	
	/**
	 * @param mixed $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}
	
	
	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	
}