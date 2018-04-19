<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/13/16
 * Time: 8:55 AM
 */
class PackageItem implements JsonSerializable
{
	private $id;
	private $package;
	private $itemCode;
	private $quantity;
	
	/**
	 * PackageItem constructor.
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
	 * @return PackageItem
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
	 * @return PackageItem
	 */
	public function setPackage($package)
	{
		$this->package = $package;
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
	 * @return PackageItem
	 */
	public function setItemCode($itemCode)
	{
		$this->itemCode = $itemCode;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}
	
	/**
	 * @param mixed $quantity
	 *
	 * @return PackageItem
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
		return $this;
	}
	
	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			
			$package = $this->getPackage() ? $this->getPackage()->getId() : 'null';
			$itemCode = !is_blank($this->getItemCode()) ? quote_esc_str($this->getItemCode()) : 'null';
			$quantity = $this->getQuantity() ? $this->getQuantity() : 0;
			
			$sql = "INSERT INTO package_item SET package_id=$package, item_code=$itemCode, quantity=$quantity";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $exception) {
			errorLog($exception);
			return null;
		}
	}
	
	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			
			$package = $this->getPackage() ? $this->getPackage()->getId() : 'null';
			$itemCode = !is_blank($this->getItemCode()) ? quote_esc_str($this->getItemCode()) : 'null';
			$quantity = $this->getQuantity() ? $this->getQuantity() : 0;
			
			$sql = "UPDATE package_item SET package_id=$package, item_code=$itemCode, quantity=$quantity WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return $this;
			}
			return null;
		} catch (PDOException $exception) {
			errorLog($exception);
			return null;
		}
	}
	
	function delete($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			
			$sql = "DELETE FROM package_item WHERE id={$this->getId()}";
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