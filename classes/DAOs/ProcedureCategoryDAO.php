<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/15/14
 * Time: 9:59 AM
 */

class ProcedureCategoryDAO {
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ProcedureCategory.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
        }
    }

    function add($cat, $pdo=NULL){
        try {
            $pdo = ($pdo === NULL) ? $this->conn->getPDO() : $pdo;

            $sql = "INSERT INTO procedure_category (`name`) VALUES ('".$cat->getName()."')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount()==1){
                $cat->setId($pdo->lastInsertId());
                return $cat;
            } else {
                return NULL;
            }
        } catch (PDOException $e) {
            return NULL;
        }
    }

    function all($pdo=NULL){
        $data = [];
        try {
            $pdo = ($pdo === NULL) ? $this->conn->getPDO() : $pdo;

            $sql = "SELECT * FROM procedure_category";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $cat = new ProcedureCategory($row['id']);
                $cat->setName($row['name']);

                $data[] = $cat;
            }
            return $data;
        } catch (PDOException $e) {
            errorLog($e);
            return [];
        }
    }

    function get($id, $pdo=NULL){
        try {
            $pdo = ($pdo === NULL) ? $this->conn->getPDO() : $pdo;

            $sql = "SELECT * FROM procedure_category WHERE id = $id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $cat = new ProcedureCategory($row['id']);
                $cat->setName($row['name']);
                return $cat;
            } else {
                return NULL;
            }
        } catch (PDOException $e) {
            return NULL;
        }
    }
    
    function find($name, $pdo= NULL){
        try {
	          $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;

            $sql = "SELECT * FROM procedure_category WHERE `name` LIKE '%$name%'";//.quote_esc_str($name);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $cat = new ProcedureCategory($row['id']);
                $cat->setName($row['name']);
                return $cat;
            } else {
                return NULL;
            }
        } catch (PDOException $e) {
            return NULL;
        }
    }
	
	function getOrCreate($name, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$return = $this->find($name, $pdo);
			
			if ($return != null) {
				return $return;
			} else {
				$category = new ProcedureCategory();
				$category->setName($name);
				return $this->add($category, $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}