<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/7/16
 * Time: 11:58 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false);
$sources = (new BillSourceDAO())->getBillSources();

$tType = !is_blank(@$_REQUEST['ins_tType']) ? @$_REQUEST['ins_tType'] : null;
$from = !is_blank(@$_REQUEST['ins_date_from']) ? @$_REQUEST['ins_date_from'] : null;
$to = !is_blank(@$_REQUEST['ins_date_to']) ? @$_REQUEST['ins_date_to'] : null;
$billSources = !is_blank(@$_REQUEST['ins_bill_source_ids']) ? (@$_REQUEST['ins_bill_source_ids']) : [];

$page = isset($_POST['page']) ? $_POST['page'] : 0;
$pageSize = 20;

$data = (new BillDAO())->getInsuredBills(@$_REQUEST['pid'], $tType, $billSources, $from, $to, $page, $pageSize);
$totalSearch = $data->total;

if(!isset($_SESSION['checked_items'])){$_SESSION['checked_items'] = [];}
if(!isset($_SESSION['checked_items_all'])){$_SESSION['checked_items_all'] = [];}
$_SESSION['checked_items'][$page] = !is_blank(@$_POST['insBill']) ? array_filter(@$_POST['insBill']) : (isset($_SESSION['checked_items'][$page])? $_SESSION['checked_items'][$page] : [] );
$_SESSION['checked_items_all'] = array_flip(array_flip(array_flatten($_SESSION['checked_items'])));
?>

<form id="insFilterForm">
	<div class="filters row-fluid">
		<div class="span4">
			Transaction Date:
			<div class="input-prepend">
				<span class="add-on">From</span>
				<input class="span5" type="text" name="ins_date_from" id="ins_date_from" placeholder="Start Date">
				<span class="add-on">To</span>
				<input class="span5" type="text" name="ins_date_to" id="ins_date_to" placeholder="Stop Date">
			</div>
		</div>
		<div class="span2" style="margin:0 -10px 0 30px">
			<label>Transaction Type:<select name="ins_tType" id="ins_tType" data-placeholder="Select">
					<option></option>
					<option value="credit">CHARGE</option>
					<option value="debit">PAYMENT</option>
					<option value="discount">DISCOUNT</option>
					<option value="reversal">REVERSAL</option>
					<option value="refund">REFUND</option>
					<option value="write-off">WRITE-OFF</option>
					<option value="transfer-debit">TRANSFER-D</option>
					<option value="transfer-credit">TRANSFER-C</option>
				</select></label>
		</div>
		<div class="span4">
			Bill Source:
			<label><select name="ins_bill_source_ids[]" id="ins_bill_source_ids" multiple="multiple" class="wide">
					<?php foreach ($sources as $source) { ?>
						<option value="<?= $source->getId() ?>"><?= ucwords($source->getName()) ?></option><?php } ?>
				</select></label>
		</div>
		<div class="span1">
			<button type="button" id="ApplyInsFilter" class="btn wide" style="margin-top:24px">Apply</button>
		</div>
		<div class="span1">
			<button type="button" class="btn-link wide" id="ins_resetFilter" style="margin-top: 24px">Reset</button>
		</div>
	</div>
</form>
<?php if ($totalSearch > 0) { ?>
	<div class="__0">
	<table class="table table-striped insuranceBills">
		<thead>
		<tr>
			<th nowrap><label><input type="checkbox" title="Check all items" id="check_all_insurance_bills"> Bill #</label></th>
			<th class="hide">Reference</th>
			<th>Item</th>
			<th>Date</th>
			<th>Type</th>
			<th nowrap>Amount(<?=$currency ?>)</th>
			<th>Billed To</th>
			<th>Responsible</th>
		</tr>
		</thead>
		<?php foreach ($data->data as $row) { ?>
			<tr>
			<td nowrap><label><input type="checkbox" name="insBill[]" <?= isset($_SESSION['checked_items_all']) && in_array((int)$row->bill_id, $_SESSION['checked_items_all'] ) ? 'checked' :'' ?> value="<?= $row->bill_id; ?>"<?php if (!$row->transaction_type == 'transfer-credit' && !$row->transaction_type == 'credit') { ?> disabled<?php } ?>> <?= (int) $row->bill_id; ?></label></td>
			<td class="hide"><?= $row->payment_reference; ?></td>
			<td><?= $row->description; ?>
				
				<?php if ($this_user->hasRole($protect->bill_auditor) || $this_user->hasRole($protect->cashier) || $this_user->hasRole($protect->hmo_officer) || $this_user->hasRole($protect->records)){ ?>
					<span class="rewriteBill"> | <a data-id="<?= $row->bill_id; ?>" href="javascript:">Reverse</a></span><?php } ?>

       <?php if($this_user->hasRole($protect->bill_auditor) && ($row->bill_source_id == 10) && ($row->transaction_type == 'credit') ) { ?> <span  >|<a id="rewriteBill"  data-id="<?= $row->bill_id ?>" href="javascript:;"> Cancel</a> </span> <?php } ?>
			</td>
			<td><?= date(MainConfig::$dateTimeFormat, strtotime($row->transaction_date)); ?></td>
			<td><?= explode("-", strtoupper($row->transaction_type))[0]; ?><?php if ($row->transaction_type == 'debit') { ?> | <a href="javascript:;" onclick="Print('receipt','<?= $row->bill_id ?>','copy')">Receipt</a><?php } ?></td>
			<td class="amount"><?= number_format(abs($row->amount), 2); ?></td>
			<td><?= $row->scheme_name ?></td>
			<td><?= ($row->receiver == '') ? '' : (new StaffDirectoryDAO())->getStaff($row->receiver)->getShortname() ?></td>
			</tr><?php } ?>
		<tr>
			<td class="hide"></td>
			<td colspan="4" style="text-align:right">TOTAL: </td>
			<td class="amount"><?=$data->totalSum?></td>
			<td colspan="2"></td>
		</tr>
	</table>

	<div class="insBillsList dataTables_wrapper no-footer">
		<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
			results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
		</div>

		<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
			<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
				records</a>
			<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
				records</a>

			<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?>
				records</a>
			<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?>
				records</a>
		</div>
	</div>
	</div>
<?php } else { ?>
	<table class="table table-striped insuranceBills">
		<tr>
			<td>
				<div class="warning-bar">Nothing found!</div>
			</td>
		</tr>
	</table>
<?php } ?>
<button type="button" class="btn" id="claimsBtn">PROCESS CLAIMS</button>
<button type="button" class="btn" id="validateBtn">VALIDATE</button>

