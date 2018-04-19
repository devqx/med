<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MessageQueue
 *
 * @author pauldic
 */
class MessageQueue implements JsonSerializable
{
	private $id;
	private $source;
	private $message_content;
	private $message_status;
	private $pid;
	private $date_sent;
	
	function __construct($id, $source, $message_content, $message_status, $pid, $date_sent)
	{
		$this->id = $id;
		$this->source = $source;
		$this->message_content = $message_content;
		$this->message_status = $message_status;
		$this->pid = $pid;
		$this->date_sent = $date_sent;
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
	
	public function getSource()
	{
		return $this->source;
	}
	
	public function setSource($source)
	{
		$this->source = $source;
		return $this;
	}
	
	public function getMessage_content()
	{
		return $this->message_content;
	}
	
	public function setMessage_content($message_content)
	{
		$this->message_content = $message_content;
		return $this;
	}
	
	public function getMessage_status()
	{
		return $this->message_status;
	}
	
	public function setMessage_status($message_status)
	{
		$this->message_status = $message_status;
		return $this;
	}
	
	public function getPid()
	{
		return $this->pid;
	}
	
	public function setPid($pid)
	{
		$this->pid = $pid;
		return $this;
	}
	
	public function setDateSent($dateSent)
	{
		$this->date_sent = $dateSent;
		return $this;
	}
	
	public function getDateSent()
	{
		return $this->date_sent;
	}
	
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
}