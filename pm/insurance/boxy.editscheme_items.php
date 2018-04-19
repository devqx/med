<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/28/16
 * Time: 1:03 PM
 */

$sid = $_GET['sid']; //Retrieve the id of this insurance scheme
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Insurer.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/InsuranceScheme.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Badge.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Procedure.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsurerDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsuranceSchemeDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsuranceItemsCostDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsuranceTypeDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/BadgeDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/class.config.main.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/ERPHandler.php";

$providers = (new InsurerDAO())->getInsurers();
$schemeDAO = new InsuranceSchemeDAO();
$scheme = $schemeDAO->get($sid);
$dao = new InsuranceItemsCostDAO();
$itemCosts = $dao->getInsuredItemsCostsByScheme($sid);
$badges = (new BadgeDAO())->all();
?>
<link rel="stylesheet" href="/style/insurance_items.css?i=<?=rand(9, 10000000)?>" media="screen">
<section style="min-width: 1000px;width: 1000px; max-width:1000px">
<?php if (sizeof($itemCosts) === 0) {?>
	<div class="alert-box notice">No items insured under this scheme.
		<!--<a href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=CO,DR,LA,VC,PR,SC,RT,IT,AD,OP,DT,NS,WR,IP,ME,AP,PGD&sid=<?/*= $scheme->getId() */?>&hid=<?/*= $scheme->getHospital()->getId() */?>', {title:'Add Insured Items'})">Add
			items to insure</a>-->
	</div>
<?php } ?>
<div id="insuredServices">
	<ul class="service_list_box" style="list-style-type: none;">
		<li class="ui-bar-c menu-head"><h5>Insured Consultations
				<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=CO&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Insured Consultancy Services'})" title="Add a non-insured consultancy service to this scheme"><i class='icon-plus-sign'></i></a>
				<span class="pull-right">CoPay <a data-name="CO" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('CO', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(3)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(3)->getId()) ?></a> | </span>
			</h5></li>
		<li class="contained_items">
			<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
				<?php foreach ($itemCosts as $itc) {
					// $itc = new InsuranceItemsCost();
					if (strpos($itc->item_code, "CO") !== false) {
						?>
						<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
							<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
							<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
						</li>
					<?php }
				} ?>
			</ul>
		</li>
		<li class="ui-bar-c menu-head"><h5>Insured Drugs
				<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=DR0&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Insured Drugs'})" title="Add a non-insured drug to this scheme"><i class='icon-plus-sign'></i></a>
				<span class="pull-right">CoPay <a data-name="DR" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('DR', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(2)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(2)->getId()) ?></a> | </span>
			</h5></li>
		<li class="contained_items">
			<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
				<?php foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "DR0") !== false) {
						?>
						<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
							<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
							<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
						</li>
					<?php }
				}?>
			</ul>
		</li>
		<li class="ui-bar-c menu-head"><h5>Insured Labs
				<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=LA&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Insured Lab Services'})" title="Add a non-insured lab to this scheme"><i class='icon-plus-sign'></i></a>
				<span class="pull-right">CoPay <a data-name="LA" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('LA', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(1)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(1)->getId()) ?></a> | </span>
			</h5></li>
		<li class="contained_items">
			<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
				<?php 
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "LA") !== false) {
						?>
						<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
							<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
							<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
						</li>
					<?php }
				}
				?>
			</ul>
		</li>
		
		<!--  Vaccine section begins -->
		<?php if(is_dir($_SERVER['DOCUMENT_ROOT']."/immunization")) { ?>
			<li class="ui-bar-c menu-head"><h5>Insured Vaccines
					<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=VC&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Insured Vaccines'})" title="Add a non-insured vaccine to this scheme"><i class='icon-plus-sign'></i></a>
					<span class="pull-right">CoPay <a data-name="VC" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('VC', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(6)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(6)->getId()) ?></a> | </span>
				</h5></li>
			<li class="contained_items">
			<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
				<?php 
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "VC") !== false) {
						?>
						<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
							<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
							<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
						</li>
					<?php }
				}?>
			</ul>
			</li><?php } ?> <!-- Vaccine section end -->
		
		<li class="ui-bar-c menu-head"><h5>Insured Radiology
				<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=SC&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Insured Radiology'})" title="Add a non-insured radiology to this scheme"><i class='icon-plus-sign'></i></a>
				<span class="pull-right">CoPay <a data-name="SC" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('SC', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(7)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(7)->getId()) ?></a> | </span>
			</h5></li>
		<li class="contained_items">
			<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
				<?php 
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "SC") !== false) {
						?>
						<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
							<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
							<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
						</li>
					<?php }
				}
				?>
			</ul>
		</li>
		<li class="ui-bar-c menu-head"><h5>Insured Non-Drug Consumables
				<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=IT&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Insured Non-Drug Consumables'})" title="Add a non-insured Non-Drug Consumables to this scheme"><i class='icon-plus-sign'></i></a>
				<span class="pull-right">CoPay <a data-name="IT" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('IT', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(11)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(11)->getId()) ?></a> | </span>
			</h5></li>
		<li class="contained_items">
			<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
				<?php 
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "IT") !== false) {
						?>
						<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
							<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
							<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
						</li>
					<?php }
				}
				?>
			</ul>
		</li>
		<li class="ui-bar-c menu-head"><h5>Insured Procedures
				<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=PR&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Insured Procedures'})" title="Add a non-insured Procedure to this scheme"><i class='icon-plus-sign'></i></a>
				<span class="pull-right">CoPay <a data-name="PR" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('PR', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(8)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(8)->getId()) ?></a> | </span>
			</h5></li>
		<li class="contained_items">
			<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
				<?php 
				$_ = new Procedure();
				$desc = $_::$desc;
				foreach ($itemCosts as $itc) {
					//                        $itc = new InsuranceItemsCost();
					if (strpos($itc->item_code, "PR") !== false) {
						?>
						<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
							<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
							<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
						</li>
					<?php }
				}	?>
			</ul>
		</li>
		<li class="ui-bar-c menu-head"><h5>Insured Beds/Rooms
				<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=RT0&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Insured Admission Beds/Rooms'})" title="Add a non-insured bed/room to this scheme"><i class='icon-plus-sign'></i></a>
				<span class="pull-right">CoPay <a data-name="RT" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('RT', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(5)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(5)->getId()) ?></a> | </span>
			</h5></li>
		<li class="contained_items">
			<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
				<?php 
				foreach ($itemCosts as $itc) {
					if (substr($itc->item_code, 0, 2) === 'RT') {
						?>
						<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
							<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
							<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
						</li>
					<?php }
				}?>
			</ul>
		</li>
		<li class="ui-bar-c menu-head"><h5>Admission Fees
				<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=AD&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Insured Admission Fees'})" title="Add a non-insured admission fee to this scheme"><i class='icon-plus-sign'></i></a>
				<span class="pull-right">CoPay <a data-name="AD" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('AD', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(5)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(5)->getId()) ?></a> | </span>
			</h5></li>
		<li class="contained_items">
			<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
				<?php 
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "AD") !== false) {
						?>
						<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
							<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
							<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
						</li>
					<?php }
				}?>
			</ul>
		</li>
		<li class="ui-bar-c menu-head"><h5>Nursing Services
				<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=NS&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Insured Nursing Services'})" title="Add a non-insured nursing service to this scheme"><i class='icon-plus-sign'></i></a>
				<span class="pull-right">CoPay <a data-name="NS" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('NS', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(16)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(16)->getId()) ?></a> | </span>
			</h5></li>
		<li class="contained_items">
			<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
				<?php 
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "NS") !== false) {
						?>
						<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
							<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
							<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
						</li>
					<?php }
				}?>
			</ul>
		</li>
		<li class="ui-bar-c menu-head"><h5>Ward Fees
				<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=WR&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Insured Ward Fees'})" title="Add a non-insured Ward fee to this scheme"><i class='icon-plus-sign'></i></a>
				<span class="pull-right">CoPay <a data-name="WR" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('WR', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(17)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(17)->getId()) ?></a> | </span>
			</h5></li>
		<li class="contained_items">
			<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
				<?php 
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "WR") !== false) {
						?>
						<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
							<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
							<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
						</li>
					<?php }
				}?>
			</ul>
		</li>
		<li class="ui-bar-c menu-head"><h5>Ophthalmology
				<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=OP&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Insured Ophthalmology Charges'})" title="Add a non-insured Ophthalmology charge to this scheme"><i class='icon-plus-sign'></i></a>
				<span class="pull-right">CoPay <a data-name="OP" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('OP', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(13)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(13)->getId()) ?></a> | </span>
			</h5></li>
		<li class="contained_items">
			<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
				<?php 
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "OP") !== false) {
						?>
						<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
							<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
							<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
						</li>
					<?php }
				}?>
			</ul>
		</li>
		<?php if(is_dir($_SERVER['DOCUMENT_ROOT']."/physiotherapy")){ ?>
			
			<li class="ui-bar-c menu-head"><h5>Physiotherapy Items
					<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=IP&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Insured Physiotherapy Charges'})" title="Add a non-insured Physiotherapy item charge to this scheme"><i class='icon-plus-sign'></i></a>
					<span class="pull-right">CoPay <a data-name="IP" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('IP', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(20)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(20)->getId()) ?></a> | </span>
				</h5></li>
			<li class="contained_items">
				<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
					<?php 
					foreach ($itemCosts as $itc) {
						if (strpos($itc->item_code, "IP") !== false) {
							?>
							<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
								<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
								<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
							</li>
						<?php }
					}?>
				</ul>
			</li>
		<?php } ?>
		
		
		<!--  Dentistry section begin -->
		<?php if(is_dir($_SERVER['DOCUMENT_ROOT']."/dentistry")) {?>
			<li class="ui-bar-c menu-head"><h5>Dentistry
					<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=DT&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Insured Dentistry Charges'})" title="Add a non-insured Dentistry charge to this scheme"><i class='icon-plus-sign'></i></a>
					<span class="pull-right">CoPay <a data-name="DT" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('DT', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(14)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(14)->getId()) ?></a> | </span>
				</h5></li>
			<li class="contained_items">
				<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
					<?php 
					foreach ($itemCosts as $itc) {
						if (strpos($itc->item_code, "DT") !== false) {
							?>
							<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
								<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
								<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
							</li>
						<?php }
					}?>
				</ul>
			</li>
		
		<?php } ?> <!-- Dentistry section end -->
		
		
		<!-- Medical Report Section begin -->
		
		<li class="ui-bar-c menu-head"><h5>Medical Report
				<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=ME&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Insured Medical Report Charges'})" title="Add a non-insured Medical Report charge to this scheme"><i class='icon-plus-sign'></i></a>
				<span class="pull-right">CoPay <a data-name="ME" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('ME', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(12)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(12)->getId()) ?></a> | </span>
			</h5></li>
		<li class="contained_items">
			<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
				<?php 
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "ME") !== false) {
						?>
						<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
							<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
							<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
						</li>
					<?php }
				}?>
			</ul>
		</li> <!-- Medical Report section ended -->
		
		<!-- Antenatal package section start -->
		<?php if(is_dir($_SERVER['DOCUMENT_ROOT']."/antenatal")) {?>
			<li class="ui-bar-c menu-head"><h5>Insured Antenatal Packages
					<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=AP&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Antenatal Packages'})" title="Add a non-insured antenatal packages to this scheme"><i class='icon-plus-sign'></i></a>
					<span class="pull-right">CoPay <a data-name="AP" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('AP', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(15)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(15)->getId()) ?></a> | </span>
				</h5></li>
			<li class="contained_items">
				<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
					<?php 
					foreach ($itemCosts as $itc) {
						if (strpos($itc->item_code, "AP") !== false) {
							?>
							<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
								<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
								<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
							</li>
						<?php }
					} ?>
				</ul>
			</li> <?php } ?> <!-- Antenatal Package section ended -->
		
		
		<?php if(is_dir($_SERVER['DOCUMENT_ROOT']."/ivf")) { ?><li class="ui-bar-c menu-head"><h5>Insured Genetic Labs
				<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=PGD&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Genetics Labs'})" title="Add a non-insured Genetic Lab to this scheme"><i class='icon-plus-sign'></i></a>
				<span class="pull-right">CoPay <a data-name="PGD" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('PGD', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(21)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(21)->getId()) ?></a> | </span>
			</h5></li>
			<li class="contained_items">
				<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
					<?php 
					foreach ($itemCosts as $itc) {
						if (strpos($itc->item_code, "PGD") !== false) {
							?>
							<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
								<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
								<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
							</li>
						<?php }
					}?>
				</ul>
			</li>
		<?php }?>
		<li class="ui-bar-c menu-head"><h5>Packages/Promos
				<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=PKG&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit Promos/Packages'})" title="Add a non-insured Package to this scheme"><i class='icon-plus-sign'></i></a>
				<span class="pull-right">CoPay <a data-name="PKG" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('PKG', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(24)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(24)->getId()) ?></a> | </span>
			</h5></li>
			<li class="contained_items">
				<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
					<?php
					foreach ($itemCosts as $itc) {
						if (strpos($itc->item_code, "PKG") !== false) {
							?>
							<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
								<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
								<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
							</li>
						<?php }
					}?>
				</ul>
			</li>
		
		<li class="ui-bar-c menu-head"><h5>Insured DRGs
				<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/insurance/boxy_addInsuredItem.php?show=DRT&sid=<?= $scheme->getId() ?>&hid=<?= $scheme->getHospital()->getId() ?>', {title:'Edit DRGs'})" title="Add a non-insured DRG to this scheme"><i class='icon-plus-sign'></i></a>
				<span class="pull-right">CoPay <a data-name="DRT" data-pk="<?= $scheme->getId() ?>" class="editable" href=""><?= (new InsuranceItemsCostDAO())->getCoPayPriceByFamily('DRT', $scheme->getId()) ?></a></span><span class="pull-right">Receivable Acct Id: <a data-name="<?= (new BillSourceDAO())->findSourceById(25)->getId() ?>" data-pk="<?= $scheme->getId() ?>" class="editable2" href=""><?= (new InsuranceSchemeDAO())->getSchemeReceivableAccountBySource($scheme->getId(), (new BillSourceDAO())->findSourceById(25)->getId()) ?></a> | </span>
			</h5></li>
			<li class="contained_items">
				<ul class="service_list_box" style="list-style-type: none;margin: 3px 0;">
					<?php
					foreach ($itemCosts as $itc) {
						if (strpos($itc->item_code, "DRT") !== false) {
							?>
							<li class='tag' style='/*display: inline-block;min-width: 110px;margin: 0 2px;*/'><?= $itc->item_description ?>
								<a href="javascript:void(0)" data-name="priceEdit" data-id="<?= $itc->id ?>"><i class='icon-edit'></i></a>
								<a href="javascript:;" data-class="removeItem" data-id="<?= $itc->id ?>"><i class="icon-remove-sign"></i></a>
							</li>
						<?php }
					}?>
				</ul>
			</li>
	</ul>
