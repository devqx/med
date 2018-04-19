<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LabTemplateDAO
 *
 * @author pauldic
 */
class OphthalmologyTemplateDAO {

    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/OphthalmologyTemplate.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/OphthalmologyTemplateData.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyTemplateDataDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($temp, $pdo = NULL) {
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $canCommit = TRUE;
            try {
                $pdo->beginTransaction();
            } catch (PDOException $e) {
                $canCommit = FALSE;
            }
            $sql = "INSERT INTO ophthalmology_template (label)  VALUES ('" . $temp->getLabel() . "')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $data = $temp->getData();

                foreach($data as $d){
                    $d->setOphthalmologyTemplate(new OphthalmologyTemplate($pdo->lastInsertId()));
                }


                if (count($data) === count((new OphthalmologyTemplateDataDAO())->add($data, $pdo))) {
                    if ($canCommit) {
                        $pdo->commit();
                    }
                }else{
                    $pdo->rollBack();
                }
            }else{
                $pdo->rollBack();
            }

            $stmt = NULL;
        } catch (PDOException $e) {
            if ($pdo != null) {
                $pdo->rollBack();
            }
            error_log("PDO Exception");
            $stmt = NULL;
            $temp = null;
        }
        return $temp;
    }

    function getTemplate($tid, $pdo = NULL) {
        $temp = new OphthalmologyTemplate();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_template WHERE id=" . $tid;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $temp->setId($row['id']);
                $temp->setLabel($row['label']);
                $temp->setData((new OphthalmologyTemplateDataDAO())->getTemplateData($row['id'], FALSE, $pdo));
            } else {
                $temp = NULL;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $stmt = NULL;
            $temp = NULL;
        }
        return $temp;
    }

    function getTemplates($getFull = FALSE, $pdo = NULL) {
        $temps = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_template";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $temp = new OphthalmologyTemplate();
                $temp->setId($row['id']);
                $temp->setLabel($row['label']);
                $temp->setData((new OphthalmologyTemplateDataDAO())->getTemplateData($row['id'], FALSE, $pdo));
                $temps[] = $temp;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $temps = array();
        }
        return $temps;
    }

}
