<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SocioEconomicStatusDAO
 *
 * @author pauldic
 */
class SocioEconomicStatusDAO {
    private $conn = null;
    
    function __construct() {      
        try {                
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/SocioEconomicStatus.php';
            $this->conn=new MyDBConnector();   
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        } 
    }

    function getSocioEconomicStatus($sid, $pdo){
        $ses=new SocioEconomicStatus();
        try {                
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM socio_economic_status WHERE id=".$sid;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $ses->setId($row['id']);
                $ses->setName($row['name']);
            }
            $stmt = NULL;
        }catch(PDOException $e) {
            $ses=NULL;
        }
        return $ses;
    }

    function getSocioEconomicStatuss(){
        $socios=array();
        try {                
            $pdo= $this->conn->getPDO();
            $sql = "SELECT * FROM socio_economic_status";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $ses=new SocioEconomicStatus();
                    $ses->setId($row['id']);
                    $ses->setName($row['name']);
                $socios[]=$ses;
            }
            $stmt = NULL;
        }catch(PDOException $e) {
            $socios=NULL;
        }
        return $socios;
    }

}
