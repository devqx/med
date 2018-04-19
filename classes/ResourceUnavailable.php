<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/12/17
 * Time: 12:42 PM
 */
class ResourceUnavailable implements JsonSerializable
{
	private $message;
	
	/**
	 * ResourceUnavailable constructor.
	 *
	 * @param $message
	 */
	public function __construct($message = null) { $this->message = $message; }
	
	
	/**
	 * @return mixed
	 */
	public function getMessage()
	{
		return $this->message;
	}
	
	/**
	 * @param mixed $message
	 *
	 * @return ResourceUnavailable
	 */
	public function setMessage($message)
	{
		$this->message = $message;
		return $this;
	}
	
	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
	
}