<script type="text/javascript">
	$(document).ready(function () {
		var now = new Date().toISOString().split('T')[0];
		$('#ins_date_from').datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onShow: function (ct) {
				this.setOptions({
					maxDate: now
				});
			},
			onChangeDateTime: function () {
				$("#ins_date_to").val("")
			}
		});
		$('#ins_date_to').datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onShow: function (ct) {
				this.setOptions({
					maxDate: now,
					minDate: $("#ins_date_from").val() ? $("#ins_date_from").val() : false
				});
			}
		});
		$('select[name^="ins_bill_source_ids"]').select2();

		$('select[id="ins_tType"]').select2({width: '100%', allowClear: true});


		$("#ins_resetFilter").click(function (e) {
			e.preventDefault();
			setTimeout(function () {
				$('a[href="#insuredBills"]').get(0).click();
			}, 500);
		});

		$("#ApplyInsFilter").click(function (e) {
			e.preventDefault();
			$.post('/billing/insurance_bills.php?pid=<?= $_GET['pid']?>', $('#insFilterForm').serialize(), function (response) {
				if($(response).filter('#insFilterForm + .__0').html()){
					$('#insFilterForm + .__0').html($(response).filter('#insFilterForm + .__0').html());
				} else {
					$('#insFilterForm + .__0').html('<div class="warning-bar">No data was returned</div><br>');
				}
			});
		});
	});
	
	$('#check_all_insurance_bills').live('change click', function (e) {
		if ($(this).is(':checked')) {
			$('[name="insBill[]"]:not(:disabled)').prop('checked', true).iCheck('update');
		} else {
			$('[name="insBill[]"]:not(:disabled)').prop('checked', false).iCheck('update');
		}
	});
	$(document).on('click', '.insBillsList.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				var postData = $('#insFilterForm').serializeObject();
				postData['page'] = page;
				var selectedBills = [];
				_.each($('[name="insBill[]"]:checked:not(:disabled)'), function(obj){
					selectedBills.push(parseInt($(obj).val()));
				});
				postData['insBill'] = selectedBills;
				$.post('/billing/insurance_bills.php?pid=<?= $_GET['pid']?>', postData, function (response) {
					$('#insFilterForm + .__0').html($(response).filter('#insFilterForm + .__0').html());
				});
			}
			e.clicked = true;
		}
	});
	
	$('.insuranceBills #rewriteBill').live('click', function (e) {
		if(!e.handled){
			var bId = $(this).data('id');
			Boxy.ask('Are you sure to cancel this bill line?', ['Yes', 'No'], function(answer){
				if(answer==='Yes'){
					$.post('/api/cancel__service_bill.php', {id: bId, type:'rewrite'}, function (response) {
						if(response==='success') {
							if ($.querystring(location.search)['aid'] !== undefined) {
								showTabs(13);
							} else {
								showTabs(7);
							}
						}else if(response==="error1"){
							Boxy.alert('Bill is already cancelled');
						} else {
							Boxy.alert('An error occurred/bill has been reversed before');
						}
					})
				}
			});
			e.handled=true;
		}
	})
	function myTrim(x) {
		return x.replace(/^\s+|\s+$/gm,'');
	}
</script>


