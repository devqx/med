<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LabResultDataDAO
 *
 * @author pauldic
 */
class OphthalmologyResultDataDAO {

    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/OphthalmologyResultData.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/OphthalmologyResult.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/OphthalmologyTemplateData.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyResultDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyTemplateDataDAO.php';
            if (!isset($_SESSION))
                session_start();
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($data, $pdo = NULL) {

        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $canCommit = TRUE;
            try {
                $pdo->beginTransaction();
            } catch (PDOException $e) {
                $canCommit = FALSE;
            }
            $counter = 0;
            foreach ($data as $datum) { // $datum = new OphthalmologyResultData();
                $sql = "INSERT INTO ophthalmology_result_data (ophthalmology_result_id, ophthalmology_template_data_id, `value`)  VALUES (" . $datum->getOphthalmologyResult()->getId() . ", " . $datum->getOphthalmologyTemplateData()->getId() . ", '" . $datum->getValue() . "')";
                $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $stmt->execute();
                $counter++;
            }

            if (count($data) === $counter) {
                if ($canCommit) {
                    $pdo->commit();
                }
            } else {
                $pdo->rollBack();
                error_log("error:What's wrong?");
            }

            $stmt = NULL;
        } catch (PDOException $e) {
            if ($pdo != null) {
                $pdo->rollBack();
            }
            error_log("PDO Exception");
            $stmt = NULL;
            $datum = null;
        }
        return $data;
    }

    function getResultDatum($ophResId, $ophTempDataId, $getFull = FALSE, $pdo = NULL) {
        $data = new OphthalmologyResultData();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_result_data WHERE ophthalmology_result_id = $ophResId AND ophthalmology_template_data_id= $ophTempDataId";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                if ($getFull) {
                    $res = (new OphthalmologyResultDAO())->get($row['ophthalmology_result_id'], FALSE, $pdo);
                } else {
                    $res = new OphthalmologyResult($row['ophthalmology_result_id']);
                }
                $data->setOphthalmologyResult($res);    //Obj
                $data->setOphthalmologyTemplateData((new OphthalmologyTemplateDataDAO())->getOphthalmologyTemplateDatum($row['ophthalmology_template_data_id'], $pdo));    //Obj
                $data->setValue($row['value']);
            } else {
                $data = NULL;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $data = NULL;
        }
        return $data;
    }

    function getResultData($rid, $getFull = FALSE, $pdo = NULL) {
        $datas = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_result_data WHERE ophthalmology_result_id=".$rid;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $data = new OphthalmologyResultData();
                if ($getFull) {
                    $res = (new OphthalmologyResultDAO())->get($row['ophthalmology_result_id'], FALSE, $pdo);
                } else {
                    $res = new OphthalmologyResult($row['ophthalmology_result_id']);
                }
                $data->setOphthalmologyResult($res);    //Obj
                $data->setOphthalmologyTemplateData((new OphthalmologyTemplateDataDAO())->getOphthalmologyTemplateDatum($row['ophthalmology_template_data_id'], FALSE, $pdo));    //Obj
                $data->setValue($row['value']);
                $datas[] = $data;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $datas = array();
        }
        return $datas;
    }

}
