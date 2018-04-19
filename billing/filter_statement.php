<?php
//sleep(0.7);
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false);
$protect = new Protect();

$pid = $_POST['pid'];
$from = !is_blank(@$_POST['date_from']) ? $_POST['date_from'] : null;
$to = !is_blank(@$_POST['date_to']) ? $_POST['date_to']:null;
$tType = @$_POST['tType'];
$sources = !is_blank(@$_POST['bill_source_ids']) ? " AND bill_source_id IN (". implode(", ", @$_POST['bill_source_ids']) .")" : "";

$sql = "SELECT bills.*, bills.bill_id AS id, ANY_VALUE(iic.type) AS type FROM bills LEFT JOIN insurance_items_cost iic ON iic.item_code=bills.item_code LEFT JOIN insurance_schemes ON insurance_schemes.id = bills.billed_to WHERE patient_id = $pid AND insurance_schemes.pay_type = 'self'  {$sources}";

if ($tType != "---" && $tType != "") {
	$sql .= " AND transaction_type IN ('".implode("','", $tType)."')";
}

if ($from != null && $to == null) {
	$sql .= " AND DATE(transaction_date) BETWEEN DATE('$from') AND DATE(NOW())";
} else if ($from == null && $to != null) {
	$sql .= " AND DATE(transaction_date) BETWEEN DATE(NOW()) AND DATE('$to')";
} else if ($from != null && $to != null) {
	$sql .= " AND DATE(transaction_date) BETWEEN DATE('$from') AND DATE('$to')";
}
$sql .= " GROUP BY bills.bill_id ORDER BY transaction_date DESC, bill_id DESC";

$outstanding_total = 0;
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
$pdo = (new MyDBConnector())->getPDO();

$page = isset($_POST['page']) ? $_POST['page'] : 0;
$pageSize =  isset($_POST['PageSize']) ? $_POST['PageSize'] : 10;

$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
$stmt->execute();
$totalSearch = $stmt->rowCount();

$page = ($page > 0) ? $page : 0;
$offset = ($page > 0) ? $pageSize * $page : 0;
$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
$sql .= " LIMIT $offset, $pageSize";
$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
$stmt->execute();
	


$patient = (new PatientDemographDAO())->getPatient($pid, FALSE, $pdo);

$real_balance = $patient->getOutstanding();
$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);



if(!isset($_SESSION['checked_bill'])){$_SESSION['checked_bill'] = [];}
if(!isset($_SESSION['checked_bill_all'])){$_SESSION['checked_bill_all'] = [];}
$_SESSION['checked_bill'][$page] = !is_blank(@$_POST['tBill']) ? array_filter(@$_POST['tBill']) : (isset($_SESSION['checked_bill'][$page]) ? $_SESSION['checked_bill'][$page] : []) ;
$_SESSION['checked_bill_all'] = isset($_SESSION['checked_bill']) && is_array(array_filter($_SESSION['checked_bill'])) ? array_flip(array_flip(array_flatten($_SESSION['checked_bill']))) : [];
?>

