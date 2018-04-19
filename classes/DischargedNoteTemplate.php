<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/2/16
 * Time: 10:37 PM
 */
class DischargedNoteTemplate implements JsonSerializable
{
	private $id;
	private $title;
	private $content;

	/**
	 * DischargedNoteTemplate constructor.
	 * @param $id
	 */
	public function __construct($id=null)
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
	 * @return DischargedNoteTemplate
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param mixed $title
	 * @return DischargedNoteTemplate
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
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
	 * @return DischargedNoteTemplate
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}


}