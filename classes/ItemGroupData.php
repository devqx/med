<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/20/17
 * Time: 10:16 AM
 */
class ItemGroupData implements JsonSerializable
{

	private $id;
	private $group;
	private $generic;

	/**
	 * ItemFormularyData constructor.
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
	 * @return ItemGroupData
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * @param mixed $group
	 * @return ItemGroupData
	 */
	public function setGroup($group)
	{
		$this->group = $group;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getGeneric()
	{
		return $this->generic;
	}

	/**
	 * @param mixed $generic
	 * @return ItemGroupData
	 */
	public function setGeneric($generic)
	{
		$this->generic = $generic;
		return $this;
	}

	/**
	 * @return mixed
	 */



	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}

	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$aray = $this->getGeneric();
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			if(is_array($aray)){
                $sql = "INSERT IGNORE INTO item_group_data (item_group, generic_id) VALUES";
                $sqlPart = [];
                foreach ($aray as $i => $grp) {
                    $sqlPart[] = '(' .$this->getGroup()->getId() . ', ' . $grp . ')';
                }
                $sql .= implode(', ', $sqlPart);
            }else{
			 $sql = "INSERT INTO item_group_data (item_group, generic_id) VALUES('". $this->getGroup()->getId() ."', '". $this->getGeneric()->getId() ."')";
            }
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 1) {
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}

	}
	
	function getOrCreate($pdo){
		error_log("come to create group data:".json_encode($this));
		try{
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$return = $this->find($pdo);
			if(!$return == null){
				return $return;
			}else{
				$create =   $this->add($pdo);
				return $create;
			}
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function find($pdo = null)
	{
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "SELECT * FROM item_group_data WHERE item_group='".$this->getGroup()->getId(). "' AND generic_id='". $this->getGeneric()->getId() ."'";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			  return $this->setGroup($row['item_group'])->setGeneric($row['generic_id']);
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
		}
		return null;
	}

	}
