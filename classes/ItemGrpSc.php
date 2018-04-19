<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/21/17
 * Time: 11:09 AM
 */
class ItemGrpSc implements JsonSerializable
{
	private $id;
	private $service_center;
	private $itemGroup;

	/**
	 * itemGrpSc constructor.
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
	 * @return ItemGrpSc
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getServiceCenter()
	{
		return $this->service_center;
	}

	/**
	 * @param mixed $service_center
	 * @return ItemGrpSc
	 */
	public function setServiceCenter($service_center)
	{
		$this->service_center = $service_center;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getItemGroup()
	{
		return $this->itemGroup;
	}

	/**
	 * @param mixed $itemGroup
	 * @return ItemGrpSc
	 */
	public function setItemGroup($itemGroup)
	{
		$this->itemGroup = $itemGroup;
		return $this;
	}

	/**
	 * @return mixed
	 */

//eco bank context

	function jsonSerialize()
	{
		// TODO: Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}

	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$aray = $this->getItemGroup();
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			if(is_array($aray)){
                $sql = "INSERT IGNORE INTO item_group_sc (service_center_id, group_id) VALUES ";
                $sqlPart = [];
                foreach ($aray as $i => $grp) {
                    $sqlPart[] = '(' . $this->getServiceCenter()->getId() . ', ' . $grp . ')';
                }
                $sql .= implode(', ', $sqlPart);
            }else{
                $sql = "INSERT INTO item_group_sc (service_center_id, group_id) VALUES ('". $this->getServiceCenter()->getId() ."', '". $this->getItemGroup()->getId() ."')";
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

	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo==null? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "UPDATE service_centre SET group_id='" . $this->getItemGroup()->getId() ."' WHERE service_center_id=". $this->getServiceCenter()->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() >= 1){
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