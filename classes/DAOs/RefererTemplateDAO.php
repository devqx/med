<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 12/21/16
 * Time: 9:46 AM
 */
class RefererTemplateDAO
{

	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/RefererTemplate.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/RefererTemplateCategoryDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function all($pdo=NULL){
		$templates = [];
		try {
			$pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM referral_template";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$template = new RefererTemplate($row['id']);
				$template->setTitle($row['title']);
				$template->setCategory((new RefererTemplateCategoryDAO())->getCategory($row['category_id'], $pdo) );
				$template->setContent(htmlentities($row['content']));

				$templates[] = $template;
			}
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}

		return $templates;
	}
	function getTemplate($id, $pdo=NULL){
		$template = new RefererTemplate();
		try {
			$pdo = $pdo===NULL?$this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM referral_template WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$template->setId($row['id']);
				$template->setTitle($row['title']);
				$template->setCategory((new RefererTemplateCategoryDAO())->getCategory($row['category_id'], $pdo) );
				$template->setContent(htmlentities($row['content']));
			} else {
				$template = NULL;
			}
		}catch (PDOException $e){
			errorLog($e);
			return NULL;
		}

		return $template;
	}

	function add($template, $pdo=NULL){
		try {
			$pdo = $pdo===NULL?$this->conn->getPDO(): $pdo;
			$title = escape($template->getTitle());
			$category_id = $template->getCategory()->getId();
			$content = escape($template->getContent());
			$sql = "INSERT INTO referral_template (title, category_id, content) VALUES ('$title', $category_id, '$content')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$template->setId($pdo->lastInsertId());
				return $template;
			}
			return NULL;
		}catch (PDOException $e){
			errorLog($e);
			return NULL;
		}
	}

}