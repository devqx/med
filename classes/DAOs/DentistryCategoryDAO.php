<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/13/14
 * Time: 2:34 PM
 */
class DentistryCategoryDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DentistryCategory.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function get($id, $pdo = NULL)
    {
        $cat = new DentistryCategory();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM dentistry_category WHERE id = $id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $cat->setId($row['id']);
                $cat->setName($row['name']);
            } else {
                $cat = NULL;
            }
            $stmt = NULL;
            $sql = NULL; //is it necessary? does it save memory?
        } catch (PDOException $e) {
            $cat = NULL;
        }
        return $cat;
    }

    function all($pdo = NULL)
    {
        $cats = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM dentistry_category";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $cat = new DentistryCategory();
                $cat->setId($row['id']);
                $cat->setName($row['name']);

                $cats[] = $cat;
            }
            $stmt = NULL;
            $sql = NULL; //is it necessary? does it save memory?
        } catch (PDOException $e) {
            $cats = [];
        }
        return $cats;
    }

    function add($cat, $pdo=NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO dentistry_category (`name`) VALUES ('".escape($cat->getName())."')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount() == 1) {
                $cat->setId($pdo->lastInsertId());
            }
            $stmt = NULL;
            $sql = NULL; //is it necessary? does it save memory?
        } catch (PDOException $e) {
            $cat = NULL;
        }
        return $cat;
    }
	
	function find($name, $pdo = NULL){
		$cates = array();
		try{
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = $sql = "SELECT * FROM dentistry_category WHERE `name` LIKE '%$name%'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED,  PDO::FETCH_ORI_NEXT)){
				$cates[] = $this->get($row['id']);
			}
			$stmt = null;
		}catch (PDOException $e){
			errorLog($e);
			$cates = [];
		}
		return $cates;
	}
	
	function getOrCreate($cat, $pdo=null){
		try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$return = $this->find($cat->getName(), $pdo)[0];
			if(!$return == null){
				return $return;
				
			}else{
				return $this->add($cat, $pdo);
			}
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
} 