<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/29/16
 * Time: 9:11 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';


$inPatients = [];
$dates = [date('Y-m-d'), date('Y-m-d')];
if(isset($_REQUEST['from'], $_REQUEST['to'])){
	$dates = [$_REQUEST['from'], $_REQUEST['to']];
} else {
	$_REQUEST['from'] = $_REQUEST['to'] = date('Y-m-d');
}

$pageSize = 10;
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$staff = !is_blank(@$_REQUEST['staff_id']) ? @$_REQUEST['staff_id'] : null;

$data = (new CreditLimitDAO())->allAudit($page, $pageSize, $dates, $staff);
$totalSearch = $data->total;

?>


<div id="report_container" class="document dataTables_wrapper">
	<div class="row-fluid">
		<div class="span3 input-prepend" style="margin-left: 0;">
			<span class="add-on">From</span>
			<input class="span10" type="text" placeholder="Start Date" name="from" value="<?=$_REQUEST['from']?>" id="from">
		</div>
		<div class="span3 input-prepend">
			<span class="add-on">To</span>
			<input class="span10" type="text" placeholder="End Date" name="to" value="<?= $_REQUEST['to'] ?>" id="to" disabled="disabled">
		</div>
		<label class="span4">
			<input type="hidden" name="staff_id" placeholder="-- Filter by Staff --">
		</label>
		<button class="btn span2 wide" type="button" id="export">Export</button>
	</div>
	<div id="mainData">
		<table class="table table-hover table-striped">
			<thead>
			<tr>
				<th>Patient</th>
				<th class="amount">Amount</th>
				<th>Date Set</th>
				<th>Expiration</th>
				<th>Reason</th>
				<th>By</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($data->data as $cr){//$cr=new CreditLimit();?>
				<tr>
					<td><span class="profile" data-pid="<?= $cr->getPatient()->getId()?>"><?= $cr->getPatient()->getFullname()?></span> </td>
					<td class="amount"><?= $cr->getAmount()?></td>
					<td><?= date(MainConfig::$dateTimeFormat, strtotime($cr->getDate())) ?></td>
					<td><?= date(MainConfig::$dateFormat, strtotime($cr->getExpiration())) ?></td>
					<td><?= $cr->getReason()?></td>
					<td><?= $cr->getSetBy()? $cr->getSetBy()->getFullname() : '--'?></td>
				</tr>
			<?php }?>
			</tbody>

		</table>

		<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page+1?> of <?= ceil($totalSearch / $pageSize)?>)</div>
		<div class="lkistsPagerOpen no-footer dataTables_paginate">
			<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
				<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
				<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>
				<a id="DataTables_Table_1_last" class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
				<a id="DataTables_Table_1_next" class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
			</div>
		</div>
	</div>
</div>
<script>
	var postData = {
		'page': 0,
		'from': $("#from").val(),
		'to': $("#to").val(),
		'staff_id': $('[name="staff_id"]').val()
	};

	var reload = function () {
		url = '/pages/pm/reporting/credit_limits.php?'+ $.param(postData);
		$("#mainData").load(url+" #mainData>*","");
	};
	$(document).ready(function () {
		var from = $("#from");
		var to = $("#to");
		from.datetimepicker({
			format:'Y-m-d',
			formatDate:'Y-m-d',
			timepicker:false,
			onChangeDateTime:function(dp,$input){
				postData["from"] = $("#from").val();
				if($input.val().trim()!=""){
					to.val('').removeAttr('disabled');}
				else {
					to.val('').attr({'disabled':'disabled'});
				}
			}
		});
		to.datetimepicker({
			format:'Y-m-d',
			formatDate:'Y-m-d',
			timepicker:false,
			onShow:function(ct){
				this.setOptions({ minDate: from.val()? from.val():false});
			},
			onSelectDate:function(ct,$i){
				postData["to"] = $("#to").val();
				if(from.val() && to.val()){
					reload();
				}
			}
		});

		if(from.val().trim()!=""){
			to.removeAttr('disabled');
		}
	});

	$('[name="staff_id"]').select2({
		placeholder: $(this).attr('placeholder'),
		allowClear: true,
		minimumInputLength: 3,
		width: '100%',
		formatResult: function (data) {
			return data.fullname + "; " + (data.specialization == null ? "" : data.specialization.name);
		},
		formatSelection: function (data) {
			return data.fullname + "; " + (data.specialization == null ? "" : data.specialization.name);
		},
		ajax: {
			url: '/api/search_staffs.php',
			dataType: 'json',
			data: function (term, page) {
				return {
					q: term, // search term
					limit: 100,
					asArray: true
				};
			},
			results: function (data, page) {
				return {results: data};
			}
		}
	}).change(function(e){
		postData['staff_id'] = $(e.target).val();
		reload();
	});

	$(document).on('click', '.lkistsPagerOpen.dataTables_paginate a.paginate_button', function(e){
		if(!$(this).hasClass("disabled") && !e.handled){
			postData["page"] = $(this).data("page");
			reload();
			e.handled = true;
		}
	});

	$(document).on('click', '#export', function(e){
		if(!e.handled){
			window.open('/excel.php?dataSource=credit_limits&filename=Credit_Limit_Report&'+ $.param(postData), '_blank');
			e.handled = true;
		}
	});
</script>