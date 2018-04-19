<?php
/**
 * Created by JetBrains PhpStorm.
 * User: peter
 * Date: 10/23/13
 * Time: 9:28 PM
 * To change this template use File | Settings | File Templates.
 */
if (!isset($_SESSION)) {
	session_start();
}
require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.patient.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/NursingServiceDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$serviceCenters = (new ServiceCenterDAO())->all('Vaccine');

$pdo = (new MyDBConnector())->getPDO();

$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

$pid = escape($_GET['id']);
$PATIENT = new Manager();
$pName = $PATIENT->getPatientName($pid);
$currency = (new CurrencyDAO())->getDefault();

$staff = $PATIENT->STAFF;
if ($this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->records)) {
	?>
	<div style="width:800px">

		<div class="well">Vaccines Due for <?= $pName; ?></div>
		<span id="totalCost" class="border"></span>

		<div class="cnt" id="cnt0">
			<form id="apply_immunization_" method="post" onsubmit="summarize_vaccine();return false;">
				<div class="row-fluid">
					<label class="span6">Service Center <select required name="service_centre_id" data-placeholder="Service Center">
							<option></option>
							<?php foreach ($serviceCenters as $center){?>
								<option value="<?=$center->getId()?>"><?=$center->getName()?></option> <?php }?>
						</select> </label>
					<label class="span6">
						Nursing Service <select name="nursing_service_id" data-placeholder=" - - Nursing Service applicable - -">
							<option value=""></option>
							<?php foreach ((new NursingServiceDAO())->all() as $service) { ?>
								<option value="<?= $service->getId() ?>"><?= $service->getName() ?></option>
							<?php } ?>
						</select>
					</label>
				</div>
				
				<?= $PATIENT->showDueVaccinesForPatient($pid, $mode = 'preparation'); ?>

			</form>
		</div>

		<div class="cnt hide" id="cnt1">
			<div class="notify-bar">*Overdue vaccines are now visible for catch-up</div>
			<form id="apply_immunization" method="post" onsubmit="summarize_vaccine();return false;">
				<div class="row-fluid">
					<label class="span6">Service Center <select required name="service_centre_id" data-placeholder="Service Center">
							<option></option>
							<?php foreach ($serviceCenters as $center){?>
								<option value="<?=$center->getId()?>"><?=$center->getName()?></option> <?php }?>
						</select> </label>
					<label class="span6">
						Nursing Service <select name="nursing_service_id" data-placeholder=" - - Nursing Service applicable - -">
							<option value=""></option>
							<?php foreach ((new NursingServiceDAO())->all() as $service) { ?>
								<option value="<?= $service->getId() ?>"><?= $service->getName() ?></option>
							<?php } ?>
						</select>
					</label>
				</div>
				<?= $PATIENT->showDueVaccinesForPatient($pid, $mode = 'preparation', $enableCatchUp = true); ?>
			</form>
		</div>

		<label><input type="checkbox" id="showDueVaccinesCheck"/> <em>Include overdue vaccines for catch-up</em></label>

		<div>
			<button type="button" onclick="$('.cnt:visible > form').submit()" class="btn">Process selected vaccines</button>
			<button type="button" onclick="Boxy.get(this).hideAndUnload()" class="btn-link">Cancel</button>
		</div>
	</div>
	<script type="text/javascript">
		function start() {
		}

		function done(s) {
			if (s === 'ok') {
				Boxy.info('Saved !', function () {
					showTabs(1);//then close this dialog,
					Boxy.get($('.close')).hideAndUnload();
				});//and reload this tab,
			} else {
				Boxy.alert(s);
			}
		}

		function summarize_vaccine() {
			var data_ = $('.cnt:visible > form').serialize();

			if ($('input[name="vaccine[]"]:checkbox:checked').length >= 1) {
				$.post('/immunization/ajax.queuePatientToVaccine.php', data_, function (s) {
					//s = {status:'',message:''}
					if (s.status === "success") {
						Boxy.info("Continue to billing/vaccine queue", function () {
							Boxy.get($(".close")).hideAndUnload();
						});
					} else {
						Boxy.alert(s.message);
					}
				}, 'json').fail(function (s) {
					Boxy.alert("Error: couldn't process the vaccines");
				});

			} else {
				Boxy.warn("You didn't select any vaccine");
			}
		}

		$('table.catchUpTable').dataTable({"bFilter": true, /*"bLengthChange": false*/});
		$('.cnt:visible input[data-price]').live('change', function (e) {
			if (!e.handled) {
				var total = 0;
				var num = 0;
				//todo: bug when you paginate, this object loses track
				$('.cnt:visible input[data-price]').each(function () {
					if ($(this).is(":checked")) {
						num += 1;
						total += parseFloat($(this).data('price'));
					}
				});
				$("#totalCost").html('Total Cost of selected ' + num + ' vaccines is: <?= $currency->getSymbolLeft() ?>' + parseFloat(total).toFixed(2)+"<?= $currency->getSymbolRight() ?>");
				$('.cnt:visible input[name="totalCost"]').val(total);
				e.handled = true;
			}

		});
		setTimeout(function () {
			$('.cnt:visible input[data-price]').trigger('change');
		}, 10);

		$('#showDueVaccinesCheck').on('click', function () {
			$("#cnt0, #cnt1").toggleClass('hide');

			// clear the previously selected ones
			$('.cnt:visible input[data-price]').each(function () {
				$(this).prop('checked', false).iCheck('update');
			});
			//then update the selected prices
			$('.cnt:visible input[data-price]').trigger('change');
		});
	</script>
	<?php
} else {
	echo $protect->ACCESS_DENIED;
} ?>