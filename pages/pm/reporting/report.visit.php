<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/9/16
 * Time: 8:03 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsurerDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/NurseReportDAO.php';

$schemes = (new InsuranceSchemeDAO())->getInsuranceSchemes();
$providers = (new InsurerDAO())->getInsurers();
$scheme_id = (isset($_REQUEST['scheme_id']) ? $_REQUEST['scheme_id'] : null);
$provider_id = (isset($_REQUEST['provider_id']) ? $_REQUEST['provider_id'] : null);
$date = ((isset($_REQUEST['from']) && $_REQUEST['from'] != '' && isset($_REQUEST['to']) && $_REQUEST['to'] != '') ? true : false);
$visits = $enrollments = [];
if ($date === true && ($_GET['view'] == 'visits')) {
	$visits = (new NurseReportDAO())->getVisits($_REQUEST['from'], $_REQUEST['to'], $scheme_id, $provider_id);
	//$enrollments = (new NurseReportDAO())->getEnrollments($_REQUEST['from'], $_REQUEST['to'], $scheme_id, $provider_id);
} else if ($date === true && $_GET['view'] == 'enrollments') {
	//$visits = (new NurseReportDAO())->getVisits($_REQUEST['from'], $_REQUEST['to'], $scheme_id, $provider_id);
	$enrollments = (new NurseReportDAO())->getEnrollments($_REQUEST['from'], $_REQUEST['to'], $scheme_id, $provider_id);
}
?>
<style type="text/css">
	.filter .btn {
		float: right;
		margin-top: 24px;
		white-space: nowrap;
	}
</style>
<div class="mini-tab"><a href="?view=visits" class="tab<?= (isset($_GET['view']) && $_GET['view'] == 'visits' ? ' on' : '') ?>">Visits</a>
	<a href="?view=enrollments" class="tab<?= (isset($_GET['view']) && $_GET['view'] == 'enrollments' ? ' on' : '') ?>">Enrollments</a></div>

<form id="filterForm" class="document" method="post" action="/pm/reporting/report.visit.php?view=<?= isset($_GET['view']) ? $_GET['view'] : '' ?>">
	<h4><?= isset($_GET['view']) ? ucwords($_GET['view']) : '' ?> Report</h4>
	<div class="clearfix filter row-fluid">
		<label class="span2">From<input type="text" name="from" value="<?= (isset($_REQUEST['from']) ? $_REQUEST['from'] : '') ?>" id="from" placeholder="Select start date"/></label>
		<label class="span2">To:<input type="text" name="to" value="<?= (isset($_REQUEST['to']) ? $_REQUEST['to'] : '') ?>" id="to" placeholder="Select end date" disabled="disabled"/></label>
		<label class="span3">
			Filter by Payer
			<select id="provider_id" name="provider_id" data-placeholder="Select Payer">
				<option></option>
				<?php foreach ($providers as $is) { ?>
					<option value="<?= $is->getId() ?>"<?= isset($_REQUEST['provider_id']) && $_REQUEST['provider_id'] == $is->getId() ? ' selected="selected"' : '' ?>><?= $is->getName() ?></option>
				<?php } ?>
			</select>
		</label>
		<label class="span3">
			Filter by Scheme
			<select id="scheme_id" name="scheme_id" data-placeholder="Select scheme">
				<option></option>
				<?php foreach ($schemes as $is) { ?>
					<option value="<?= $is->getId() ?>"<?= isset($_REQUEST['scheme_id']) && $_REQUEST['scheme_id'] == $is->getId() ? ' selected="selected"' : '' ?>><?= $is->getName() ?></option>
				<?php } ?>
			</select>
		</label>
		<button type="submit" class="btn span2">Show</button>
	</div>
