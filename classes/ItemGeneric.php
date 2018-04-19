<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/28/17
 * Time: 3:43 PM
 */
class ItemGeneric implements JsonSerializable
{
	private $id;
	private $name;
	private $category;
	private $description;

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 * @return ItemGeneric
	 */
	public function setId($id = null)
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
	 * @return ItemGeneric
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     * @return ItemGeneric
     */
    public function setCategory($category)
    {
        $this->category = $category;
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
	 * @return ItemGeneric
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}



	function jsonSerialize()
	{
		// TODO: Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}


	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$desc =   $this->getDescription()  ? $this->getDescription() : "NULL";
		$cat = $this->getCategory() ?  $this->getCategory()->getId() : "NULL";
		try{
			$sql = "INSERT INTO item_generic SET category_id=$cat, `name`= '". $this->getName() ."', description='". $desc . "'";
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
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
		$desc =   $this->getDescription()  ? $this->getDescription() : "NULL";
        $cat = $this->getCategory() ?  $this->getCategory()->getId() : "NULL";

        try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "UPDATE item_generic SET category_id=$cat, `name` = '" . $this->getName() . "', description='". $desc ."'  WHERE id='" . $this->getId() . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 1) {
			    error_log("yess");
				return $this->setId($pdo->lastInsertId());
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	
	function getOrCreate($pdo)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGenericDAO.php';
		try{
			$pdo = $pdo == null ? (new MyDBConnector)->getPDO() : $pdo;
			$igen = (new ItemGenericDAO())->find($this->getName(), $pdo)[0];
			if(!$igen == null){
				return $igen;
			}else{
				return $this->setName($this->getName())->setCategory($this->getCategory())->setDescription($this->getDescription())->add($pdo);
			}
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
		
	}


}