<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/11/17
 * Time: 3:35 PM
 */
class AppointmentResource implements JsonSerializable
{
	private $id;
	private $group;
	private $resource;
	private $resources;
	
	function __construct($id = null)
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
	 * @return AppointmentResource
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
	 *
	 * @return AppointmentResource
	 */
	public function setGroup($group)
	{
		$this->group = $group;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getResource()
	{
		return $this->resource;
	}
	
	/**
	 * @param mixed $resource
	 *
	 * @return AppointmentResource
	 */
	public function setResource($resource)
	{
		$this->resource = $resource;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getResources()
	{
		return $this->resources;
	}
	
	/**
	 * @param mixed $resources
	 *
	 * @return AppointmentResource
	 */
	public function setResources($resources)
	{
		$this->resources = $resources;
		return $this;
	}
	
	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
	
	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$groupId = $this->getGroup()->getId();
		$resourceId = $this->getResource()->getId();
		
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "INSERT INTO appointment_resource (group_id, resource_id) VALUES ($groupId, $resourceId)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		}catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function overlaps($new_start, $new_end, $pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$resourceId = $this->getResource()->getId();
		//add 1 second to the start time, and add 1 second to the end time, and continue
		//so that you can have events that start immediately the previous one ends
		$new_start = date('Y-m-d H:i:s',strtotime($new_start) + 1);
		$new_end = date('Y-m-d H:i:s', strtotime($new_end) + 1);
		$sql = "SELECT a.id, a.group_id FROM appointment_group g LEFT JOIN appointment a ON a.group_id=g.id LEFT JOIN appointment_resource i ON i.group_id=g.id WHERE ((a.start_time <= '$new_start' AND a.end_time >= '$new_start') OR (a.start_time >= '$new_start' AND a.end_time <= '$new_end')) AND i.resource_id=$resourceId AND a.status IN ('Active', 'Scheduled')";
		//error_log($sql);
		//return true;
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			return ($stmt->rowCount() >= 1);
		}catch (PDOException $e){
			errorLog($e);
			return false;
		}
	}
	
}