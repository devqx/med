<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/functions/func.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true);

sessionExpired(); ?>
<div id="billMenuBar" class="mini-tab">
	<a class="tab on" href="./">Find Patient Bill</a>
	<a class="tab" href="javascript:" id="outStandingLink">Patients' outstanding</a>
	<a class="tab" href="javascript:" id="insuranceBillInk">Insurance Bills</a>
	<a class="tab" href="javascript:" id="pa_codes" data-href="/billing/pa_codes.php">P.A. Codes</a>
	<a class="tab" href="javascript:" id="linkUnReviewedBills" data-href="/billing/unreviewed-transactions.php">Review Transactions</a>
    <a class="tab" href="javascript:;" id="estimated_bills" data-href="/billing/estimated-bills.php">Estimated Bills</a>
	<?php if ($staff->hasRole($protect->accounts)) { ?><a class="tab pull-right" href="javascript:;" id="addBillInk">New Misc. Transaction</a><?php } ?>
</div>
<div id="billContent">

</div>
<div id="billDoc" class="document">

</div>
<div id="container2">

</div>