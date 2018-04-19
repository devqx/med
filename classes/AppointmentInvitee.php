<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AppointmentInvitee
 *
 * @author pauldic
 */
class AppointmentInvitee implements JsonSerializable
{
	private $id;
	private $group;
	private $staff;
	private $staffs;

	function __construct($id = null)
	{
		$this->id = $id;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getGroup()
	{
		return $this->group;
	}

	public function getStaff()
	{
		return $this->staff;
	}

	public function getStaffs()
	{
		return $this->staffs;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function setGroup($group)
	{
		$this->group = $group;
	}

	public function setStaff($staff)
	{
		$this->staff = $staff;
	}

	public function setStaffs($staffs)
	{
		$this->staffs = $staffs;
	}

	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}

	function overlaps($new_start, $new_end, $pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$staffId = $this->getStaff()->getId();
		//add 1 second to the start time, and add 1 second to the end time, and continue
		//so that you can have events that start immediately the previous one ends
		$new_start = date('Y-m-d H:i:s',strtotime($new_start) + 1);
		$new_end = date('Y-m-d H:i:s', strtotime($new_end) + 1);
		//$sql = "SELECT a.id, a.group_id FROM appointment_group g LEFT JOIN appointment a ON a.group_id=g.id LEFT JOIN appointment_invitee i ON i.group_id=g.id WHERE '$new_start' < a.start_time OR '$new_end' > a.end_time AND i.staff_id=$staffId";
		$sql = "SELECT a.id, a.group_id FROM appointment_group g LEFT JOIN appointment a ON a.group_id=g.id LEFT JOIN appointment_invitee i ON i.group_id=g.id WHERE ((a.start_time <= '$new_start' AND a.end_time >= '$new_start') OR (a.start_time >= '$new_start' AND a.end_time <= '$new_end')) AND i.staff_id=$staffId AND a.status IN ('Active', 'Scheduled')";
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
