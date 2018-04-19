<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/7/18
 * Time: 11:59 AM
 */

class IVFAnalysisTemplatesDAO
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
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/IVFAnalysisTemplate.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo=null){
		$sql = "SELECT * FROM ivf_analysis_templates WHERE id=$id";
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new IVFAnalysisTemplate($row['id']))->setName($row['name'])->setType($row['type'])->setNote($row['note']);
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function all($type = null, $pdo=null){
		
		$filter = "";
		if ($type != null){
			$filter = " WHERE type='$type'";
		}
		
		$sql = "SELECT * FROM ivf_analysis_templates $filter";
		
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			
		}catch (PDOException $e){
			errorLog($e);
			$data = [];
		}
		return $data;
	}
	
	function types($pdo=null){
		
		$sql = "SELECT type FROM ivf_analysis_templates";
		
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			
		}catch (PDOException $e){
			errorLog($e);
			$data = [];
		}
		return $data;
	}
}