<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StateDAO
 *
 * @author pauldic
 */
class StateDAO {
    private $conn = null;    
    
    function __construct() {      
        try {                
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/State.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/LGADAO.php';
            $this->conn=new MyDBConnector();   
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        } 
    }
	
	function getState($sid, $pdo = null)
	{
		$state = new State();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM state WHERE id=" . $sid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			//            error_log(print_r($sql, TRUE));
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$state->setId($row["id"]);
				$state->setName($row["name"]);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$state = null;
		}
		return $state;
	}
    
    function getStates($getFull=FALSE){

        $states=array();
        try {                
            $pdo=$this->conn->getPDO();
            $sql = "SELECT * from state";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $state=new State();
                    $state->setId($row["id"]);
                    $state->setName($row["name"]);
                    if($getFull){
                        $dao=new LGADAO();
                            $lgas=$dao->getLGAsByState($row["id"], $pdo);
                        $state->setLgas($lgas);
                    }
                $states[]=$state;
            }
            $stmt = null;
        }catch(PDOException $e) {
            $states=array();
        }
        return $states;
    }

    
    function getStaffHospitalID($staffid, $pdo){
        $clinicID=0;
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT clinicID FROM staff_directory WHERE staff_directory.staffid = '" . $staffid."'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $clinicID=$row['clinicID'];
            }
            $stmt = null;
        }catch(PDOException $e) {
            $clinicID=0;
        }
        return $clinicID;
    }
}
