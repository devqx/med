<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LabTemplateDataDAO
 *
 * @author pauldic
 */
class OphthalmologyTemplateDataDAO {

    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/OphthalmologyTemplate.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/OphthalmologyTemplateData.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyTemplateDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyTemplateDataDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($data, $pdo = NULL) {
        try {
            $counter=0;
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            foreach ($data as $temp) {
                //$temp = new OphthalmologyTemplateData();
                $sql = "INSERT INTO ophthalmology_template_data (ophthalmology_template_id, label, reference)  VALUES (" . $temp->getOphthalmologyTemplate()->getId() . ", '" . $temp->getLabel() . "', '".$temp->getReference()."')";
                //error_log($sql);
                $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $stmt->execute();
                $counter++;
            }

            if (count($data) !== $counter) {
                $data=[];
            }

            $stmt = NULL;
        } catch (PDOException $e) {
            if ($pdo != null) {
                $pdo->rollBack();
            }
            error_log("PDO Exception>>");
            $stmt = NULL;
            $data = [];
        }
        return $data;
    }

    function update($data, $pdo = NULL){
        try {
            $counter=0;
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $pdo->beginTransaction();
            foreach ($data as $k=>$temp) {
                $sql = "UPDATE ophthalmology_template_data SET label='" . $temp->getLabel() . "', reference='".$temp->getReference()."' WHERE id=". $temp->getId();
                $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $stmt->execute();

                $counter++;
            }
            if (count($data) !== $counter) {
                $pdo->rollBack();
                $data=[null];
            }
            else {
                $pdo->commit();
            }

            $stmt = NULL;
        } catch (PDOException $e) {
            $pdo->rollBack();
            errorLog($e);
            $stmt = NULL;
            $data = [null];
        }
        return $data;
    }

    function getOphthalmologyTemplateDatum($did, $getFull = FALSE, $pdo = NULL) {
        $datum = new OphthalmologyTemplateData();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_template_data WHERE id=" . $did;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                if ($getFull) {
                    $temp = (new OphthalmologyTemplateDAO())->getTemplate($row['ophthalmology_template_id'], $pdo);
                } else {
                    $temp = new OphthalmologyTemplate($row['ophthalmology_template_id']);
                }
                $datum->setId($row['id']);
                $datum->setLabel($row['label']);
                $datum->setOphthalmologyTemplate($temp);
                $datum->setReference($row['reference']);
            } else {
                $datum = NULL;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $stmt = NULL;
            $datum = NULL;
        }
        return $datum;
    }

    function getTemplateData($did, $getFull = FALSE, $pdo = NULL) {
        $data = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_template_data WHERE ophthalmology_template_id=".$did;
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $datum = new OphthalmologyTemplateData();
                if ($getFull) {
                    $temp = (new OphthalmologyTemplateDAO())->getTemplate($row['ophthalmology_template_id'], $pdo);
                } else {
                    $temp = new OphthalmologyTemplate($row['ophthalmology_template_id']);
                }
                $datum->setId($row['id']);
                $datum->setLabel($row['label']);
                $datum->setOphthalmologyTemplate($temp);
                $datum->setReference($row['reference']);
                $data[] = $datum;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            errorLog($e);
            $data = array();
        }
        return $data;
    }
    
    function getAllLabTemplateData($getFull = FALSE, $pdo = NULL) {
        $data = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_template_data";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                
                $datum = new OphthalmologyTemplateData();
                if ($getFull) {
                    $temp = (new OphthalmologyTemplateDAO())->getTemplate($row['ophthalmology_template_id'], $pdo);
                } else {
                    $temp = new OphthalmologyTemplate($row['ophthalmology_template_id']);
                }
                $datum->setId($row['id']);
                $datum->setLabel($row['label']);
                $datum->setOphthalmologyTemplate($temp);
                $datum->setReference($row['reference']);
                $data[] = $datum;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $data = array();
        }
        return $data;
    }

}
