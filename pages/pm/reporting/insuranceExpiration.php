<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/8/16
 * Time: 3:08 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';

$schemes = (new InsuranceSchemeDAO())->getInsuranceSchemes(true);
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$scheme = (isset($_REQUEST['scheme']) && !is_blank($_REQUEST['scheme'])) ? $_REQUEST['scheme'] : null;
$date = (isset($_REQUEST['date']) && !is_blank($_REQUEST['date'])) ? $_REQUEST['date'] : null;
$pageSize = 50;
$data = (new InsuranceDAO())->all($page, $pageSize, $scheme, $date);
$totalSearch = $data->total;
?>
<p></p>
<div class="clearBoth"></div>
<div class="document">
	<div class="row-fluid">
		<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>">
			<div class="span6">
				<label>Filter by Insurance Scheme
					<select name="scheme_id" data-placeholder=" Filter by Insurance Scheme ">
						<option></option>
						<?php foreach ($schemes as $s) { ?>
							<option <?= ($s->getId() == @$_REQUEST['scheme']) ? 'selected' : '' ?> value="<?= $s->getId() ?>"><?= $s->getName() ?></option>
						<?php } ?>

					</select>
				</label>
			</div>
			<label class="span4">
				Filter by date:
				<input type="text" name="date" value="<?= isset($date) ? $date : '' ?>" placeholder="Date">
			</label>
			<div class="span1">
				<button class="btn wide" type="button" style="margin-top: 25px" id="show">Show</button>
			</div>
			<div class="span1">
				<button class="btn wide" type="button" style="margin-top: 25px">Export</button>
			</div>
		</form>

	</div>
	<div id="area" class="dataTables_wrapper">
		<table class="table table-striped">
			<thead>
			<tr>
				<th>Patient</th>
				<th>Scheme</th>
				<th>Expiration</th>
			</tr>
			</thead>
			<?php foreach ($data->data as $d) { //$d=new Insurance;?>
				<tr>
				<td><a href="/patient_profile.php?id=<?= $d->patient_id ?>" target="_blank"><?= $d->patientName ?></a></td>
				<td><?= $d->scheme_name ?></td>
				<td<?= (!(bool)$d->active ? ' class="required"' : '') ?>><?= ($d->insurance_expiration != 0 && $d->insurance_expiration != null) ? date(MainConfig::$shortDateFormat, strtotime($d->insurance_expiration)) : 'N/A' ?></td>
				</tr><?php } ?>
		</table>
		<div class="list1 dataTables_wrapper no-footer">
			<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)</div>

			<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
				<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?> records</a>
				<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?> records</a>

				<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
				<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
			</div>
		</div>

	</div>

</div>
<script>
	$(document).ready(function () {
		$('select').select2({width: '100%', allowClear: true});
		$('input[name="date"]').datetimepicker({format: 'Y/m/d', timepicker: false});

		$('#show').click(function (e) {
			if (!e.clicked) {
				var schemeId = $('select[name="scheme_id"]').select2("val");
				var dateSel = $('input[name="date"]').val();
				$.get('/pages/pm/reporting/insuranceExpiration.php', {date: dateSel, page:0, scheme: schemeId}, function (s) {
					$('#area').html($(s).find('#area').html());
				});
				e.clicked = true;
			}
		});

		$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
			if (!e.clicked) {
				var page = $(this).data("page");
				var schemeId = $('select[name="scheme_id"]').select2("val");
				var dateSel = $('input[name="date"]').val();
				if (!$(this).hasClass("disabled")) {
					$.get('/pages/pm/reporting/insuranceExpiration.php', {date: dateSel, page:0, scheme: schemeId}, function (s) {
						$('#area').html($(s).find('#area').html());
					});
				}
				e.clicked = true;
			}
		});
	})
</script>

