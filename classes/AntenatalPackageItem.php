<?php

/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 12/10/15
 * Time: 4:17 PM
 */
class AntenatalPackageItem implements JsonSerializable
{
	private $id;
	private $package;
	private $name;
	private $itemCode;
	private $usage;
	private $type;
	
	/**
	 * AntenatalPackageItems constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null)
	{
		$this->id = $id;
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
	 * @return AntenatalPackageItem
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getPackage()
	{
		return $this->package;
	}
	
	/**
	 * @param mixed $package
	 *
	 * @return AntenatalPackageItem
	 */
	public function setPackage($package)
	{
		$this->package = $package;
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
	 * @return AntenatalPackageItem
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getItemCode()
	{
		return $this->itemCode;
	}
	
	/**
	 * @param mixed $itemCode
	 *
	 * @return AntenatalPackageItem
	 */
	public function setItemCode($itemCode)
	{
		$this->itemCode = $itemCode;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getUsage()
	{
		return $this->usage;
	}
	
	/**
	 * @param mixed $usage
	 *
	 * @return AntenatalPackageItem
	 */
	public function setUsage($usage)
	{
		$this->usage = $usage;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * @param mixed $type
	 *
	 * @return AntenatalPackageItem
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	
	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	function update($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT']. '/Connections/MyDBConnector.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$itemId = $this->getName();
		$type = quote_esc_str($this->getType());
		$usage = $this->getUsage();
		$code = quote_esc_str($this->getItemCode());
		
		try {
			$sql = "UPDATE antenatal_package_item SET item_id=$itemId, type=$type, item_usage=$usage, item_code=$code WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if($stmt->rowCount() >= 0){
				return $this;
			}
			return null;
		} catch (PDOException $exception){
			errorLog($exception);
			return null;
		}
	}
	
	function delete($pdo = null) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			
			$sql = "DELETE FROM antenatal_package_item WHERE id={$this->getId()}";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return true;
			}
			return false;
		} catch (PDOException $exception) {
			errorLog($exception);
			return null;
		}
	}
}