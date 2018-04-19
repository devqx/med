<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/22/16
 * Time: 5:29 PM
 */
class IVFNoteTemplateDAO
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
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/IVFNoteTemplate.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $pdo=null){
		$sql = "SELECT * FROM ivf_note_template WHERE id=$id";

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new IVFNoteTemplate($row['id']))->setTitle($row['title'])->setContent($row['content']);
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

	function all($pdo=null){
		$sql = "SELECT * FROM ivf_note_template";

		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			//return $data;
		}catch (PDOException $e){
			errorLog($e);
			$data = [];
		}
		return $data;
	}
}