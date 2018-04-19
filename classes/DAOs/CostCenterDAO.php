<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/3/15
 * Time: 4:39 PM
 */
class CostCenterDAO
{
    private $conn = null;

    function __construct() {
        if(!isset($_SESSION)){@session_start();}
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/CostCenter.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function add($costCenter, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $name = escape($costCenter->getName());
            $description = escape($costCenter->getDescription());
            $code = escape($costCenter->getAnalyticalCode());
            $sql = "INSERT INTO cost_centre (`name`, analytical_code, description) VALUES ('$name', '$code','$description')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($stmt->rowCount()>0){
                $costCenter->setId($pdo->lastInsertId());
                return $costCenter;
            }
            return NULL;

        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function update($costCenter, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $name = escape($costCenter->getName());
            $description = escape($costCenter->getDescription());
            $code = escape($costCenter->getAnalyticalCode());
            $sql = "UPDATE cost_centre SET `name`='$name', analytical_code='$code', description='$description' WHERE id=".$costCenter->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($stmt->rowCount()>0){
                $costCenter->setId($pdo->lastInsertId());
                return $costCenter;
            }
            return NULL;

        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function get($id, $pdo=NULL)
    {
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM cost_centre WHERE id = $id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $costCenter = new CostCenter($row['id']);
                $costCenter->setName($row['name']);
                $costCenter->setDescription($row['description']);
                $costCenter->setAnalyticalCode($row['analytical_code']);
                return $costCenter;
            }
            return NULL;
        } catch (PDOException $e) {
            errorLog($e);
            return NULL;
        }
    }
	
	
	function find($name, $pdo = NULL){
		$depts = array();
		try{
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = $sql = "SELECT * FROM cost_centre WHERE `name` LIKE '%$name%'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED,  PDO::FETCH_ORI_NEXT)){
				$depts[] = $this->get($row['id']);
			}
			$stmt = null;
		}catch (PDOException $e){
			errorLog($e);
			$depts = [];
		}
		return $depts;
	}
	
	function getOrCreate($costCent,$pdo=null){
		
		try{
			$pdo = $pdo == null ? (new MyDBConnector)->getPDO() : $pdo;
			$igen = $this->find($costCent->getName(), $pdo)[0];
			if(!$igen == null){
				return $igen;
			}else{
				return $this->add($costCent, $pdo);
			}
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
		
	}

    function all($pdo=NULL){
        $centres = [];
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM cost_centre";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $costCenter = new CostCenter($row['id']);
                $costCenter->setName($row['name']);
                $costCenter->setDescription($row['description']);
                $costCenter->setAnalyticalCode($row['analytical_code']);
                $centres[] = $costCenter;
            }
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
        return $centres;
    }
}