<?php

//error_reporting(0);
class Bills
{
	// TODO: how do i assign the bill to an insurance company if the patient is
	// on insurance?
	public $BILL_NOT_FOUND = '<br><div class="warning-bar">No billing information found</div>';
	public $TYPE_A_SEARCH_QUERY = '<br><div class="warning-bar">Type a search</div>';
	public $BILL_LIST_HEADER = '<table class="table table-striped table-hover"><thead><tr><th>EMR-ID</th><th>Patient Name</th><th>Sex</th><th>Phone#</th><th class="amount">*Outstanding Amount(?)</th></tr></thead>';

	function updateBillForScheme($sid)
	{
		if (!isset ($_SESSION)) {
			@session_start();
		}
		$staff = $_SESSION ['staffID'];

		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "UPDATE bills SET invoiced = 'yes', receiver='$staff' WHERE billed_to=" . $sid;
		//mark all items as invoiced because the invoice has been printed, hasn't it
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		return true; // not really used, or shall we report success or not?
		// no,the user shouldnt really know we are tracking bills
	}

	function getPatientBillDocument($pid)
	{
		// FIXME:it's not even
//        error_log($pid);
		return '<div class="warning-bar">Bill Document Failed to load. Please contact administrator</div>';
	}

	/***************************
	 * use these function tested and trusted
	 **/
	function getPatientOutstandingSum($pid)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = (new MyDBConnector())->getPDO();
		$pid = trim(escape($pid));
		$sql = "SELECT COALESCE(SUM(amount),0) AS amount FROM bills b LEFT JOIN insurance_schemes s ON s.id=b.billed_to WHERE s.pay_type = 'self' AND patient_id = $pid AND cancelled_on IS NULL";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		return $row ['amount'];
	}
	
	
	function _getPatientPaymentsTotals($id=null, $mode = null, $pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;

		if($id != null){
		$id = trim(escape($id));
		if ($mode != null) {
			//  insurance
			$sql = "SELECT COALESCE(SUM(amount),0) AS amount FROM bills WHERE (transaction_type = 'debit' OR transaction_type = 'discount' OR transaction_type = 'reversal' OR transaction_type = 'write-off' OR transaction_type = 'transfer-debit' /*OR transaction_type = 'refund'*/) AND  billed_to = $id /*AND cancelled_on IS NULL*/";
		} else {
			$sql = "SELECT COALESCE(SUM(amount),0) AS amount FROM bills b LEFT JOIN insurance_schemes ON insurance_schemes.id = b.billed_to WHERE patient_id = $id AND (transaction_type = 'debit' OR transaction_type = 'discount' OR transaction_type = 'reversal' OR transaction_type = 'write-off' OR transaction_type = 'transfer-debit'/* OR transaction_type = 'refund'*/) AND insurance_schemes.pay_type = 'self' /* AND cancelled_on IS NULL */";
		}
		
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		return $row ['amount'];
		}
		return $row['amount'] = 0;
	}
	

	function _getPatientCreditTotals($id=null, $mode = null, $pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
    if($id != null) {
	    $id = trim(escape($id));
	    if ($mode != null) {
		    //  insurance
		    $sql = "SELECT COALESCE(SUM(amount),0) AS amount FROM bills WHERE (transaction_type = 'credit' OR transaction_type = 'refund' OR transaction_type = 'transfer-credit') AND  billed_to = $id /* AND cancelled_on IS NULL*/";
	    } else {
		    $sql = "SELECT COALESCE(SUM(amount),0) AS amount FROM bills b LEFT JOIN insurance_schemes ON insurance_schemes.id = b.billed_to WHERE patient_id = $id AND (transaction_type = 'credit' OR transaction_type = 'refund' OR transaction_type = 'transfer-credit') AND insurance_schemes.pay_type = 'self' /* AND cancelled_on IS NULL */";
	    }
	    $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	    $stmt->execute();
	    $row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
	    return $row ['amount'];
    }
    return $row['amount'] = 0;
	}
	

	function getInsuranceOutstanding($scheme_id=null)
	{
		if($scheme_id != null) {
			$debits = number_format(0 - $this->_getPatientPaymentsTotals($scheme_id, "insurance"), 2);
			$credits = number_format($this->_getPatientCreditTotals($scheme_id, "insurance"), 2);
			return $credits - $debits;
		}
		return $credits = 0;
	}

	function getOutstandingTotalForPatient($pid, $pdo = null)
	{
		$debits = number_format(0 - $this->_getPatientPaymentsTotals($pid, null, $pdo), 2);
		$credits = number_format($this->_getPatientCreditTotals($pid, null, $pdo), 2);
		return $credits - $debits;
	}

}
