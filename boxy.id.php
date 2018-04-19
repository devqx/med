<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/13/17
 * Time: 11:08 AM
 */

if (!isset ($_SESSION)) {
	session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/class.config.main.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PatientDemographDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/ClinicDAO.php";


$hosp = (new ClinicDAO())->getClinic(1)->getName();

$pat = (new PatientDemographDAO())->getPatient($_GET['pid'], false, null, null);
$code = $pat->getId();

//$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/barcode.php?text=$code";
//$imdata = base64_encode(file_get_contents($url));
//$img_ = '<img width="100%" src="data:image/png;base64,' . ($imdata) . '" />';


require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/php-barcode-generator-master/src/BarcodeGenerator.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/php-barcode-generator-master/src/BarcodeGeneratorJPG.php';
$generator = new Picqer\Barcode\BarcodeGeneratorJPG();

$img_ = '<img style="height:100%" src="data:image/png;base64,' . base64_encode( $generator->getBarcode($code, $generator::TYPE_CODE_128)) . '" />';

?>
<link rel="stylesheet" href="/style/def.css" media="print"/>
<link href="/style/bootstrap.css" rel="stylesheet" type="text/css" media="print"/>
<style>
	table {text-align: left}
	@media screen {
		#idCard {zoom: 1;}
		#demo {position: absolute;left: 19mm;top: 33mm;font-size: 3mm;font-family: sans-serif;line-height: 4mm;width:50mm}
		#barcode {position: absolute;top: 55mm;left: 18mm;width: 50mm;height: 11mm;}
		#phot {width: 28mm;position: absolute;left: 70mm;top: 38mm;}
	}
	
	@media print {
		#idCard { zoom:2 }
		#demo {position: absolute;left: 14mm;top: 18mm;font-size: 2.5mm;font-family: sans-serif;line-height: 3mm;width:45mm}
		#barcode {position: absolute;top: 35mm;left: 14mm;width: 40mm;height: 10mm;}
		#phot {width: 20mm;position: absolute;left: 62mm;top: 25mm;}
	}
</style>

<div id="idCard" style="width:88mm;height:56mm;background: url(/img/ID_Card.png?v1) no-repeat;background-size: contain;">
	<div id="demo">
		<?php
		$name = [];
		if(!is_blank($pat->getLname())){
			$name[] = $pat->getLname();
		}
		if(!is_blank($pat->getFname())){
			$name[] = $pat->getFname();
		}
		if(!is_blank($pat->getMname())){
			$name[] = $pat->getMname();
		}?>
		<table border="0" cellpadding="0" cellspacing="0">
			<tr><th valign="top">Name</th><td>&nbsp;</td><td valign="top"><?=implode(" ", $name) ?></td></tr>
			<tr><th>Sex</th><td>&nbsp;</td><td><?= ucwords($pat->getSex() )?></td></tr>
			<tr><th>DoB</th><td>&nbsp;</td><td><?= date(MainConfig::$shortDateFormat, strtotime($pat->getDateOfBirth())) ?></td></tr>
			<tr><th>ID</th><td>&nbsp;</td><td><?= $pat->getId() ?></td></tr>
		</table>
	</div>
	<div id="barcode">
		<?= $img_ ?>
	</div>

	<div id="phot">
		<img src="<?= $pat->getPassportPath() ?>">
	</div>

</div>
<div class="no-print btn-block">
	<a class="pull-right action" target="_blank" href="/pdf.php?page=<?= urlencode($_SERVER['REQUEST_URI']) ?>&noMargins">Print</a>
</div>