<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 7/25/16
 * Time: 9:15 PM
 */
class EyePart
{

	private $id;
	private $name;
	private $shape;

	/**
	 * @return mixed
	 */
	public function getShape()
	{
		return $this->shape;
	}

	/**
	 * @param mixed $shape
	 * @return EyePart
	 */
	public function setShape($shape)
	{
		$this->shape = $shape;
		return $this;
	}
	private $coords;

	/**
	 * EyePart constructor.
	 * @param $id
	 */
	public function __construct($id=null)
	{
		$this->id = $id;
	}


	/**
	 * @return mixed
	 */
	public function getCoords()
	{
		return $this->coords;
	}

	/**
	 * @param mixed $coords
	 * @return EyePart
	 */
	public function setCoords($coords)
	{
		$this->coords = $coords;
		return $this;
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
	 * @return EyePart
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param mixed $name
	 * @return EyePart
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

}