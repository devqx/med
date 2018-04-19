<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/28/16
 * Time: 3:24 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;
$start =  isset($_POST['start']) ? $_POST['start'] : null;
$stop = isset($_POST['stop']) ? $_POST['stop']: null;
$data = (new PatientLabDAO())->getUnfulfilledLabs($page, $pageSize, TRUE, $start, $stop);
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
			<?php foreach ($data->data as $labs){?>
			<tr>
				<td class="nowrap"><?= date(MainConfig::$dateTimeFormat, strtotime($labs->getLabGroup()->getRequestTime())) ?></td>
				<td>
					<span class="profile" data-pid="<?= $labs->getPatient()->getId() ?>" xtitle="<?= $labs->getPatient()->getFullname() ?>"><?= $labs->getPatient()->getShortName() ?></span>
				</td>
				<td><?= $labs->getLabGroup()->getGroupName() ?></td>
				<td><?= $labs->getTest()->getName() ?></td>
				<td>
					<?= ($labs->getLabGroup()->getReferral() !== null) ? '<span title="Referred from ' . $labs->getLabGroup()->getReferral()->getName() . '(' . $labs->getLabGroup()->getReferral()->getCompany()->getName() . ')"><i class="icon-info-sign"></i></span>' : '' ?>

					<span title="<?= $labs->getLabGroup()->getRequestedBy()->getFullname() ?>"><?= $labs->getLabGroup()->getRequestedBy()->getShortName() ?></span>
				</td>
				<td class="nowrap">
					<?php if ($labs->getStatus() == "open" && $labs->getReceived() === FALSE) { ?>
						<a href="javascript:;" class="cancelLabLink" data-id="<?= $labs->getId() ?>">Cancel</a>
					<?php } ?>
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
	});

	function goto(page) {
		$.post('<?= $_SERVER['REQUEST_URI'] ?>', {page: page, start: $('[name="date_start"]').val(), stop: $('[name="date_stop"]').val()}, function (response) {
			$('#requestList').html($(response).find('#requestList').html());
		});
	}

	$('a.cancelLabLink').live('click', function(e){
		var id = $(this).data("id");
		if(e.handled != true){
			Boxy.ask("Are you sure you want to cancel this request line item?", ["Yes", "No"], function(choice){
				if(choice == "Yes"){
					$.post('/api/labrequests.php', {id: id, action:"cancel"}, function(s){
						if(s.trim()=="ok"){
							goto(currentPage);
						} else {
							Boxy.alert("An error occurred");
						}
					});
				}
			});
			e.handled=true;
		}
	});

	$('[name="date_start"]').datetimepicker({timepicker:false,format:'Y/m/d'});
	$('[name="date_stop"]').datetimepicker({timepicker:false,format:'Y/m/d'});
</script>