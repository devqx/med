<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/1/16
 * Time: 9:12 AM
 */
class EyeReviewDAO
{
	private $conn = null;

	function __construct()
	{
		try{
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
			require_once  $_SERVER ['DOCUMENT_ROOT'] .'/classes/EyeReview.php';
			$this->conn=new MyDBConnector();
		}catch (PDOException $e){
			exit('Error' . $e->getMessage());
		}
	}

	function get($id, $pdo=null){
		if (null === $id){
			return NULL;
		}
		try {
			$pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * from eye_review WHERE id=".$id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new EyeReview($row['id']))->setName($row['name'])->setCategory($row['category_id']);
			} else {
				return NULL;
			}
		}catch(PDOException $e) {
			errorLog($e);
			return NULL;
		}
	}


	function getAll($pdo=null){
		try{
			$pdo = $pdo==NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM eye_review";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$data = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$data[] = (new EyeReview($row['id']))->setName($row['name'])->setCategory($row['category_id']);
			}
			return $data;
					
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}

	}

}