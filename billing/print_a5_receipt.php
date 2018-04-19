<?php 
//use the Clinic class that holds the clinic information ( name, address, logo etc )
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT']  .'/classes/DAOs/CurrencyDAO.php';
//get the query var ( bill_id ) available in the browser request url 
$bill_id = $_GET['bill_id'];

//fetch the bill details 
$c = new Clinic();
$bill = (new BillDAO())->getBill($bill_id, true);

//get the clinic headers 
$clinic = ( new ClinicDAO() )->getClinic(1);

//var_export( $bill );

$currency = (new CurrencyDAO())->getDefault();

$curr_symbol = $currency->getSymbolLeft() ;
?>

<!DOCTYPE html>
<html moznomarginboxes mozdisallowselectionprint>
<head>
	<meta charset="UTF-8">
	
	<script src="/js/jquery-2.1.1.min.js"></script>
	<script src="/js/jquery-migrate-1.2.1.min.js"></script>
	<script src="/assets/jquery-print/jQuery.print.js" type="text/javascript"></script>
	<link href="/style/def.css" rel="stylesheet" type="text/css"/>
	<link href="/style/bootstrap.css" rel="stylesheet" type="text/css"/>
	<link href="/style/font-awesome.css" rel="stylesheet" type="text/css"/>
	
	<script src="/assets/blockUI/jquery.blockUI.js"></script>
	<script src="/assets/boxy/js/jquery.boxy.js"></script>
	<link rel="stylesheet" href="/assets/boxy/css/boxy.css">
	<script src="/assets/jquery-number-master/jquery.number.js"></script>
	<script type="text/javascript" src="/assets/select2_2/select2.min.js"></script>
	<link rel="stylesheet" href="/assets/select2_2/select2.css">
	<link href="/assets/blockUI/growl.ui.css" rel="stylesheet" type="text/css"/>
	
	<meta name="viewport" content="width=device-width">

</head>
<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';

$c = new Clinic(); ?>
<?= ($c::$useHeader) ? $clinic->getHeader() : '' ?>


<div class="container">
<div style="text-align: center; font-size: 28px; margin-top: <?= ($c::$useHeader) ? 0 : 2 ?>30px"><h2>Payment Receipt</h2>
</div>
<div style="width:50%;">
<br/>

    <h4>Patient's Name: <span style="float:right"> <?= $bill->getPatient()->getFullname() ?> </span> </h4>
    
    <h4> Transaction Date: <span style="float:right"> <?= $bill->getTransactionDate();?> </span> </h4>

     <h4> Amount in words: <span style="float:right"> <?= ucwords((new toWords($bill->getAmount()))->words) ?> </span> </p>

    <h4> Amount: <span style="float:right"> <?= $curr_symbol.abs($bill->getAmount() );?> </span> </p>

    <h4> Transaction Number:  <span style="float:right"> <?= $bill->getId();?> </span> </p>

    <h4> Payment Method:  <span style="float:right"> <?= $bill->getPaymentMethod()->getName();?> </span> </p>

     <h4> Narration:  <span style="float:right"> <?= $bill->getDescription();?> </span> </h4>

    
    
<center> 
<h4>Served By: <?= $bill->getReceiver()->getFullName();?> </h4>
<h4>Thanks for your patronage!</p>

</center>
</div>
</div>

<div class="no-print btn-block" style="display: block; margin-left: 120px;margin-top:20px;">
		<a class="action btn btn-primary" target="_blank" href="/billing/a5Pdf.config.php?page=<?= urlencode($_SERVER['REQUEST_URI']) ?>&noMargins"><i class="fa fa-print"></i>Print</a>
	</div>
</body>
</html>







