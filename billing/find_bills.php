<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();
if ($_POST) {
	if (is_blank($_POST['q'])) {?>
		<br>
		<div class="warning-bar">Type a search</div>
		<?php exit;
	} else {
		$page = isset($_POST['page']) ? intval($_POST['page']) : 0;
		$pageSize = 10;
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
		$data = (new BillDAO())->searchPatientBill($_POST['q'], $page, $pageSize);
		$totalSearch = $data->total;
		$bills = $data->data;
		if ($totalSearch > 0) { ?>
			<div id="bill_result_">
				<table class="small table table-hover table-striped">
					<thead class="ui-bar-c">
					<tr>
						<th>BILL#</th>
						<th>Patient</th>
						<th>Date</th>
						<th>Details</th>
						<th class="amount">Amount(<?= $currency ?>)</th>
						<th>Type</th>
						<th>PAYING</th>
					</tr>
					</thead>
					<?php foreach ($bills as $bill) {//$bill=new Bill; ?>
						<tr class="small">
							<td><?= $bill->getId() ?></td>
							<td><a class="name" href="/patient_profile.php?id=<?= $bill->getPatient()->getId() ?>"><?= $bill->getPatient()->getFullname() ?></a>
							</td>
							<td><?= date("Y/m/d h:ia", strtotime($bill->getTransactionDate())) ?></td>
							<td>
								<small><?= $bill->getDescription() ?></small>
							</td>
							<td class="amount"><?= abs($bill->getAmount()) ?></td>
							<td><?= strtoupper($bill->getTransactionType()) ?></td>
							<td><?= $bill->getBilledTo()->getName();//$i_mgr->getInsuranceSchemeName() ?></td>
						</tr>
					<?php } ?>
				</table>
				<div class="list10 dataTables_wrapper no-footer">
					<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
						results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
					</div>
					<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
						<a id="DataTables_Table_1_first" data-page="0"
						   class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
							records</a>
						<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>"
						   class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
							records</a>
						<a id="DataTables_Table_1_last"
						   class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
						   data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
						<a id="DataTables_Table_1_next"
						   class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
						   data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
					</div>
				</div>
			</div>
			<?php exit;
		} else {
			?>
			<br>
			<div class="warning-bar">No billing information found</div>
			<?php exit;
		}
	}
} ?>
<script type="text/javascript">
	$(document).ready(function () {
		//$('input#q').focus();
	});

	function start() {
		$.event.trigger("ajaxSend");
	}

	function done(s) {
		$('#fetch').html(s);
		$.event.trigger("ajaxStop");
	}

	$(document).on('click', '.list10.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.post("/billing/find_bills.php", {page: page, q: $("#q").val()}, function (s) {
					$("#bill_result_").html($(s).filter("#bill_result_").html());
				});
			}
			e.clicked = true;
		}
	});

</script>
<form action="find_bills.php" method="post" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done});">
	<div class="input-append">
		<input type="text" name="q" id="q" autofocus autocomplete="off" placeholder="Patient EMR or Name or bill #" style="width: 90%;">
		<button class="btn" type="submit" name="button" id="button" style="width: 10%;">Find Bill &raquo;</button>
	</div>
</form>
<div id="fetch"></div>
