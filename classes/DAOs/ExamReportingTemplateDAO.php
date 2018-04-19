<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/5/16
 * Time: 6:24 PM
 */
class ExamReportingTemplateDAO
{
	private $conn = NULL;

	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ExamReportingTemplate.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	function get($id, $pdo=NULL){
		try {
			$pdo = $pdo==NULL?$this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM exam_report_template WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				return (new ExamReportingTemplate($row['id']))->setBodyPart($row['body_part'])->setTitle($row['title']);
			}
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

	function all($pdo=NULL){
		$data = [];
		try {
			$pdo = $pdo==NULL?$this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM exam_report_template";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}
}