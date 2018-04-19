<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NotificationOptions
 *
 * @author pauldic
 */
class SubscribedChannel implements JsonSerializable
{
	private $id;
	private $pid;
	private $channel_subscribed;
	
	function __construct($id, $channel, $channel_subscribed)
	{
		$this->id = $id;
		$this->pid = $channel;
		$this->channel_subscribed = $channel_subscribed;
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
	
	public function getPID()
	{
		return $this->pid;
	}
	
	public function setPID($pid)
	{
		$this->pid = $pid;
		return $this;
	}
	
	public function getChannel_subscribed()
	{
		return $this->channel_subscribed;
	}
	
	public function setChannel_subscribed($channel_subscribed)
	{
		$this->channel_subscribed = $channel_subscribed;
		return $this;
	}
	
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
}
