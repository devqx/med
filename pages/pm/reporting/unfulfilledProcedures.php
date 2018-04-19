<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/29/16
 * Time: 9:57 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;
$start =  isset($_POST['start']) ? $_POST['start'] : null;
$stop = isset($_POST['stop']) ? $_POST['stop']: null;
$data = (new PatientProcedureDAO())->unfulfilledRequests($page, $pageSize, $start, $stop);
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
			<?php foreach ($data->data as $p){?>
				<tr>
					<td nowrap><?= date(MainConfig::$dateTimeFormat, strtotime($p->getRequestDate())) ?></td>
					<?php if ($p->getPatient() != null) { ?>
						<td nowrap>
						<span class="profile" data-pid="<?= $p->getPatient()->getId(); ?>" xtitle="<?= $p->getPatient()->getFullname(); ?>"><?= $p->getPatient()->getShortname(); ?></span>
						</td><?php } ?>
					<td><a href="javascript:;" title="more details"><?= $p->getRequestCode() ?></a></td>
					<td><?= $p->getProcedure()->getName() ?></td>

					<td>
						<?= ($p->getReferral() !== null) ? '<span title="Referred from ' . $p->getReferral()->getName() . '(' . $p->getReferral()->getCompany()->getName() . ')"><i class="icon-info-sign"></i></span>' : '' ?>
						<?= $p->getRequestedBy()->getShortname() ?></td>
					<td><?= ucwords($p->getStatus()) ?> <?php if($p->getStatus()=='open'){?> | <a href="javascript:" class="cancelOrder" data-id="<?= $p->getId()?>">Cancel</a><?php }?></td>
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
	}).on('click', '.cancelOrder', function (e) {
		if (!e.handled) {
			Boxy.ask('Are you sure to cancel this request?', ['Yes', 'Not really'], function(answer){
				if(answer == 'Yes'){
					$.post('/api/procedure_action.php', {status: "cancel", id: $(e.target).data('id')}, function (s) {
						var data = s.split(":");
						if (data[0] === "error") {
							Boxy.alert(data[1]);
						} else if (data[0] === "success") {
							goto(currentPage);
						}
					});
				}
			});

			e.handled = true;
		}
	});
	$('[name="date_start"]').datetimepicker({timepicker:false,format:'Y/m/d'});
	$('[name="date_stop"]').datetimepicker({timepicker:false,format:'Y/m/d'});
</script>