</div>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('a[data-class="removeItem"][data-id]').live('click', function (e) {
			var $this = $(this);
			item = $this;
			if (e.handled != true) {
				Boxy.ask('Are you sure to remove this item from the insurance coverage?', ['Yes', 'No'], function (answer) {
					if (answer == "Yes") {
						$.post('/api/scheme_remove_item.php', {id: $this.data("id")}, function (s) {
							var data = s.split(":");
							if (data[0] == "error") {
								Boxy.alert(data[1]);
							} else {
								$this.parent().remove();
							}
						});
					}
				});
				e.handled = true;
			}
		});
		$('a.editable').editable({
			url: '/api/co_pay.php',
			mode: 'inline',
			tpl: '<input type="text" min="0" max="100" step="1">',
			validate: function (value) {
				if (isNaN(value) || value / 1 > 100 || value / 1 < 0) {
					return 'Invalid value';
				}
			},
			title: 'Set new co-pay amount (in percentage)',
			success: function (response, newValue) {
				response = JSON.parse(response);
				return {newValue: response.newValue};
			},
			error: function (response, newValue) {
				return "Failed to reach server"
			},
			display: function (value) {
				$(this).text(Number(value).toFixed(1) + '%');
			}
		});
		$('a.editable2').editable({
			url: '/api/receivable_account.php',
			mode: 'inline',
			tpl: '<input type="text" min="1" step="1">',
			validate: function (value) {
				if (isNaN(value)) {
					return 'Invalid value';
				}
			},
			title: 'Set the Receivable Account Id',
			emptytext: 'Not Set',
			success: function (response, newValue) {
				response = JSON.parse(response);
				return {newValue: response.newValue};
			},
			error: function (response, newValue) {
				return "Failed to reach server"
			}
		});
		
		$('button[data-name="priceSave"]').click(function () {
			block = $(this).prop("id").split("_")[0];
			idd = $(this).prop("id").split("_")[2];

			var coverageType = "&coverage_type=" + $("#" + block + "_type_" + idd).val();
			var capitation = "&capitated=" + ($("#" + block + "_capitated_" + idd).is(":checked") ? "true" : "false");

			if (parseFloat($("#" + block + "_" + idd).val()) === parseFloat($(this).data("price")) && parseFloat($("#" + block + "_fol_" + idd).val()) === parseFloat($(this).data("fol_price"))) {
				$("#" + block + "_edit_block_" + idd).toggle("fast");
			} else {
				var extra = "";
				if (block === "con") {
					//attach follow up prices
					extra = "&followUpPrice=" + $("#" + block + "_fol_" + idd).val();
				} else if (block === "pr") {
					//attach the theatre, anaesthesia, surgeon price
					extra = "&theatre=" + $("#" + block + "_theatre_" + idd).val() + "&surgeon=" + $("#" + block + "_surg_" + idd).val() + "&anaesthesia=" + $("#" + block + "_anas_" + idd).val();
				}
				// Boxy.confirm("You are about to change the current service/product price for <?= $scheme->getName() ?> from <em>"+$(this).data("price")+"</em> to <em>"+$("#"+block+"_"+idd).val()+"</em> <br>Click 'Ok' to proceed otherwise click 'Cancel'", function(){
				Boxy.confirm("You are about to modify the price for an item. <br>Click 'OK' to proceed otherwise click 'Cancel'", function () {
					$.ajax({
						url: '/pm/insurance/boxy.editscheme.php?sid=<?= $sid ?>',
						type: 'POST',
						data: "editPrice=true&type=" + block + "&itcid=" + idd + "&price=" + $("#" + block + "_" + idd).val() + extra + coverageType + capitation,
						dataType: 'json',
						beforeSend: function () {
//                            alert(this.data)
						},
						success: function (d) {
							if (d.indexOf("success") !== -1) {
								$("#" + block + "_edit_block_" + idd).toggle("fast");
								$("#" + block + "_save_" + idd).data("price", $("#" + block + "_" + idd).val());
								Boxy.info(d.split(":")[1]);
							} else {
								Boxy.alert(d.split(":")[1]);
							}
						},
						error: function (d) {
							Boxy.alert("Sorry, something went wrong!!!");
						}
					}, {title: 'Item Price Update'});
				});
			}
		});

		$('a[data-name="priceEdit"]').click(function (e) {
			var id = $(e.currentTarget).data('id');
			Boxy.load('/pm/insurance/boxy.editscheme_edit_item.php?id='+id, {title: 'Edit Covered Item'});
		});
		
	});
</script>
