<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/14/18
 * Time: 12:39 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';

$code = str_replace("PR", '', $_GET['pcode']);
$code = str_replace("/",'', $code);

require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/php-barcode-generator-master/src/BarcodeGenerator.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/php-barcode-generator-master/src/BarcodeGeneratorJPG.php';
$generator = new Picqer\Barcode\BarcodeGeneratorJPG();


$img_ = '<img style="height:100%; width:30mm;"  src="data:image/png;base64,' . base64_encode( $generator->getBarcode($code, $generator::TYPE_CODE_128)) . '" />';



$pp = (new PrescriptionDAO())->getPrescriptionByCode($_GET['pcode'], true);
$clinic = (new ClinicDAO())->getClinic(1);
?>

<?php foreach ($pp->getData() as $p) { ?>
<html>
<head>
	<script src="/js/jquery-2.1.1.min.js"></script>
	<script src="/js/jquery-migrate-1.2.1.min.js"></script>
	<script type="text/javascript" src="/js/JsBarcode.all.min.js"></script>
	<script src="/assets/jquery-print/jQuery.print.js" type="text/javascript"></script>
	<link href="/style/bootstrap.css" rel="stylesheet" type="text/css" media="print"/>
	<link rel="stylesheet" href="/style/def.css" media="print"/>
	<style>
		@media print {
		#idCard { zoom:2 }
		#barcode {
		position: relative;
		top: 4mm;
		left: 3mm;
			width: 40mm;
			height: 10mm;
		}
		}
		@media screen {
		#idCard {zoom: 1;}
		#barcode {
		position: relative;
		top: 4mm;
		left: 3mm;
			width: 50mm;
			height: 10mm;
		}
		}
		body {
			margin: 0;
			border: 0.264583333mm solid green;
			display: flex;
			justify-content: center;
		}
		.table_style td {
			text-align: left;
			padding: 0 0 3.175mm 5.291666667mm;
		}

		
		.div_print {
			width: 70mm;
			height: auto; /*37.735416667mm; */
			margin-top: 1.735416667mm;
			border-width: 1px;
			border-style: solid;
			border-color: red;
			overflow: -webkit-paged-x;
		}


		.part_dec_print {
			font: 3mm arial;
			text-align: left;
			padding-left: 1.5875mm;
		}

		.p_tag {
			vertical-align: middle;
			padding-top: 1.5875mm;
		}
		
		.qty_print {
			font: 3mm arial;
			text-align: left;
			height: 6.5mm;
			line-height: 6mm;
			padding-left: 0.5mm;
		}
		
		}
		
	</style>
</head>
<body  >
	<div id="idCard"  class="div_print" style="width:40mm;height:149mm;background:  no-repeat;background-size: contain;">
	<div class="part_dec_print">
		<div class="p_tag">
			<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			$c = new Clinic(); ?>
			<?= ($c::$useHeader) ? $clinic->getLogoLebel() : '' ?>
		</div>
	</div>
	
		<div class="part_dec_print">
			<div><span >RX:</span> <span class="qty_print"><?= $_GET['pcode'] ?></span></div>
			<div><span >Patient:</span> <span class="qty_print"><?= $pp->getPatient()->getFullName() ?></span></div>
			<div><span >Drug:</span> <span class="qty_print"><?= $p->getDrug() ? $p->getDrug()->getName() . '('.$p->getGeneric()->getWeight().' ' .$p->getGeneric()->getForm() .')' : 'n/a' ?></span></div>
			<div><span >Note:</span> <span class="qty_print"><?= $p->getComment() ? $p->getComment() : 'n/a' ?></span></div>
			<div><span>Batch:</span><span class="qty_print"><?= $p->getBatch() ? $p->getBatch()->getName() : 'n/a'?></span></div>
			<div><span>Exp Date:</span><span class="qty_print"><?= $p->getBatch()->getExpirationDate() ?> </span></div>
			<div><span>Disp Date:</span><span class="qty_print"><?= date(MainConfig::$dateFormat, strtotime($p->getFilledOn())) ?> </span></div>
			<div><span>Prsc Dr: </span><span class="qty_print"><?= $p->getRequestedBy()->getFullName() ?> </span></div>
			<div><span>Pharm:</span><span class="qty_print"><?= $p->getFilledBy()->getFullname() ?> </span></div>
			<!--</div>-->
			<!--<div></div>-->
			<div id="barcode">
				<?= $img_ ?>
			</div>
		</div>
		
</div>
	<?php } ?>
	<div class="no-print btn-block" style="display: block; margin-top: 20px; margin-left: 30px;">
		<a class="pull-right action" target="_blank" href="/pharmaceuticals/labelPdf.config.php?page=<?= urlencode($_SERVER['REQUEST_URI']) ?>&noMargins"><i class="fa fa-print"></i>Print</a>
	</div>
</body>
</html>


