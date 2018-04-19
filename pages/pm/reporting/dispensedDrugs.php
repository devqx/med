<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/14/16
 * Time: 1:19 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DispensedDrugsDAO.php';

$page = !is_blank(@$_GET['page']) ? intval(@$_GET['page']) : 0;
$from = !is_blank(@$_GET['from']) ? @$_GET['from']: null;
$to = !is_blank(@$_GET['to']) ? @$_GET['to']: null;
$pageSize = 10;
$data = (new DispensedDrugsDAO())->all($page, $pageSize, $from, $to);
$totalSearch = $data->total;
?>

<div><a href="/pm/reporting/index.php"><input type="button" class="btn" value="<< Back"></a></div>
<h2>Dispensed Drugs Report</h2>

<div class="document">
	<div class="row-fluid">
		<div class="span5 input-prepend" style="margin-left: 0;">
			<span class="add-on">From</span>
			<input class="span11" type="text" placeholder="Start Date" name="from" value="<?= @$_REQUEST['from'] ?>" id="from">
		</div>
		<div class="span5 input-prepend">
			<span class="add-on">To</span>
			<input class="span11" type="text" placeholder="End Date" name="to" value="<?= @$_REQUEST['to'] ?>" id="to" disabled="disabled">
		</div>
		<button class="btn span2 wide" type="button" id="export">Export</button>
	</div>

	<div id="mainData" class=" dataTables_wrapper">
		<table class="table-bordered table-striped table-hover table">
			<thead>
			<tr>
				<th>Drug Brand Name</th>
				<th>Generic Name</th>
				<th class="amount">Quantity Dispensed</th>
				<th>Stock Unit</th>
				<!--<th>Service Center</th>-->
			</tr>
			</thead>
			<tbody>
			<?php foreach ($data->data as $datum){ // $datum=new DispensedDrugs();?>
				<tr>
					<td><?= $datum['drug']->getName()?></td>
					<td><?= $datum['drug']->getGeneric()->getName()?></td>
					<td class="amount"><?= $datum['quantity']?></td>
					<td><?= ucwords($datum['drug']->getGeneric()->getForm())?></td>
					<!--<td><?//= $datum->getBatch()->getServiceCentre()->getName()?></td>-->
				</tr>
			<?php } ?>
			</tbody>
		</table>

		<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)</div>
		<div class="resultsPagerOpen no-footer dataTables_paginate">
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
	var reload = function () {
		url = '/pages/pm/reporting/dispensedDrugs.php?' + $.param(postData);
		$("#mainData").load(url + " #mainData>*", "");
		//$.get(url, function (response) {
		//	console.log(response);
		//})
	};
	$(document).ready(function () {
		var from = $("#from");
		var to = $("#to");
		from.datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onChangeDateTime: function (dp, $input) {
				postData["from"] = $("#from").val();
				if ($input.val().trim() != "") {
					to.val('').removeAttr('disabled');
				}
				else {
					to.val('').attr({'disabled': 'disabled'});
				}
			}
		});
		to.datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onShow: function (ct) {
				this.setOptions({minDate: from.val() ? from.val() : false});
			},
			onSelectDate: function (ct, $i) {
				postData["to"] = $("#to").val();
				if (from.val() && to.val()) {
					reload();
				}
			}
		});

		if (from.val().trim() != "") {
			to.removeAttr('disabled');
		}
	})
</script>
<script>
	var postData = {
		'page': 0,
		'from': $("#from").val(),
		'to': $("#to").val()
	};

	$(document).on('click', '.resultsPagerOpen.dataTables_paginate a.paginate_button', function (e) {
		if (!$(this).hasClass("disabled") && !e.handled) {
			postData["page"] = $(this).data("page");
			reload();
			e.handled = true;
		}
	});

	$(document).on('click', '#export', function (e) {
		if (!e.handled) {
			window.open('/excel.php?dataSource=dispensed_drugs&filename=Dispensed_Drugs_Report_&' + $.param(postData), '_blank');
			e.handled = true;
		}
	});
</script>