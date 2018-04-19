<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/28/16
 * Time: 4:36 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;
$start =  isset($_POST['start']) ? $_POST['start'] : null;
$stop = isset($_POST['stop']) ? $_POST['stop']: null;
$data = (new PatientScanDAO())->getUnfulfilledScans($page, $pageSize, $start, $stop);
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
			<?php foreach ($data->data as $ps){?>
				<tr id="_sc_an_tr_<?= $ps->getId() ?>">
					<td class="nowrap">
						<div datetime="<?= strtotime($ps->getRequestDate()) ?>" title="<?= strtotime($ps->getRequestDate()) ?>"><?= date(MainConfig::$dateTimeFormat, strtotime($ps->getRequestDate())) ?></div>
					</td>
					<td nowrap="">
						<span class="profile" data-pid="<?= $ps->getPatient()->getId(); ?>"><?= $ps->getPatient()->getShortname(); ?></span>
					</td><td>
						<a data-title="<?= $ps->getRequestCode() . ": " . $ps->getScans()[0]->getName() ?>" class="" href="javascript:;" data-href="/imaging/scan.details.php?id=<?= $ps->getId() ?>"><?= $ps->getRequestCode() ?></a>
					</td>
					<td><?php $dd = [];
						foreach ($ps->getScans() as $rq) {$dd[] = $rq->getName();}
						echo implode(", ", $dd) ?></td>
					<td>
						<?= ($ps->getReferral() !== null) ? '<span title="Referred from ' . $ps->getReferral()->getName() . ' (' . $ps->getReferral()->getCompany()->getName() . ')"><i class="icon-info-sign"></i></span>' : '' ?>
						<span title="<?= $ps->getRequestedBy()->getFullname() ?>"><?= $ps->getRequestedBy()->getUsername() ?></span>
					</td>

					<td><?php if (!$ps->getStatus() && !$ps->getCancelled()) { ?>
								<a href="javascript:;" class="cancelRequest" data-id="<?= $ps->getId() ?>">Cancel</a>
							<?php }?>
					</td>
				</tr>
			<?php }?>
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

	function goto(page) {
		$.post('<?= $_SERVER['REQUEST_URI'] ?>', {page: page, start: $('[name="date_start"]').val(), stop: $('[name="date_stop"]').val()}, function (response) {
			$('#requestList').html($(response).find('#requestList').html());
		});
	}

	$(document).on('click', '.resultsPager2.dataTables_paginate a.paginate_button', function (e) {
		var page = $(this).data("page");
		if (!$(this).hasClass("disabled") && !e.handled) {
			goto(page);
			e.handled = true;
		}
	}).on('click', '#date_filter', function (e) {
		var page = 0;
		if (!e.handled) {
			goto(page);
			e.handled = true;
		}
	}).on("click", ".cancelRequest", function (e) {
		var request_id = $(this).data('id');
		if (!e.handled) {
			Boxy.ask("Cancel Request?", ['Yes', 'No'], function (response) {
				if (response === "Yes") {
					$.post("/api/cancel_image_request.php", {id: request_id}, function (data) {
						if (data) {
							goto(currentPage);
						} else {
							Boxy.alert("Failed to process request.");
						}
					}, 'json');
				}
			});
			e.handled = true;
		}
	});
	$('[name="date_start"]').datetimepicker({timepicker:false,format:'Y/m/d'});
	$('[name="date_stop"]').datetimepicker({timepicker:false,format:'Y/m/d'});
</script>