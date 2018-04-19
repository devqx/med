<?php
/**
 * Created by PhpStorm.
 * User: nnamdi
 * Date: 1/5/17
 * Time: 4:15 PM
 */
class AuditLogDAO {

    /**
     * AuditLogDAO constructor.
     */

    private $conn =null;
    public function __construct()
    {

        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }

    }

    function  add($log,$pdo = NULL){
        return $log->add($pdo);
    }
}