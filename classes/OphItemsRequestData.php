<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 4:30 PM
 */
class OphItemsRequestData implements JsonSerializable
{
	
	private $id;
	private $request;
	private $item;
	
	/**
	 * OphItemsRequestData constructor.
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
	 *
	 * @return OphItemsRequestData
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getRequest()
	{
		return $this->request;
	}
	
	/**
	 * @param mixed $request
	 *
	 * @return OphItemsRequestData
	 */
	public function setRequest($request)
	{
		$this->request = $request;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getItem()
	{
		return $this->item;
	}
	
	/**
	 * @param mixed $item
	 *
	 * @return OphItemsRequestData
	 */
	public function setItem($item)
	{
		$this->item = $item;
		return $this;
	}
	
	
	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
}