<?php if($pageSize > 0) { ?>
<div id="billContent" class="__0 dataTables_wrapper">
<table class="table table-striped statementBills">
	<thead>
	<tr>
		<th style="word-break: break-word" nowrap><label title="Check all Items"><input type="checkbox" id="checkAllStatementItems"> Bill#</label></th>
		<th>Reference</th>
		<th style="word-break: break-word">Item</th>
		<th style="word-break: break-word">Date</th>
		<th style="word-break: break-word">Type</th>
		<th style="word-break: break-word" nowrap>Amount(<?= $currency?>)</th>
		<th style="word-break: break-word">Balance</th>
		<th style="word-break: break-word" nowrap>Responsible</th>
	</tr>
	</thead>
	<!--if num_rows == 0-->
	<?php if ($stmt->rowCount() == 0) { ?>
		<tr>
			<td colspan="8"><em class="warning-bar">No bill items matching the filter</em></td>
		</tr>
		<!--end if-->
	<?php } else {
		do {
			$item = (new InsuranceItemsCostDAO())->getInsuranceItem($row['item_code'], $row['patient_id']);
			$real_balance = $real_balance - $row['amount'];
			//$parentBill = (new BillDAO())->checkBill($row['bill_id'], true, $pdo);
			//
			//if ($parentBill && $parentBill->getPatient() != null){
			//  $parent_id = $parentBill->getParent();
			//}
			
			?>
			<!-- else start repeat-->
			<tr <?php if ($tType != "---" && $tType != "" && !in_array($row['transaction_type'], $tType)) {?>class="hide"<?php }?>>
				<td nowrap style="word-break: break-word">
					<label><input type="checkbox" name="tBill[]" <?= isset($_SESSION['checked_bill_all']) && in_array((int)$row['bill_id'], $_SESSION['checked_bill_all'] ) ? 'checked' :'' ?>  value="<?= (int)$row['bill_id'] ?>"<?php if ($row['transferred'] == '1' || !in_array($row['transaction_type'], ['credit', 'transfer-credit']) || !in_array($row['bill_active'], ['bill_active']) /*|| ($row['bill_id'] == $parent_id)*/) { ?> disabled<?php } ?> id="<?= (int)$row['bill_id']?>" > <?= (int)$row['bill_id']; ?>
					</label></td>
				<td style="word-break: break-word"><?= $row['payment_reference']; ?></td>
				<td width="20%" style="word-break: break-word">
					<?= ($row['item_code'] && $item != null) ? '[<b>'. truncate( $item->type, -1, false) .'</b>]':'' ?>
					<?= str_replace('Diff: ', '<strong>Diff: </strong>', $row['description']); ?>
					<?php if ($this_user->hasRole($protect->bill_auditor) && (in_array($row['bill_source_id'], [5,9,10,16,26]) || $row['misc']==1) && $row['transaction_type'] == 'credit' && !in_array($row['bill_active'], ['not_active'])){//admission, misc, registration, nursing svc, feeding ?><span class="reverseBill"> | <a data-id="<?= $row['bill_id']; ?>" href="javascript:">Cancel</a></span><?php } ?>
				</td>
				<td nowrap style="word-break: break-word"><?= date(MainConfig::$dateTimeFormat, strtotime($row['transaction_date'])); ?></td>
				<td style="word-break: break-word" title="<?=$row['transaction_type']?>"><?= explode("-", strtoupper($row['transaction_type']))[0] ; ?><?php if (in_array($row['transaction_type'], ['debit','discount']) ) { ?> |
						<a href="javascript:;" onclick="Print('receipt','<?= $row['bill_id'] ?>','copy')">Receipt</a>  <?php } ?>
				</td>
				<td class="amount" nowrap style="word-break: break-word"><?= number_format(abs($row['amount']), 2); ?></td>
				<td class="amount" nowrap style="word-break: break-word"><?= number_format(( $real_balance +   $row['amount']), 2); ?></td>
				<td><?= ($row['receiver'] == '') ? '' : (new StaffDirectoryDAO())->getStaff($row['receiver'])->getShortname() ?></td>
			</tr>
			<!--end repeat-->
		<?php } while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)); ?>
	<?php } ?>
</table>

	<div class="billList dataTables_wrapper no-footer">
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

<?php } else{ ?>
	<table class="table table-striped">
		<tr>
			<td>
				<div class="warning-bar">Nothing found!</div>
			</td>
		</tr>
	</table>
<?php } ?>

<div style="margin-top:10px">
	<button type="button" class="btn" onclick="Print('stmnt')"><i class="icon-print"></i> PRINT STATEMENT</button>
	<button type="button" class="btn" id="transferBtn"><i class="icon-exchange"></i> TRANSFER</button>
	<button type="button" class="btn" id="drtBtn"><i class="icon-exchange"></i> D. R. G</button>
	<button type="button" class="btn" id="requestPABtn"><i class="icon-message"></i> REQUEST AUTHORIZATION</button>
	<button type="button" class="btn" id="PrivateClaimsBtn"><i class="icon-message"></i> Process Claims</button>
</div>

<script>

	$(document).on('click', '.billList.dataTables_wrapper a.paginate_button', function (d) {
		if(!d.clicked){
			var page = $(this).data("page");
			if(!$(this).hasClass("disabled")){
				var postData = $("#filterForm").serializeObject();
				postData['page'] = page;
				postData['pid'] = '<?= @$_POST['pid'] ?>';
				var selectedItems = [];
				_.each($('[name="tBill[]"]:checked:not(:disabled)'), function (obj) {
					selectedItems.push(parseInt($(obj).val()));
				});
				postData['tBill'] = selectedItems;
				$.post('/billing/filter_statement.php', postData,  function (response) {
					$('#billContent').html($(response).filter('.__0').html());
				});
			}
			d.clicked = true;
		}

	});
	
	$('#checkAllStatementItems').live('change', function(e){
		if(!e.handled){
			var $this = $(this);
			if($this.is(':checked')){
				_.each($('[name="tBill[]"]:not(:disabled)'), function (obj) {
					$(obj).prop('checked', true).iCheck('update');
				});
			} else {
				_.each($('[name="tBill[]"]:not(:disabled)'), function (obj) {
					$(obj).prop('checked', false).iCheck('update');
				});
			}
			e.handled = true;
		}
	});

	$('.statementBills .reverseBill a').live('click', function (e) {
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
						}else if(response==='error1'){
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
</script>
