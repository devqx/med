<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/20/17
 * Time: 9:59 AM
 */
class ItemGroup implements JsonSerializable
{
 private $id;
	private $name;
	private $data;
	private $description;

	/**
	 * ItemFormulary constructor.
	 * @param $id
	 */
	public function __construct($id = null)
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
	 * @return ItemGroup
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
	 * @return ItemGroup
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return ItemGroup
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }


	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param mixed $data
	 * @return ItemGroup
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}

	function jsonSerialize()
	{
		return (object)get_object_vars($this);
		// TODO: Implement jsonSerialize() method.
	}


	function add($pdo = null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try{
			$sql = "INSERT INTO item_group SET `name`='". $this->getName() ."', description= '". $this->getDescription() ."'";
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				$pdo->commit();
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			$pdo->rollBack();
			$stmt = null;
			return null;
		}

	}

	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "UPDATE item_group SET 	`name` = '" . $this->getName() . "', description='". $this->getDescription() ."' WHERE id='". $this->getId() ."'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				return $this;
			} else {
				return null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function getOrCreate($name, $pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGroupDAO.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$groupItem =  (new ItemGroupDAO())->find($name, $pdo);
			if ($groupItem != null) {
				return $groupItem;
			} else {
				error_log("Came to create item group... $name...");
				
				$this->setName($name);
				$this->setDescription($name);
				return $this->add($pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
}