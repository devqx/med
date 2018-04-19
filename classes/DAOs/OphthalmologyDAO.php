<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Lab
 *
 * @author pauldic
 */
class OphthalmologyDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Ophthalmology.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/OphthalmologyCategory.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/OphthalmologyTemplate.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyCategoryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyTemplateDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($ophthalmology, $price, $pdo = NULL)
    {
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $pdo->beginTransaction();
            $bCode = "OP" . generateBillableItemCode('ophthalmology', $pdo);
            $ophthalmology->setCode($bCode);
            $sql = "INSERT INTO ophthalmology (billing_code, `name`, category_id, ophthalmology_template_id, unit_symbol, reference, hospid) VALUES "
                . "('" . $ophthalmology->getCode() . "', '" . escape($ophthalmology->getName()) . "', '" . $ophthalmology->getCategory()->getId() . "', '" . $ophthalmology->getTemplate()->getId() . "', '" . escape($ophthalmology->getUnitSymbol()) . "', '" . escape($ophthalmology->getReference()) . "', " . $ophthalmology->getHospital()->getId() . ")";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount() == 1) {
                $ophthalmology->setId($pdo->lastInsertId());
                $insureBI = new InsuranceBillableItem();
                $insureBI->setItem($ophthalmology);
                $insureBI->setItemDescription($ophthalmology->getDescription());
                $insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(13, $pdo));
                $insureBI->setClinic($ophthalmology->getHospital());
                $insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
                if ($insBI == NULL) {
                    $pdo->rollBack();
                    $stmt = null;
                    return NULL;
                }

                $insureIC = new InsuranceItemsCost();
                $insureIC->setItem($ophthalmology);
                $insureIC->setSellingPrice ($price);
                $insureSch = new InsuranceScheme();
                $insureSch->setId(1);
                $insureIC->setInsuranceScheme($insureSch);
                $insureIC->setClinic($ophthalmology->getHospital());
                $insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($insureIC, $pdo);
                if ($insIC == NULL) {
                    $pdo->rollBack();
                    $stmt = null;
                    return NULL;
                }
                $pdo->commit();
                return $ophthalmology;
            } else {
                $pdo->rollBack();
                $ophthalmology = NULL;
            }

            $stmt = null;
        } catch (PDOException $e) {
            $stmt = NULL;
            $ophthalmology = NULL;
        }
        return $ophthalmology;
    }

    function get($lid, $getFull = FALSE, $pdo = NULL)
    {
        $ophthalmology = new Ophthalmology();

        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology WHERE id=" . $lid;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $ophthalmology->setId($row['id']);
                $ophthalmology->setCode($row['billing_code']);
                $ophthalmology->setName($row['name']);
                if ($getFull) {
                    $cfg = (new OphthalmologyCategoryDAO())->getCategory($row['category_id'], $pdo);
                    $clinic = (new ClinicDAO())->getClinic($row['hospid'], FALSE);
                    $temp = (new OphthalmologyTemplateDAO())->getTemplate($row['ophthalmology_template_id'], FALSE);
                } else {
                    $cfg = new OphthalmologyCategory();
                    $cfg->setId($row['category_id']);
                    $clinic = new Clinic();
                    $clinic->setId($row['hospid']);
                    $temp=new OphthalmologyTemplate($row['ophthalmology_template_id']);
                }
                $ophthalmology->setCategory($cfg);
                $ophthalmology->setTemplate($temp);
                $ophthalmology->setUnitSymbol($row['unit_symbol']);
                $ophthalmology->setReference($row['reference']);
                $ophthalmology->setDescription( (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($row['billing_code'])->getItemDescription() );
                $ophthalmology->setHospital($clinic);
                $ophthalmology->setBasePrice( (new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($row['billing_code'], $pdo) );

            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $ophthalmology = NULL;
        }
        return $ophthalmology;
    }


    function getByCode($iCode, $getFull = FALSE, $pdo = NULL)
    {
        $ophthalmology = new Ophthalmology();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology WHERE billing_code='" . $iCode . "'";
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                // return (object)$row;
                $ophthalmology->setId($row['id']);
                $ophthalmology->setCode($row['billing_code']);
                $ophthalmology->setName($row['name']);
                if ($getFull) {
                    $cfg = (new OphthalmologyCategoryDAO())->getCategory($row['category_id'], $pdo);
                    $clinic = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
                    $temp = (new OphthalmologyTemplateDAO())->getTemplate($row['ophthalmology_template_id'], $pdo);
                } else {
                    $cfg = new OphthalmologyCategory();
                    $cfg->setId($row['category_id']);
                    $clinic = new Clinic();
                    $clinic->setId($row['hospid']);
                    $temp=new OphthalmologyTemplate($row['ophthalmology_template_id']);
                }
                $ophthalmology->setCategory($cfg);
                $ophthalmology->setTemplate($temp);
                $ophthalmology->setUnitSymbol($row['unit_symbol']);
                $ophthalmology->setReference($row['reference']);
                //purposely left out the description set- call. it is recursive if it's included
                //since getItem in utils.php, calls this function again
//                $lab->setDescription(  );
                $ophthalmology->setHospital($clinic);
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $ophthalmology = NULL;
        }
        return $ophthalmology;
    }

    function update($ophthalmology, $price, $pdo = NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $pdo->beginTransaction();
            $sql = "UPDATE ophthalmology SET `name` = '".escape($ophthalmology->getName())."', category_id=".$ophthalmology->getCategory()->getId().", ophthalmology_template_id=".$ophthalmology->getTemplate()->getId().", unit_symbol='".escape($ophthalmology->getUnitSymbol())."', reference='".escape($ophthalmology->getReference())."' WHERE id = ". $ophthalmology->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount() == 1 || $stmt->rowCount()== 0) {
                $insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($ophthalmology->getCode(), TRUE, $pdo);
                $insureBI->setItemDescription($ophthalmology->getName());
                $insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(13, $pdo));
                $insureBI->setClinic($ophthalmology->getHospital());
                $insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);

                if ($insBI == NULL) {
                    $pdo->rollBack();
                    $stmt = null;
                    return NULL;
                }
                $insureIC = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($ophthalmology->getCode(), 1, FALSE, FALSE, $pdo);
                $insureIC->selling_price = ($price);
                $insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);

                if ($insIC == NULL) {
                    error_log("Something is not right");
                    $pdo->rollBack();
                    $stmt = null;
                    return NULL;
                }
                $pdo->commit();
                return $ophthalmology;
            } else {
                error_log("Is there problem");
                $pdo->rollBack();
                $ophthalmology = NULL;
            }

            $stmt = null;
        } catch (PDOException $e) {
            errorLog($e);
            $stmt = NULL;
            $ophthalmology = NULL;
        }
        return $ophthalmology;
    }

    function all($getFull = FALSE, $pdo = NULL)
    {
        $labs = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $lab = $this->get($row['id'], $getFull, $pdo);
                $labs[] = $lab;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $labs = array();
        }
        return $labs;
    }

    function findTests($search, $getFull = FALSE, $pdo = NULL)
    {
        $array = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT c.* FROM ophthalmology c LEFT JOIN ophthalmology_category l ON c.category_id=l.id WHERE c.name LIKE '%$search%' OR l.name LIKE '%$search%'";
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $ophthalmology = $this->get($row['id'], $getFull, $pdo);
                $array[] = $ophthalmology;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $array = array();
        }
        return $array;
    }
}
