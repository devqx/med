<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/6/17
 * Time: 3:59 PM
 */

class Currency implements JsonSerializable
{
	private $id;
	private $title;
	private $code;
	private $symbolRight;
	private $symbolLeft;
	private $decimalPlace;
	private $value;
	private $active = false;
	private $default = false;
	private $dateModified;
	
	/**
	 * Currency constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
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
	 * @return Currency
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
	 *
	 * @return Currency
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCode()
	{
		return $this->code;
	}
	
	/**
	 * @param mixed $code
	 *
	 * @return Currency
	 */
	public function setCode($code)
	{
		$this->code = $code;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getSymbolRight()
	{
		return $this->symbolRight;
	}
	
	/**
	 * @param mixed $symbolRight
	 *
	 * @return Currency
	 */
	public function setSymbolRight($symbolRight)
	{
		$this->symbolRight = $symbolRight;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getSymbolLeft()
	{
		return $this->symbolLeft;
	}
	
	/**
	 * @param mixed $symbolLeft
	 *
	 * @return Currency
	 */
	public function setSymbolLeft($symbolLeft)
	{
		$this->symbolLeft = $symbolLeft;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDecimalPlace()
	{
		return $this->decimalPlace;
	}
	
	/**
	 * @param mixed $decimalPlace
	 *
	 * @return Currency
	 */
	public function setDecimalPlace($decimalPlace)
	{
		$this->decimalPlace = $decimalPlace;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}
	
	/**
	 * @param mixed $value
	 *
	 * @return Currency
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isActive(): bool
	{
		return $this->active;
	}
	
	/**
	 * @param bool $active
	 *
	 * @return Currency
	 */
	public function setActive(bool $active): Currency
	{
		$this->active = $active;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isDefault(): bool
	{
		return $this->default;
	}
	
	/**
	 * @param bool $default
	 *
	 * @return Currency
	 */
	public function setDefault(bool $default): Currency
	{
		$this->default = $default;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDateModified()
	{
		return $this->dateModified;
	}
	
	/**
	 * @param mixed $dateModified
	 *
	 * @return Currency
	 */
	public function setDateModified($dateModified)
	{
		$this->dateModified = $dateModified;
		return $this;
	}
	
	public function __toString()
	{
		// Implement __toString() method.
		return $this->getSymbolRight() . $this->getSymbolLeft();
	}
	
	
	public function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
	
	function add($pdo = null)
	{
		try {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$title = quote_esc_str($this->getTitle());
			$code = quote_esc_str($this->getCode());
			$active = var_export($this->isActive(), true);
			$sLeft = quote_esc_str(htmlentities($this->getSymbolLeft()));
			$sRight = quote_esc_str(htmlentities($this->getSymbolRight()));
			$default = var_export($this->isDefault(), true);
			$dp = $this->getDecimalPlace() ? $this->getDecimalPlace() : 0;
			$val = $this->getValue() ? parseNumber($this->getValue()) : 0;
			$sql = "INSERT INTO currency (title, `code`, symbol_left, symbol_right, decimal_place, `value`, active, `default`) VALUES ($title, $code, $sLeft, $sRight, $dp, $val, $active, $default)";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function update($pdo = null)
	{
		try {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$title = quote_esc_str($this->getTitle());
			$code = quote_esc_str($this->getCode());
			$active = var_export($this->isActive(), true);
			$sLeft = quote_esc_str($this->getSymbolLeft());
			$sRight = quote_esc_str($this->getSymbolRight());
			$default = var_export($this->isDefault(), true);
			$dp = $this->getDecimalPlace() ? $this->getDecimalPlace() : 0;
			$val = $this->getValue() ? parseNumber($this->getValue()) : 0;
			$sql = "UPDATE currency SET title=$title, `code`=$code, symbol_left=$sLeft, symbol_right=$sRight, decimal_place=$dp, `value`=$val, active=$active, `default`=$default WHERE id={$this->getId()}";
			//error_log($sql);
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