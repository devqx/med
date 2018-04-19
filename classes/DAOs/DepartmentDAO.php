<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/16/14
 * Time: 8:33 AM
 */

class DepartmentDAO {
    private $conn = null;

    function __construct() {
        if(!isset($_SESSION)){@session_start();}
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/Department.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/CostCenterDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function get($id, $pdo=NULL){
        if(is_blank($id))
            return null;
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM departments WHERE id =" . $id;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                return (new Department($row['id']))
                    ->setName($row['name'])
                    ->setCostCentre((new CostCenterDAO())->get($row['cost_centre_id'], $pdo) )
                ;
            }
            return NULL;
        } catch (PDOException $e) {
            errorLog($e);
            return NULL;

        }
    }

    function getDepartments($pdo=NULL)
    {
        $depts = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM departments";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $depts[] = $this->get($row['id'], $pdo);
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            errorLog($e);
            return [];
        }
        return $depts;
    }

    function add($dept, $pdo = NULL){
        //$dept = new Department();
        if(trim($dept->getName()==''))return NULL;
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO departments SET `name` = '".escape($dept->getName())."', cost_centre_id=".$dept->getCostCentre()->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount()==1){
                $dept->setId($pdo->lastInsertId());
            }else {
                $dept = NULL;
            }
            $stmt = NULL;

        }catch (PDOException $e){
            $dept = NULL;
        }
        return $dept;
    }
    
    function find($name, $pdo = NULL){
	    $depts = [];
    	try{
		    $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
		    $sql = $sql = "SELECT * FROM departments WHERE `name` LIKE '%$name%'";
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

    function getOrCreate($dept,$pdo=null){
	
	    try{
		    $pdo = $pdo == null ? (new MyDBConnector)->getPDO() : $pdo;
		    $igen = $this->find($dept->getName(), $pdo)[0];
		    if(!$igen == null){
			    return $igen;
		    }else{
		    	return $this->add($dept, $pdo);
		    }
	    }catch (PDOException $e){
		    errorLog($e);
		    return null;
	    }
	
    }
    
    function update($dept, $pdo = NULL){
        //$dept = new Department();
        if(trim($dept->getName()==''))return NULL;
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "UPDATE departments SET `name` = '".escape($dept->getName())."', cost_centre_id=".$dept->getCostCentre()->getId()." WHERE id=".$dept->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount()==1){
                return $dept;
            }else {
                $dept = NULL;
            }
        }catch (PDOException $e){
            $dept = NULL;
        }
        return $dept;
    }
} 