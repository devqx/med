<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/18/16
 * Time: 2:30 PM
 */
class InsuranceTypeDAO
{
    private $conn = null;

    function __construct()
    {
        if (!isset($_SESSION)) {
            @session_start();
        }
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceType.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';

            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function all($pdo = NULL)
    {
        $data = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM insurance_type";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $data[] = $this->get($row['id'], $pdo);
            }
        } catch (PDOException $e) {
            errorLog($e);
            $data = [];
        }
        return $data;
    }

    function get($id, $pdo = NULL)
    {
        //if (is_blank($id)) return NULL;
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM insurance_type WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                return (new InsuranceType($row["id"]))->setName($row["name"]);
            }
            return NULL;
        } catch (PDOException $e) {
            errorLog($e);
            return NULL;
        }
    }
}