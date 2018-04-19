<?php
/**
 * Description of AdmissionDAO
 *
 * @author pauldic
 */
class AdmissionDAO {

    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Admission.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    //TODO: check for the usages of this, this is not supposed to be like this
    function addAdmission($ad, $pdo = NULL) {
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $pdo->beginTransaction();
            $code = "AD" . generateBillableItemCode('in_patient', $pdo);
            $ad->setCode($code);
            $sql = "INSERT INTO in_patient SET name = '" . $ad->getName() . "', billing_code = '" . $ad->getCode()."'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $insureBI = new InsuranceBillableItem();
            $insureBI->setItem($ad);
            $insureBI->setItemDescription($ad->getName());
            $insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(5, $pdo));

            $clinic = new Clinic();
            $clinic->setId(1);
            $insureBI->setClinic($clinic);

            $insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
            if ($insBI == NULL) {
                $pdo->rollBack();
                $stmt = null;
                return NULL;
            }

            $insureIC = new InsuranceItemsCost();
            $insureIC->setItem($ad);
            $insureIC->setSellingPrice ($ad->getBasePrice());
            $insureSch = new InsuranceScheme();
            $insureSch->setId(1);
            $insureIC->setInsuranceScheme($insureSch);
            $insureIC->setClinic($clinic);
            $insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($insureIC, $pdo);
            if ($insIC == NULL) {
                $pdo->rollBack();
                $stmt = null;
                return NULL;
            }
            if($stmt->rowCount()>0){
                $ad->setId($pdo->lastInsertId());
                $pdo->commit();
            }else{
                $ad=NULL;
                $pdo->rollBack();
            }

            
            $stmt = NULL;
        } catch (PDOException $e) {
            $ad = NULL;
        } catch (Exception $e) {
            $ad = NULL;
        }

        return $ad;
    }

    /*function getAdmissionByCode($code, $pdo = NULL) {
        $adm = new Admission();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM in_patient WHERE billing_code='" . $code."'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $adm->setId($row["id"]);
                $adm->setName($row["name"]);
                $adm->setCode($row['billing_code']);
            } else {
                $adm = null;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $adm = NULL;
        }
        return $adm;
    }*/

    function getAdmission($id, $pdo = NULL) {
        if(is_null($id))return null;
        $adm = new Admission();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM in_patient WHERE id=" . $id;
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $adm->setId($row["id"]);
                $adm->setName($row["name"]);
                $adm->setCode($row['billing_code']);
            } else {
                $adm = null;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $adm = NULL;
        }
        return $adm;
    }

    function getAdmissions($pdo = NULL) {
        $adms = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM in_patient ORDER BY name ASC";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $adm=new Admission();
                    $adm->setId($row["id"]);
                    $adm->setName($row["name"]);
                    $adm->setCode($row['billing_code']);
                $adms[] = $adm;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $adms = NULL;
        }
        return $adms;
    }


    function updateAdmission($adm, $pdo = NULL) {
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "UPDATE in_patient SET name = '" . $adm->getName() ."WHERE id= " . $adm->getId();

            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            $stmt = NULL;
            return $adm;
        } catch (PDOException $e) {
            return NULL;
        }
    }

}
