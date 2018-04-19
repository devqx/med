<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NotificationOptions
 *
 * @author pauldic
 */
class NotificationOptions implements JsonSerializable
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/SubscribedChannel.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Channel.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function getSubscribedChannels($pid)
	{
		$subChannels = array();
		try {
			$pdo = $this->conn->getPDO();
			$sql = "SELECT ms.id, ms.patient, c.id, c.name, c.description FROM message_subscription ms, channel c WHERE ms.patient=" . $pid . " AND ms.channel_subscribed=c.id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			//$i = 0;
			while ($row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
				$subChannel = new SubscribedChannel($row[0], $row[1], new Channel($row[2], $row[3], $row[4]));
				$subChannels[] = $subChannel;
				//$subChannels[$i++] = $subChannel;
			}
			$stmt = null;
		} catch (PDOException $e) {
			echo 'ERROR: ' . $e->getMessage();
		}
		return $subChannels;
	}
	
	function getAllChannels()
	{
		$channels = array();
		try {
			$pdo = $this->conn->getPDO();
			$sql = "SELECT * FROM `channel` WHERE enabled IS TRUE";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			//$i = 0;
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
				$channels[] = $this->get($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			echo 'ERROR: ' . $e->getMessage();
		}
		return $channels;
	}
	
	function get($id, $pdo = null)
	{
		if (is_null($id)) {
			return null;
		}
		$channels = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM `channel` WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			//$i = 0;
			if ($row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
				return new Channel($row['id'], $row['name'], $row['description'], (bool)$row['enabled']);
			}
			$stmt = null;
		} catch (PDOException $e) {
			echo 'ERROR: ' . $e->getMessage();
		}
		return $channels;
	}
	
	function isSubscribed($allSubs, $id)
	{
		$status = false;
		for ($i = 0; $i < count($allSubs); $i++) {
			if ($allSubs[$i]->getChannel_subscribed()->getId() == $id) {
				$status = true;
			}
		}
		return $status;
	}
	
	function saveChanges($pid, $test, $ids)
	{
		$status = true;
		
		try {
			$pdo = $this->conn->getPDO();
			$dao = new PatientDemographDAO();
			if ($test && !$dao->hasEmail($pid, $pdo)) {
				return "Please update your email";
			}
			$pdo->beginTransaction();
			$pdo->query("DELETE FROM message_subscription WHERE patient=" . $pid);
			
			$sql = "INSERT INTO message_subscription (patient, channel_subscribed) VALUES ";
			
			$sqlParts = [];
			for ($i = 0; $i < count($ids); $i++) {
				$sqlParts[] = "(" . $pid . ", " . $ids[$i] . ")";
			}
			$sql .= implode(",", $sqlParts);
			//error_log($sql);
			$pdo->query($sql);
			$pdo->commit();
		} catch (PDOException $e) {
			echo 'ERROR: ' . $e->getMessage();
			$status = false;
		}
		return $status;
	}
	
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
}
