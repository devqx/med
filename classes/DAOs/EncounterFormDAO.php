<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/16/17
 * Time: 11:37 AM
 */
class EncounterFormDAO
{
	private $conn = null;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Encounter.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/EncounterForm.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/SFormDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo = null)
	{
		if (is_null($id) || is_blank($id)) {
			return null;
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM encounter_form WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$form = (new SFormDAO())->get($row['form_id'], $pdo);
				return (new EncounterForm($row['id']))->setEncounter( new Encounter($row['encounter_id']) )->setForm($form)->setDateAdded($row['time_added'])->setCreateUser(new StaffDirectory($row['user_id']));
			}
			return null;
		} catch (PDOException $exception) {
			errorLog($exception);
			return null;
		}
		
	}
	function forEncounter($encounterId, $pdo = null)
	{
		if (is_null($encounterId) || is_blank($encounterId)) {
			return [];
		}
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM encounter_form WHERE encounter_id=$encounterId";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $exception) {
			errorLog($exception);
			return [];
		}
		
	}
}