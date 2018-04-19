<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/10/16
 * Time: 11:47 AM
 */
class EggCollectionFollicleData implements JsonSerializable
{
	private $id;
	private $eggCollection;
	private $size;
	private $value;

	/**
	 * EggCollectionFollicleData constructor.
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
	 * @return EggCollectionFollicleData
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEggCollection()
	{
		return $this->eggCollection;
	}

	/**
	 * @param mixed $eggCollection
	 * @return EggCollectionFollicleData
	 */
	public function setEggCollection($eggCollection)
	{
		$this->eggCollection = $eggCollection;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @param mixed $size
	 * @return EggCollectionFollicleData
	 */
	public function setSize($size)
	{
		$this->size = $size;
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
	 * @return EggCollectionFollicleData
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$instance = $this->getEggCollection() ? $this->getEggCollection()->getId() : 'null';
			$size = $this->getSize() ? $this->getSize()->getId() : 'NULL';
			$value = $this->getValue() ? $this->getValue() : 0;
			$sql = "INSERT INTO ivf_egg_collection_follicle_data (egg_collection_id, size_id, `value`) VALUES ($instance, $size, $value)";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;

		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

}