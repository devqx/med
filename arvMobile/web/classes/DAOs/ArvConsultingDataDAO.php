<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/21/16
 * Time: 1:10 PM
 */
class ArvConsultingDataDAO
{
    private $conn = null;

    public function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/arvMobile/web/classes/ArvConsulting.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/arvMobile/web/classes/ArvConsultingData.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/ArvConsultingDAO.php';
            if (!isset($_SESSION)) session_start();
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($data, $pdo=null){
        // $data = new ArvConsultingData();
        
        $arvConsultingId = $data->getArvConsulting()->getId();
        $type = $data->getType();
        $typeDataId = $data->getTypeData()->getId();

        $sql = "INSERT INTO arv_consulting_data (arv_consulting_id, type, type_data_id) VALUES ($arvConsultingId, '$type', $typeDataId)";
        try {
            $pdo = $pdo==null? $this->conn->getPDO(): $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount()==1){
                $data->setId($pdo->lastInsertId());
                return $data;
            }
            return null;
        }catch (PDOException $e){
            errorLog($e);
            return null;
        }
    }
}