<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/2/15
 * Time: 1:43 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';

$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();

$bil = new Bill();
$bil->setPatient((new PatientDemograph(28, $pdo)));
$bil->setDescription("Test Billing ...");
$bil->setPriceType('selling_price');

$bil->setItem(getItem('PR00006', $pdo));
$bil->setSource((new BillSourceDAO())->findSourceById(8, $pdo));
$bil->setTransactionType("credit");

//$item = (new InsuranceItemsCostDAO())->getItemPricesByCode($procedure->getProcedure()->getCode(), $procedure->getPatient()->getId(), true, $pdo);
$bil->setAmount(9999);
$bil->setPriceType('selling_price');
$bil->setDiscounted(null);
$bil->setDiscountedBy(null);

//$patientScheme = (new InsuranceDAO())->getInsurance($procedure->getPatient()->getId(), false, $pdo)->getScheme();
$bil->setClinic(new Clinic(1));
$bil->setBilledTo(new InsuranceScheme(1));
$bil->setReferral(null);
//$bil->setCostCentre((new ServiceCenterDAO())->get($service_centre_id, $pdo) ? (new ServiceCenterDAO())->get($service_centre_id, $pdo)->getCostCentre() : null);
$bil->setCostCentre(null);

$bill = (new BillDAO())->addBill($bil, 1, $pdo);
exit('.....');

?>
<table>
	<?php
	while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
		$data = [];
		foreach(explode(',',$row['ids']) as $dId){
			$data[] = (new DrugSuperGenericData())->setDrugGeneric( new DrugGeneric($dId) );
		}
		(new SuperGeneric())->setName($row['name'])->setData($data)->add($pdo);
		?>
		<tr>
			<td><?= $row['name'] ?></td>
			<td><?= $row['ids'] ?></td>
		</tr>
	<?php } ?>
</table>
<?php
$pdo->commit();
exit;
$options = getTypeOptions('type', 'vital_sign');
$data = (new ClinicalTaskComboDAO())->get(1);
exit(json_encode($data));
@ob_end_clean();
$refinedBlob = (file_get_contents('/tmp/signature-signature.png'));
//$refinedBlob = bin2hex(file_get_contents('/tmp/signature-original.png'));
//
////$content = ;
$_SERVER['DOCUMENT_ROOT'] = '/home/robot/Projects/WebProjects/medicplus/euracare';

//$pdo->beginTransaction();

//$pdo->commit();
//exit('<img src="data:image/png;base64,'.base64_encode($refinedBlob).'">');
//file_put_contents('/tmp/noise2.png', $refinedBlob);

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SignatureDAO.php';
$data = (new SignatureDAO())->get(2);
@ob_end_clean();
//$blob = (! get_magic_quotes_gpc ()) ? stripslashes($data->getBlob()) : $data->getBlob();
exit('<img src="data:image/png;base64,' . base64_encode($data->getBlob()) . '">');

require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

exit(pluralize('', 10));


$date = date_create(date("Y-m-d", time()));
exit($_SERVER['HTTP_HOST']);
exit(json_encode($_SERVER));
date_sub($date, date_interval_create_from_date_string("40 weeks"));
exit(date_format($date, "Y-m-d"));
//require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
//require_once $_SERVER['DOCUMENT_ROOT'] . '/api/antenatal_vars.php';
//
//echo get_index_by_value("Fifth gravida (G5)", $gravida);

//exit(json_encode(checkDateInRange('2015-11-25 15:37:34')));

//$date1 = new DateTime('2015-10-12');
//$date2 = new DateTime();
//
//exit(json_encode( $date2->diff($date1) ));


//$number = 0;
//exit(number_format($number, 2));
//exit((new toWords(($number)))->words);