</form>
<div class="document">
	<?php if (isset($_REQUEST['from']) && isset($_REQUEST['to']) && $_REQUEST['from'] != '') { ?>
		<h3 style="text-align: center"><?= isset($_GET['view']) ? ucwords($_GET['view']) : '' ?> report for
			<br>PERIOD: <span> [<?php echo date("Y M d", strtotime($_REQUEST['from'])) . ' - ' . (($_REQUEST['to'] == '') ? date('Y M d') : date("Y M d", strtotime($_REQUEST['to']))) ?>]</span></h3>
	<?php } ?>
	<div id="visit_report">
		<?php if ($_GET['view'] == 'visits') { ?>
			<table class="table" id="visits_table">
				<thead>
				<tr>
					<th>Date</th>
					<th>Number of visits</th>
					<th>*</th>
				</tr>
				</thead>
				<?php if (isset($visits) && sizeof($visits) > 0) {
					foreach ($visits as $i => $d) {
						?>
						<tr>
							<td><?= date('Y M d', strtotime($d->getDate())) ?></td>
							<td><?= $d->getCount() ?></td>
							<td>
								<a data-meta="<?= urlencode($d->getMeta()) ?>" class="view" href="javscript:;" data-date="<?= $d->getDate() ?>" data-id="<?= $d->getScheme()->getId() ?>">View</a> |
								<a data-meta="<?= urlencode($d->getMeta()) ?>" class="export" href="javscript:;" data-date="<?= $d->getDate() ?>" data-id="<?= $d->getScheme()->getId() ?>">Export</a>
							</td>
						</tr>
						<?php
					}
				} else {
					?>
					<tr>
						<td colspan="4"><span class="warning-bar">No patients were seen during this period</span></td>
					</tr>
				<?php } ?>
			</table>
		<?php } ?>
		<?php if ($_GET['view'] == 'enrollments') { ?>
			<table class="table" id="enroll__">
				<thead>
				<tr>
					<th>Date</th>
					<th>Number of enrollments</th>
					<th>*</th>
				</tr>
				</thead>
				<?php if (isset($enrollments) && sizeof($enrollments) > 0) {
					foreach ($enrollments as $d) {
						?>
						<tr>
							<td><?= date('Y M d', strtotime($d->getDate())) ?></td>
							<td><?= $d->getCount() ?></td>
							<td>
								<a class="view" href="javascript:;" data-meta="<?= urlencode($d->getMeta()) ?>" data-date="<?= $d->getDate() ?>" data-id="<?= $d->getScheme()->getId() ?>">View</a> |
								<a class="export" href="javascript:" data-meta="<?= urlencode($d->getMeta()) ?>" data-date="<?= $d->getDate() ?>" data-id="<?= $d->getScheme()->getId() ?>">Export</a></td>
						</tr>
						<?php
					}
				} else {
					?>
					<tr>
						<td colspan="4"><span class="warning-bar">No patients were enrolled during this period</span></td>
					</tr>
				<?php } ?>
			</table>
		<?php } ?>
	</div>
</div>
<script>
	$(document).ready(function () {

		$('#visits_table').dataTable();
		$('#enroll__').dataTable();

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

		$("#scheme_id").select2({
			allowClear: true,
			width: '100%'
		});

		$("#provider_id").select2({
			allowClear: true,
			width: '100%'
		});

		$(document).on('click', '.export', function (e) {
			if (!e.handled) {
				window.open('/pages/pm/reporting/reports/export_reports.php?ex_=xsl&type=<?= @$_GET['view']?>&date=' + $(this).data('date') + '&meta=' + $(this).data('meta'));
				//window.open('/pages/pm/reporting/reports/export_reports.php?ex_=xsl&type=<?= ((isset($_GET['view'])) ? $_GET['view'] : '') ?>&scheme=<?= $scheme_id ?>&date='+ $(this).data('date'),'_blank');
				e.handled = true;
				e.preventDefault();
			}
		});

		$(document).on('click', '.view', function (e) {
			if (!e.handled) {
				$.post('/pages/pm/reporting/reports/view_report.php', {'type': '<?= @$_GET['view']?>', 'date': $(this).data('date'), 'meta': $(this).data('meta')}, function (data) {
					new Boxy(data);
				});
				//Boxy.load('/pages/pm/reporting/reports/view_report.php?type=<?= ((isset($_GET['view'])) ? $_GET['view'] : '') ?>&scheme=<?= $scheme_id ?>&date='+ $(this).data('date'), {title:'View <?= ((isset($_GET['view'])) ? ucfirst($_GET['view']) : '') ?>'});
				e.handled = true;
				e.preventDefault();
			}
		});
	});
</script>
