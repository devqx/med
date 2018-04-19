<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/21/15
 * Time: 11:24 AM
 */

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralCompanyDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/api/get_referrals.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/json2csv.class.php';

$page = 0;
$pageSize = 1000;
$referralReport_ = array();
if (isset($_REQUEST['ex_']) && isset($referralReport) && sizeof($referralReport) > 0) {
	foreach ($referralReport as $k => $report) {
		//$report = new Bill();
		$rpt = array();
		$rpt['Patient Name'] = $report->getPatient()->getFullname();
		$rpt['Referral Name/Doctor'] = $report->getReferral()->getName() . ' [' . $report->getReferral()->getBankName() . ':' . $report->getReferral()->getAccountNumber() . ']';
		$rpt['Referral Company/Hospital'] = $report->getReferral()->getCompany()->getName() . ' [' . $report->getReferral()->getCompany()->getBankName() . ':' . $report->getReferral()->getCompany()->getAccountNumber() . ']';
		$rpt['Service Type'] = ucwords($report->getSource()->getName());
		$rpt['Service Description'] = $report->getDescription();
		$rpt['Cost of Service'] = $report->getAmount();
		$referralReport_[] = $rpt;
	}
}
if (isset($_REQUEST['ex_'])) {
	$JSON2CSV = new JSON2CSVutil;
	$JSON2CSV->readJSON(json_encode($referralReport_));
	$JSON2CSV->flattenDL("Referral_Reports.csv");
	exit;
}

$referrals = (new ReferralDAO())->all(0, 1000);
$referral_companies = (new ReferralCompanyDAO())->all(0, 1000);
?>
<style type="text/css">
	.filter .btn {
		float: right;
		margin-top: 24px;
	}

	.filter .span1 {
		margin-left: 0;
	}

	#exportIT {
		margin-left: 1%;
		width: 8%;
	}
</style>
<div><a class="btn-link" href="/pm/reporting/index.php">&laquo; Back</a></div>


<form id="filterForm" class="document" method="post" action="/pm/reporting/referrals.php">
	<h4>Referrals Report</h4>
	<div class="clearfix filter row-fluid">
		<label class="span2">From<input type="text" name="from" value="<?= (isset($_REQUEST['from']) ? $_REQUEST['from'] : '') ?>" id="from" placeholder="Select start date"/></label>
		<label class="span2">To:<input type="text" name="to" value="<?= (isset($_REQUEST['to']) ? $_REQUEST['to'] : '') ?>" id="to" placeholder="Select end date" disabled="disabled"/></label>
		<label class="span3">
			Filter by Referrer
			<select id="referrer_id" name="referrer_id" data-placeholder="Select referrer">
				<option></option>
				<?php foreach ($referrals->data as $k => $refs) { ?>
					<option value="<?= $refs->getId() ?>"<?= isset($_REQUEST['referrer_id']) && $_REQUEST['referrer_id'] == $refs->getId() ? ' selected="selected"' : '' ?>><?= $refs->getName() ?></option>
				<?php } ?>
			</select>
		</label>
		<label class="span3">
			Filter by Hospital
			<select id="hospital" name="hospital" data-placeholder="Select hospital">
				<option></option>
				<?php foreach ($referral_companies->data as $k => $refCo) { ?>
					<option value="<?= $refCo->getId() ?>"<?= isset($_REQUEST['hospital']) && $_REQUEST['hospital'] == $refCo->getId() ? ' selected="selected"' : '' ?>><?= $refCo->getName() ?></option>
				<?php } ?>
			</select>
		</label>
		<button class="btn span" id="exportIT" type="button"><i class="fa fa-file-excel-o"></i> Export</button>
		<button type="submit" class="btn span1">Show</button>
	</div>
</form>
<div class="document">
	<?php if (isset($_REQUEST['from']) && isset($_REQUEST['to']) && $_REQUEST['from'] != '') { ?>
		<h3 style="text-align: center">Referrals report for
			<?php if (isset($_REQUEST['referrer_id']) && $_REQUEST['referrer_id'] != '') { ?>
				<br>Referral Name/Doctor: <?= (new ReferralDAO())->get($_REQUEST['referrer_id'])->getName() ?>
			<?php } ?>
			<?php if (isset($_REQUEST['hospital']) && $_REQUEST['hospital'] != '') { ?>
				<br>Referral Company/Hospital: <?= (new ReferralCompanyDAO())->get($_REQUEST['hospital'])->getName() ?>
			<?php } ?>
			<br>PERIOD: <span> [<?php echo date("Y M d", strtotime($_REQUEST['from'])) . ' - ' . (($_REQUEST['to'] == '') ? date('Y M d') : date("Y M d", strtotime($_REQUEST['to']))) ?>]</span></h3>
	<?php } ?>
	<table class="table table-bordered table-hover">
		<thead>
		<tr>
			<th>Patient Name</th>
			<th>Referral Name/Doctor</th>
			<th>Referral Company/Hospital</th>
			<th>Service Type</th>
			<th>Service Description</th>
			<th>Cost of Service</th>
		</tr>
		</thead>
		<?php if (isset($referralReport) && sizeof($referralReport) > 0) {
			foreach ($referralReport as $k => $report) { ?>
				<tr>
					<td><?= $report->getPatient()->getFullname() ?></td>
					<td><?= $report->getReferral()->getName() ?> [<?= $report->getReferral()->getBankName() ?>:<?= $report->getReferral()->getAccountNumber() ?>]</td>
					<td><?= $report->getReferral()->getCompany()->getName() ?> [<?= $report->getReferral()->getCompany()->getBankName() ?>:<?= $report->getReferral()->getCompany()->getAccountNumber() ?>]</td>
					<td><?= ucwords($report->getSource()->getName()) ?></td>
					<td><?= $report->getDescription() ?></td>
					<td><?= $report->getAmount() ?></td>
				</tr>
			<?php }
		} ?>
	</table>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		$('table').dataTable();
		$("#from").datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onChangeDateTime: function (dp, $input) {
				if ($input.val().trim() !== "") {
					$("#to").val('').removeAttr('disabled');
				}
				else {
					$("#to").val('').attr({'disabled': 'disabled'});
				}

			}
		});
		$("#to").datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onShow: function (ct) {
				this.setOptions({minDate: $("#from").val() ? $("#from").val() : false});
			},
			onSelectDate: function (ct, $i) {

			}
		});

		if ($("#from").val().trim() !== "") {
			$("#to").removeAttr('disabled');
		}

		$("#hospital").select2({
			allowClear: true,
			width: '100%'
		});
		$("#referrer_id").select2({
			allowClear: true,
			width: '100%'
		});
		$('#exportIT').on('click', function (e) {
			if (!e.handled) {
				window.open('/pages/pm/reporting/referrals.php?ex_=csv&from=<?=(isset($_REQUEST['from'])) ? $_REQUEST['from'] : ''?>&to=<?=(isset($_REQUEST['to'])) ? $_REQUEST['to'] : ''?>&referrer_id=<?=(isset($_REQUEST['referrer_id']) ? $_REQUEST['referrer_id'] : '')?>&hospital=<?=(isset($_REQUEST['hospital']) ? $_REQUEST['hospital'] : '')?>', '_blank');
				e.handled = true;
				e.preventDefault();
			}
		});
	});
</script>