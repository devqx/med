<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/2/17
 * Time: 12:35 PM
 */
class Vital implements JsonSerializable
{
	private $id;
	private $name;
	private $unit;
	private $minimum;
	private $maximum;
	private $pattern;
	
	/**
	 * Vital constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
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
	 * @return Vital
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
	 *
	 * @return Vital
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getUnit()
	{
		return $this->unit;
	}
	
	/**
	 * @param mixed $unit
	 *
	 * @return Vital
	 */
	public function setUnit($unit)
	{
		$this->unit = $unit;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getMinimum()
	{
		return $this->minimum;
	}
	
	/**
	 * @param mixed $minimum
	 *
	 * @return Vital
	 */
	public function setMinimum($minimum)
	{
		$this->minimum = $minimum;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getMaximum()
	{
		return $this->maximum;
	}
	
	/**
	 * @param mixed $maximum
	 *
	 * @return Vital
	 */
	public function setMaximum($maximum)
	{
		$this->maximum = $maximum;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getPattern()
	{
		return $this->pattern;
	}
	
	/**
	 * @param mixed $pattern
	 *
	 * @return Vital
	 */
	public function setPattern($pattern)
	{
		$this->pattern = $pattern;
		return $this;
	}
	
	
	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : 'null';
		$unit = !is_blank($this->getUnit()) ? quote_esc_str($this->getUnit()) : 'null';
		$min = !is_blank($this->getMinimum()) ? quote_esc_str($this->getMinimum()) : 'null';
		$max = !is_blank($this->getMaximum()) ? quote_esc_str($this->getMaximum()) : 'null';
		$pattern = !is_blank($this->getPattern()) ? quote_esc_str($this->getPattern()) : 'null';
		try {
			$sql = "INSERT INTO vital SET `name`=$name, unit=$unit, min_val=$min, max_val=$max, pattern=$pattern";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return $this->setId($pdo->lastInsertId());
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	
	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : 'null';
		$unit = !is_blank($this->getUnit()) ? quote_esc_str($this->getUnit()) : 'null';
		$min = !is_blank($this->getMinimum()) ? quote_esc_str($this->getMinimum()) : 'null';
		$max = !is_blank($this->getMaximum()) ? quote_esc_str($this->getMaximum()) : 'null';
		$pattern = !is_blank($this->getPattern()) ? quote_esc_str($this->getPattern()) : 'null';
		try {
			$sql = "UPDATE vital SET `name`=$name, unit=$unit, min_val=$min, max_val=$max, pattern=$pattern WHERE id={$this->getId()}";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	
}