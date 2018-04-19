<?php
/**
 * Created by PhpStorm.
 * User: nnamdi
 * Date: 4/16/17
 * Time: 8:26 PM
 */
class EstimatedBillLineDAO{

    private $conn = null;

    function __construct()
    {
        try {
            @session_start();
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/EstimatedBillLine.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/EstimatedBills.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AntenatalEnrollment.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/BillSource.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/EstimatedBillsDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/EstimatedBillLineDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAntenatalUsagesDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/protect.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function getEstimatedBillLineById($es_id,$pdo=null){

        try {
            $pdo = $pdo === null ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM estimated_bill_lines WHERE estimated_bill_id=".$es_id;

            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $data = [];
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $data[] = $this->getEstimatedBillLines($row['id'],$pdo);
            }
            return $data;
        } catch (PDOException $e) {
            errorLog($e);
            return [];
        }

    }


    function getEstimatedBillLines($eid, $pdo = null)
    {
        $bill_line = new EstimatedBillLine();
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM estimated_bill_lines WHERE id=" . $eid;

            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $bill_line->setId($row['id']);
                $bill_line->setServiceDescription($row['service_description']);
                $bill_line->setUnitPrice($row['unit_price']);
                $bill_line->setItemDescription($row['item_description']);
                $bill_line->setItemCode($row['item_code']);
                $bill_line->setQuantity($row['quantity']);
                $bill_line->setEstimatedBillId($row['estimated_bill_id']);
            }
            $stmt = null;
        } catch (PDOException $e) {
            $bill_line = null;
        }
        return $bill_line;
    }



    function addEsBillLines($pref_lines, $pdo = null)
    {
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO estimated_bill_lines (estimated_bill_id,services_id, unit_price, item_description,item_cost_id,service_description,item_code,item_insurance_id,quantity)  VALUES ";
             $data = [];
            foreach ($pref_lines as $pref_line){
             $data[] =  "(".$pref_line->estimated_bill_id.",".$pref_line->item_group_category_id.",".floatval($pref_line->selling_price).",'".$pref_line->item_description."',".$pref_line->id.",'".$pref_line->service_description."','".$pref_line->item_code."',".$pref_line->insurance_scheme_id.",".$pref_line->quantity.")";
            }
            $sql .= implode(",",$data);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            //$bill_lines->setId($pdo->lastInsertId());
            $stmt = null;
        } catch (PDOException $e) {
            errorLog($e);
            return null;
        }
        return $pref_lines;
    }

    public function delete($line, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $pdo->beginTransaction();
            $sql = "DELETE FROM estimated_bill_lines WHERE id =".$line->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($stmt->rowCount()>=0){
                $pdo->commit();
                return TRUE;
            }

            $pdo->rollBack();
            return FALSE;
        }catch(PDOException $e) {
            $pdo->rollBack();
            return FALSE;
        }
    }


}