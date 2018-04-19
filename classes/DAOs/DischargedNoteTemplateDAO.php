<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/2/16
 * Time: 10:41 PM
 */
class DischargedNoteTemplateDAO
{

	private $conn = null;

	/**
	 * DischargedNoteTemplateDAO constructor.
	 * @param null $conn
	 */
	function  __construct()
	{
		try{
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DischargedNoteTemplate.php';
			$this->conn = new MyDBConnector();

		}catch (PDOException $e){
			errorLog("error occure :" .$e->getMessage());
		}
	}

	function all($pdo=null){
		$templates = [];
		try{
			$pdo = $pdo===NUll ? $this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM discharge_template";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$templates[] = $this->getTemplate($row['id'], $pdo);
			}
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
		return $templates;

	}

	function getTemplate($id, $pdo=null){
		try{
			$pdo = $pdo===NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM discharge_template WHERE id =$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				return (new DischargedNoteTemplate($row['id']))->setTitle($row['title'])->setContent(htmlentities($row['content']));
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	

	function add($template, $pdo=NULL){
		try{
			$pdo = $pdo===NULL?$this->conn->getPDO(): $pdo;
			$title = escape($template->getTitle());
			$content = escape($template->getContent());
			$sql = "INSERT INTO discharge_template (title, content) VALUES ('$title', '$content')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$template->setId($pdo->lastInsertId());
				return $template;
			}
		}catch (PDOException $e){
			error_log("error". $e);
			return NULL;
		}
	}

}