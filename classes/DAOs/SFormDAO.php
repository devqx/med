<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/27/17
 * Time: 10:49 AM
 */
class SFormDAO
{
	private $conn = null;
	
	function __construct() {
		if(!isset($_SESSION)){@session_start();}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/SForm.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/SFormCategoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/SFormQuestionDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
			$this->conn=new MyDBConnector();
		}catch(PDOException $e) {
			exit( 'ERROR: ' . $e->getMessage() );
		}
	}
	
	function get($id, $pdo = null)
	{
		if ($id === null || is_blank($id)) {
			return null;
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM sform WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$questions = (new SFormQuestionDAO())->forForm($row['id'], $pdo);
				return (new SForm($row['id']))->setName($row['name'])
					->setCategory( (new SFormCategoryDAO())->get($row['category_id'], $pdo) )
					->setQuestions($questions);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function all($pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM sform";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
}