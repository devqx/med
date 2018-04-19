<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/5/16
 * Time: 12:19 PM
 */
class ProcedureChecklistTemplateDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ProcedureChecklistTemplate.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function all($pdo=null){
		$templates = [];
		try {
			$pdo = $pdo===NULL ? $this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM procedure_checklist_template";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
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
			$sql = "SELECT * FROM procedure_checklist_template WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				return (new ProcedureChecklistTemplate($row['id']))->setTitle($row['title'])->setContent(htmlentities($row['content']));
			}
			return null;
		}catch (PDOException $e){
			errorLog("error: ".$e->getMessage());
		}
	}



	function add($template, $pdo=NULL){
		try{
			$pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
			$title = escape($template->getTitle());
			$content = escape($template->getContent());
			$sql = "INSERT INTO procedure_checklist_template (title, content) VALUES ('$title', '$content')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$template->setId($pdo->lastInsertId());
				return $template;
			}
			return NULL;

		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}


	function update($pdo = null)
	{

		$pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
		$title = $this->getTitle() ? quote_esc_str($this->getTitle()) : "NULL";
		$content = $this->getContent() ? quote_esc_str($this->getContent()) : "NULL";
		$sql = "UPDATE procedure_checklist_template SET title=$title, content=$content WHERE id={$this->getId()}";
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

}