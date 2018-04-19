<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/3/15
 * Time: 4:46 PM
 */

class InvoiceLineDAO {
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Invoice.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InvoiceLine.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InvoiceDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            errorLog($e);
        }
    }

    function add($line, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO invoice_line (invoice_id, bill_id) VALUES (".$line->getInvoice()->getId().", ".$line->getBill()->getId().")";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($stmt->rowCount() == 1){
                $line->setId($pdo->lastInsertId());
                return $line;
            }

            return NULL;

        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function get($id, $pdo=NULL){
        $line = new InvoiceLine();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM invoice_line WHERE id = $id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $line->setId($row['id']);
                $line->setBill( (new BillDAO())->getBill($row['bill_id'], TRUE, $pdo) );
                $line->setInvoice( (new InvoiceDAO())->get($row['invoice_id'], $pdo) );
            }else{
                $line=NULL;
            }
            $stmt = NULL;
        }catch (PDOException $E){
            $line = NULL;
        }
        return $line;

    }

    function getLines($invoice, $pdo=NULL){
        $lines = array();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM invoice_line WHERE invoice_id = ".$invoice->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $line = new InvoiceLine();
                $line->setId($row['id']);
                $line->setBill( (new BillDAO())->getBill($row['bill_id'], TRUE, $pdo) );

                $lines[] = $line;
            }
            $stmt = NULL;
        }catch (PDOException $e){
            errorLog($e);
            $lines = [];
        }
        return $lines;

    }
}