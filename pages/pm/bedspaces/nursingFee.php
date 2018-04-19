<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/23/15
 * Time: 4:10 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AdmissionConfiguration.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AdmissionConfigurationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$DAO = (new AdmissionConfigurationDAO());
$admissionItems = $DAO->getAdmissionConfigurations();

if ($_POST) {
	if (is_blank($_POST['service_name']) || is_blank($_POST['service_price'])) {
		exit("error:Check empty or invalid field");
	}
	$item = new AdmissionConfiguration();
	$item->setName($_POST['service_name']);
	$item->setDefaultPrice(parseNumber($_POST['service_price']));

	$newItem = $DAO->addAdmissionConfiguration($item);

	if ($newItem !== null) {
		exit("success:Item created");
	}

	exit("error:Item not created");
}
?>
<section>
	<a class="pull-right action" href="#hide1" title="New Item">Add a Charge</a>
	<div class="clear"></div>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Item</th>
			<th class="amount">Default Price</th>
			<th>*</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($admissionItems as $item) {//$item = new AdmissionConfiguration();?>
			<tr>
				<td><?= $item->getName() ?></td>
				<td class="amount"><?= $item->getDefaultPrice() ?></td>
				<td>
					<a href="javascript:" data-href="/pages/pm/bedspaces/nursingFeeEdit.php?id=<?= $item->getId() ?>" class="editFeeLink" data-id="<?= $item->getId() ?>">Edit</a>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" class="" style="display: none;" id="hide1" onsubmit="return AIM.submit(this, {onStart: _t, onComplete: _o})">
		<span></span>
		<label>Item/Service Name <input type="text" name="service_name" required="required"> </label>
		<label>Item/Service Cost
			<span class="pull-right"><i class="icon-info-sign"></i> <em>might be charged daily</em></span><input type="number" name="service_price" min="0" step="0.01" required="required">
		</label>
		<div class="btn-block">
			<button class="btn" type="submit">Add</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hide()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('section > a.pull-right.action').boxy({});

		$(".boxy-content").on("click", ".editFeeLink", function (e) {
			if (!e.handled) {
				Boxy.load($(this).data("href"), {title: "Edit Admission Fee"});
				e.handled = true;
			}
		});
	});
	function _t() {
		$('form > span:first-child').html('<img src="/img/ajax-loader.gif">');
	}
	function _o(s) {
		if (s.split(":")[0] == "error") {
			$('form > span:first-child').html(s.split(":")[1]).removeClass("warning-bar").addClass("warning-bar");
		} else if (s.split(":")[0] == "success") {
			//reload this tab
			showTabs(6);
			$('form#hide1').get(0).reset();
			$('form > span:first-child').html('');
			Boxy.get($("#hide1")).hide()
		}
	}
</script>