?>
<div class="container">
	<div class="row row-offcanvas row-offcanvas-left">
		<div class="visible-sm visible-md visible-lg">
			<nav class="navbar navbar-default" role="navigation">
				<ul class="nav navbar-nav">
					<li class="active"><a href="#">Link</a></li>
					<li><a href="#">Link</a></li>
					<li><a href="#">Link</a></li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="#">Action</a></li>
							<li><a href="#">Another action</a></li>
							<li><a href="#">Something else here</a></li>
							<li class="divider"></li>
							<li class="dropdown-header">Nav header</li>
							<li><a href="#">Separated link</a></li>
							<li><a href="#">One more separated link</a></li>
						</ul>
					</li>
				</ul>
			</nav>
		</div>

		<div class=" visible-xs">
			<div class="navbar navbar-default" role="navigation">
				<div class="container">
					<div class="navbar-header">
						<a class="navbar-brand" href="index.php" title="Menu 2">Menu 2</a>
						<button type="button" class="navbar-toggle" data-toggle="offcanvas" data-target=".navbar-collapse">
							<span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span></button>

					</div>
				</div>
			</div>
			<div class="col-xs-6 col-sm-3 sidebar-offcanvas showhide navbar-collapse" id="sidebar" role="navigation" style="">
				<div class="sidebar-nav">
					<ul class="nav navbar-nav">
						<li class="active"><a href="#">Link</a></li>
						<li><a href="#">Link</a></li>
						<li><a href="#">Link</a></li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li><a href="#">Action</a></li>
								<li><a href="#">Another action</a></li>
								<li><a href="#">Something else here</a></li>
								<li class="divider"></li>
								<li class="dropdown-header">Nav header</li>
								<li><a href="#">Separated link</a></li>
								<li><a href="#">One more separated link</a></li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-sm-12">
			<div class="row">
				<div class="jumbotron">
					<h2>Off-canvas Sidebar Example</h2>
					<p>This is an example to show the potential of an offcanvas layout pattern in Bootstrap. Try some
						responsive-range viewport sizes to see it in action.
						
						<?php
						function barcodeImg($text)
						{
							$url = ($_SERVER['HTTPS'] ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/barcode.php?text=$text";
							$imdata = base64_encode(file_get_contents($url));
							return "<img src='data:image/png;base64,$imdata'>";
						}
						
						echo barcodeImg('08073207201');
						?>
					</p>
				</div>
				<div class="row">
					<div class="col-6 col-sm-6 col-lg-4">
						<h2>Heading</h2>
						<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris
							condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod.
							Donec sed odio dui. </p>
						<p><a class="btn btn-default" href="#">View details »</a></p>
					</div><!--/span-->
					<div class="col-6 col-sm-6 col-lg-4">
						<h2>Heading</h2>
						<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris
							condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod.
							Donec sed odio dui. </p>
						<p><a class="btn btn-default" href="#">View details »</a></p>
					</div><!--/span-->
					<div class="col-6 col-sm-6 col-lg-4">
						<h2>Heading</h2>
						<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris
							condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod.
							Donec sed odio dui. </p>
						<p><a class="btn btn-default" href="#">View details »</a></p>
					</div><!--/span-->
					<div class="col-6 col-sm-6 col-lg-4">
						<h2>Heading</h2>
						<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris
							condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod.
							Donec sed odio dui. </p>
						<p><a class="btn btn-default" href="#">View details »</a></p>
					</div><!--/span-->
					<div class="col-6 col-sm-6 col-lg-4">
						<h2>Heading</h2>
						<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris
							condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod.
							Donec sed odio dui. </p>
						<p><a class="btn btn-default" href="#">View details »</a></p>
					</div><!--/span-->
					<div class="col-6 col-sm-6 col-lg-4">
						<h2>Heading</h2>
						<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris
							condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod.
							Donec sed odio dui. </p>
						<p><a class="btn btn-default" href="#">View details »</a></p>
					</div><!--/span-->
				</div><!--/row-->
			</div>
		</div>
	</div>

	<hr>

	<a href="http://bootply.com/92159">Edit on Bootply</a> - <a href="http://bootply.com/templates" target="ext">Bootstrap
		Templates</a>

	<hr>

</div>
