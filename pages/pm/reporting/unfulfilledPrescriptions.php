<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/29/16
 * Time: 9:58 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;
$start =  isset($_POST['start']) ? $_POST['start'] : null;
$stop = isset($_POST['stop']) ? $_POST['stop']: null;
$data = (new PrescriptionDataDAO())->getUnfulfilledPrescriptions($page, $pageSize, $start, $stop);
$totalSearch = $data->total;
?>
<div class="no-print"><a href="/pm/reporting/index.php" class="btn-link"> Back</a></div>
<div class="document">
	<div class="row-fluid ui-bar-c">
		<div class="span12">
			Filter by date:
			<div class="input-prepend">
				<span class="add-on">From</span>
				<input class="span5" type="text" name="date_start" placeholder="Start Date">
				<span class="add-on">To</span>
				<input class="span5" type="text" name="date_stop" placeholder="Stop Date">
				<button class="btn" type="submit" id="date_filter">Search</button>
			</div>
		</div>
	</div>
	<div class="dataTables_wrapper" id="requestList">
		<table class="table table-striped">
			<thead>
			<tr>
				<th>Request Date</th>
				<th>Patient</th>
				<th>Request #</th>
				<th>Service</th>
				<th>Request By</th>
				<th>*</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($data->data as $ps) { ?>
				<tr>
					<td class="nowrap"><?= date(MainConfig::$dateTimeFormat, strtotime($ps->when)) ?></td>
					<td>
						<span class="profile" data-pid="<?= $ps->patient_id ?>"><a href="javascript:"><?= $ps->patientName ?></a></span>
					</td>
					<td><?= $ps->group_code ?></td>
					<td><?= $ps->quantity ?> <?= $ps->generic_name ?> <?= pluralize($ps->form, $ps->quantity) ?>
						(<?= $ps->drug_name ?>)
					</td>
					<td>
						<?php //= ($ps->getLabGroup()->getReferral() !== null) ? '<span title="Referred from ' . $ps->getLabGroup()->getReferral()->getName() . '(' . $ps->getLabGroup()->getReferral()->getCompany()->getName() . ')"><i class="icon-info-sign"></i></span>' : '' ?>

						<span><?= $ps->requestedByName ?></span>
					</td>
					<td class="nowrap">
						<?php if ($ps->status == "filled") { ?>
							<a href="javascript:;" class="cancelLink" data-id="<?= $ps->id ?>">Cancel</a>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<div class="dataTables_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found
			(Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
		</div>
		<div class="resultsPager2 no-footer dataTables_paginate">
			<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
				<a data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
					records</a>
				<a data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
					records</a>
				<?php /*<span>
                <?php if(ceil($data->total/$pageSize) >= 1 ){?><a class="paginate_button <?= (1 == $page) ?"current":""?>" data-page="1">1</a><?php }?>
                <?php if(ceil($data->total/$pageSize) >= 2){?><a class="paginate_button <?= (2 == $page) ?"current":""?>" data-page="2">2</a><?php }?>
                <?php if(ceil($data->total/$pageSize) > 2){?><span>&hellip;</span> <a class="paginate_button" data-page="<?= ceil($data->total/$pageSize) ?>"><?= ceil($data->total/$pageSize) ?></a><?php }?>
            </span> */ ?>
				<a class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?>
					records</a>
				<a class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?>
					records</a>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	var currentPage = parseInt($($('#DataTables_Table_1_paginate').find('.next')[1]).data('page'))-1;

	$(document).on('click', '.resultsPager2.dataTables_paginate a.paginate_button', function (e) {
		var page = $(this).data("page");
		if (!$(this).hasClass("disabled") && !e.handled) {
			goto(page);
			e.handled = true;
		}
	}).on('click', '.cancelLink', function (e) {
		if (!e.handled) {
			cancelPrescription($(e.target).data('id'));
			e.handled = true;
		}
	}).on('click', '#date_filter', function (e) {
		var page = 0;
		if (!e.handled) {
			goto(page);
			e.handled = true;
		}
	});

	function goto(page) {
		$.post('<?= $_SERVER['REQUEST_URI'] ?>', {page: page, start: $('[name="date_start"]').val(), stop: $('[name="date_stop"]').val()}, function (response) {
			$('#requestList').html($(response).find('#requestList').html());
		});
	}

	function cancelPrescription(id) {
		if (confirm("Are you sure you want to cancel this prescription?")) {
			vex.dialog.prompt({
				message: 'Please enter your reason for cancellation',
				placeholder: 'Regimen Cancellation note',
				value: 'Unfulfilled Request?',
				overlayClosesOnClick: false,
				beforeClose: function (e) {
					e.preventDefault();
				},
				callback: function (value) {
					if (value !== false && value !== '') {
						$.ajax({
							url: '/api/regimens.php',
							data: {action: 'cancel', id: id, reason: value},
							type: 'POST',
							complete: function (xhr, status) {
								if (status == "success" && xhr.responseText == "true") {
									goto(currentPage);
								}
							}
						});

					} else {

					}
				}, afterOpen: function ($vexContent) {
					$('.vex-dialog-prompt-input').attr('autocomplete', 'off');
					var $submit = $($vexContent).find('[type="submit"]');
					$submit.attr('disabled', true);
					$vexContent.find('input').on('input', function () {
						if ($(this).val()) {
							$submit.removeAttr('disabled');
						} else {
							$submit.attr('disabled', true);
						}
					}).trigger('input');
				}
			});
		}
	}

	$('[name="date_start"]').datetimepicker({timepicker:false,format:'Y/m/d'});
	$('[name="date_stop"]').datetimepicker({timepicker:false,format:'Y/m/d'});
</script>