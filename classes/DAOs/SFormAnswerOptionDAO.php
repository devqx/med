<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/20/17
 * Time: 2:07 PM
 */
class SFormAnswerOptionDAO
{
	private $conn = null;
	
	function __construct() {
		if(!isset($_SESSION)){@session_start();}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/SFormAnswerOption.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/SFormOptionDAO.php';
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
			$sql = "SELECT * FROM sform_answer_option WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new SFormAnswerOption($row['id']))->setText($row['answer_text'])->setOption( (new SFormOptionDAO())->get($row['option_id'], $pdo) );
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
}