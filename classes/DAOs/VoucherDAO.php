<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/20/15
 * Time: 10:53 AM
 */

class VoucherDAO {
    private $conn = null;

    function __construct() {
        if(!isset($_SESSION)){@session_start();}
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/Voucher.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/VoucherBatchDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function get($id, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM voucher WHERE id = ".$id;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $v=new Voucher($row['id']);
                $v->setBatch((new VoucherBatchDAO())->get($row['batch_id'], $pdo));
                $v->setCode($row['code']);
                $v->setUsedDate($row['date_used']);
                return $v;
            }
            return NULL;
        }catch(PDOException $e) {
            errorLog($e);
            return NULL;
        }
    }

    function getByCode($code, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM voucher WHERE `code` = '$code'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                return $this->get($row['id'], $pdo);
            }
            return NULL;
        }catch(PDOException $e) {
            errorLog($e);
            return NULL;
        }
    }

    function getByBatch($batch, $pdo=NULL){
        $vs = [];
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM voucher WHERE date_used IS NULL AND batch_id = '$batch'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $vs[] = $this->get($row['id'], $pdo);
            }
            return $vs;
        }catch(PDOException $e) {
            errorLog($e);
            return [];
        }
    }

    function getAllByBatch($batch, $pdo=NULL){
        $vs = [];
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM voucher WHERE batch_id = '$batch'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $vs[] = $this->get($row['id'], $pdo);
            }
            return $vs;
        }catch(PDOException $e) {
            errorLog($e);
            return [];
        }
    }

    function all($pdo=NULL){
        $vs = [];
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM voucher";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $vs[] = $this->get($row['id'], $pdo);
            }
            return $vs;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }

    function byType($type, $pdo=NULL){
        $vs = [];
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;

            $typeBatches = (new VoucherBatchDAO())->getByType($type, $pdo);

            foreach($typeBatches as $t){
                foreach ($this->getAllByBatch($t->getId(), $pdo) as $v) {
                    $vs[] = $v;
                }
            }
            return $vs;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }

    function byTypeAndDate($type=[], $from, $to, $generatorId=null, $page=0, $pageSize=10, $pdo=NULL){
        $vs = [];
        $total = 0;
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $typeBatches = (new VoucherBatchDAO())->getByTypeAndDate($type, $from, $to, $generatorId, $page, $pageSize, $pdo);
            $total = count($typeBatches);
            foreach($typeBatches as $t){
                foreach ($this->getAllByBatch($t->getId(), $pdo) as $v) {$vs[] = $v;}
            }
        }catch (PDOException $e){
            errorLog($e);
            $vs = [];
        }

        $results = (object)null;
        $results->data = $vs;
        $results->total = $total;
        $results->page = $page;

        return $results;
    }

    function unused($pdo=NULL){
        $vs = [];
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM voucher WHERE date_used IS NULL";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $vs[] = $this->get($row['id'], $pdo);
            }
            return $vs;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }

    private function generate(){
        return strtoupper(substr(str_shuffle(MD5(microtime())), 0, 8));

        /*$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $rand = '';
        for ($i = 0; $i < 8; $i++) {
            $rand .= $characters[rand(0, strlen($characters))];
        }

        return $rand;*/
    }
    /**
     * @param $v
     * @param null $pdo
     *
     * generates a new voucher
     * @return object
     */
    function add($v, $pdo=NULL){
//        $v = new Voucher();
        $code = $this->generate();
        $v->setCode( $code );
        $batch = $v->getBatch();

        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO voucher (batch_id, `code`) VALUES ('$batch', '$code')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                return $v;
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }

    }

    function use_($v_id, $pdo=NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "UPDATE voucher SET date_used = NOW() WHERE id=".$v_id;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount() == 1){
                return TRUE;
            }
            return FALSE;
        }catch (PDOException $e){
            errorLog($e);
            return FALSE;
        }
    }

    function findVouchers($filter, $pdo=NULL){
        $vs = [];
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT v.*, b.patient_id AS patient_emr FROM voucher v LEFT JOIN voucher_batch vb ON vb.id=v.batch_id LEFT JOIN bills b ON b.voucher_id=v.id LEFT JOIN patient_demograph dm ON dm.patient_ID=b.patient_id WHERE v.date_used IS NOT NULL AND v.code LIKE '%$filter%' OR b.patient_id LIKE '%$filter%' OR dm.fname LIKE '%$filter%' OR dm.mname LIKE '%$filter%' OR dm.lname LIKE '%$filter%'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $v=new Voucher($row['id']);
                $v->setBatch((new VoucherBatchDAO())->get($row['batch_id'], $pdo));
                $v->setCode($row['code']);
                $v->setUsedDate($row['date_used']);
                $v->setVoucherUser((new PatientDemographDAO())->getPatient($row['patient_emr'], FALSE, $pdo, TRUE));
                $vs[] = $v;
            }
            return $vs;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }
}