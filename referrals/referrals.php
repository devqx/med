<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/27/15
 * Time: 1:07 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralCompanyDAO.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Referral.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ReferralCompany.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffSpecialization.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';

$page = !is_blank(@$_GET['page']) ? intval(@$_GET['page']) : 0;
$pageSize = 10;

$filterCompanyRef = isset($_GET['filterCompanyRef']) && !is_blank($_GET['filterCompanyRef']) ? $_GET['filterCompanyRef'] : null;
$filterRef = isset($_GET['filterRef']) && !is_blank($_GET['filterRef']) ? $_GET['filterRef'] : null;

$companies = (new ReferralCompanyDAO())->all($page, $pageSize, $filterCompanyRef);
$all = (new ReferralDAO())->all($page, $pageSize, $filterRef);

$protect = new Protect();
if (!isset($_SESSION)) {
	@session_start();
}
$this_user = null;
if (isset($_SESSION['staffID'])) {
	$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
}

if ($this_user && ($this_user->hasRole($protect->referral_mgt))) {
	?>
	<p class="pull-right">
		<a href="javascript:" class="action" id="newReferralBtn">New Referral</a>
		<a href="javascript:" class="action" id="newReferralCompBtn">New Referral Company</a>
	</p>
	<div class="clear"></div>
	<div>
		<div id="refCompanyData" class="document clearBoth clear dataTables_wrapper"><h6 class="pull-left">Referral Companies</h6>
			<div class="pull-right"><input type="search" id="filterCompanyRef" placeholder="Filter" name="filterCompanyRef" value="<?= isset($_GET['filterCompanyRef']) ? $_GET['filterCompanyRef'] : '' ?>"> </div>
			<table class="table-bordered table-hover table">
				<thead>
				<tr>
					<th>Company Name</th>
					<th>Address</th>
					<th>Phone</th>
					<th>Email</th>
					<th>Bank Details</th>
					<th>*</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($companies->data as $company) {//$company = new ReferralCompany();?>
					<tr>
						<td><a href="javascript:" data-title="<?= $company->getName() ?>" data-class="company" data-id="<?= $company->getId() ?>"><?= $company->getName() ?></a></td>
						<td><?= $company->getAddress() ?></td>
						<td><?= $company->getContactPhone() ?></td>
						<td><?= $company->getEmail() ?></td>
						<td><?= $company->getBankName() ?> [<?= $company->getAccountNumber() ?>]</td>
						<td><a href="javascript:" class="ref_edit_company" data-href="referral_edit.php?type=company&id=<?= $company->getId() ?>">Edit</a></td>
					</tr>
				
				<?php } ?>
				</tbody>
			</table>
			<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $companies->total ?> results found (Page <?= $companies->page + 1 ?> of <?= ceil($companies->total / $companies->pageSize) ?>)</div>
			<div class="resultsPagerOpen no-footer dataTables_paginate">
				<div id="DataTables_Table_0_paginate" class="dataTables_paginate paging_simple_numbers">
					<a data-div="company" id="DataTables_Table_0_first" data-page="0" class="paginate_button previous <?= (($companies->page + 1) == 1) ? "disabled" : "" ?>">First <?= $companies->pageSize ?> records</a>
					<a data-div="company" id="DataTables_Table_0_previous" data-page="<?= ($companies->page) - 1 ?>" class="paginate_button previous <?= (($companies->page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $companies->pageSize ?> records</a>
					<a data-div="company" id="DataTables_Table_0_last" class="paginate_button next <?= (($companies->page + 1) == ceil($companies->total / $companies->pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($companies->total / $companies->pageSize) - 1 ?>">Last <?= $companies->pageSize ?>
						records</a>
					<a data-div="company" id="DataTables_Table_0_next" class="paginate_button next <?= (($companies->page + 1) >= ceil($companies->total / $companies->pageSize)) ? "disabled" : "" ?>" data-page="<?= ($companies->page) + 1 ?>">Next <?= $companies->pageSize ?> records</a>
				</div>
			</div>
		</div>

		<div id="refIndData" class="document clearBoth clear dataTables_wrapper"><h6 class="pull-left">Referrals</h6>
			<div class="pull-right"><input type="search" id="filterRef" placeholder="Filter" name="filterRef" value="<?= isset($_GET['filterRef']) ? $_GET['filterRef'] : '' ?>"> </div>
			<table class="table-bordered table-hover table">
				<thead>
				<tr>
					<th>Name</th>
					<th>Company</th>
					<th>Phone</th>
					<th>Email</th>
					<th>Specialization</th>
					<th>Bank Details</th>
					<th>*</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($all->data as $referral) { ?>
					<tr>
						<td><?= $referral->getName() ?></td>
						<td><?= $referral->getCompany() ? $referral->getCompany()->getName() : 'N/A' ?></td>
						<td><?= $referral->getPhone() ?></td>
						<td><?= $referral->getEmail() ?></td>
						<td><?= $referral->getSpecialization() ? $referral->getSpecialization()->getName() : '' ?></td>
						<td><?= $referral->getBankName() ?> [<?= $referral->getAccountNumber() ?>]</td>
						<td>
							<a href="javascript:" class="ref_edit_single" data-href="referral_edit.php?type=personal&id=<?= $referral->getId() ?>">Edit</a>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			<div class="dataTables_info" role="status" aria-live="polite"> <?= $all->total ?> results found (Page <?= $page + 1 ?> of <?= ceil($all->total / $pageSize) ?>)</div>
			<div class="resultsPagerOpen no-footer dataTables_paginate">
				<div class="dataTables_paginate paging_simple_numbers">
					<a data-div="single" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?> records</a>
					<a data-div="single" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?> records</a>
					<a data-div="single" class="paginate_button next <?= (($page + 1) == ceil($all->total / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($all->total / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
					<a data-div="single" class="paginate_button next <?= (($page + 1) >= ceil($all->total / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
				</div>
			</div>
		</div>
	</div>
	<div class="clear"></div>
<?php } else { ?>
	<?= $protect->ACCESS_DENIED ?>
<?php } ?>

<script>
	var reload = function (where) {
		url = '/referrals/referrals.php?' + $.param(postData);
		$("#" + where).load(url + " #" + where + ">*", "");
	};
	
	$(document).on('keydown', '#filterRef', function(e){
		if(e.keyCode === 13){
			postData['filterRef'] = $(this).val();
			reload('refIndData');
		}
	}).on('keydown', '#filterCompanyRef', function(e){
		if(e.keyCode === 13){
			postData['filterCompanyRef'] = $(this).val();
			reload('refCompanyData');
		}
	});

	function starting() {
	}

	var postData = {
		'page': 0,
	};

	$(document).on('click', '.resultsPagerOpen.dataTables_paginate a.paginate_button', function (e) {
		if (!$(this).hasClass("disabled") && !e.handled) {
			postData["page"] = $(this).data("page");
			if ($(this).data("div") === "company") {
				reload('refCompanyData');
			} else {
				reload('refIndData');
			}
			e.handled = true;
		}
	});

	function savedReferral(s) {
		var status = s.split(":")[0];
		var response = s.split(":")[1];
		if (status === "error") {
			Boxy.alert(response);
		} else if(status === "success"){
			Boxy.get($(".close")).hideAndUnload();
			Boxy.info(response, function () {
				postData["page"] = 0;
				reload('refCompanyData');
			});
		}
	}
	$(document).ready(function () {
		$('a[class="ref_edit_company"]').live('click', function (e) {
			if (!e.handled) {
				Boxy.load($(this).data("href"), {
					title: "New Referral", afterHide: function () {
						postData["page"] = 0;
						reload('refCompanyData');
					}
				});
				e.handled = true;
			}
		});
		$('a[class="ref_edit_single"]').live('click', function (e) {
			if (!e.handled) {
				Boxy.load($(this).data("href"), {
					title: "New Referral", afterHide: function () {
						postData["page"] = 0;
						reload('refIndData');
					}
				});
				e.handled = true;
			}
		});

		$('a[data-class="company"]').live('click', function (e) {
			if (!e.handled) {
				Boxy.load("company_details.php?c_id=" + $(this).data("id"), {title: $(this).data("title")});
				e.handled = true;
			}
		});

		$('#newReferralBtn').live('click', function (e) {
			if (!e.handled) {
				Boxy.load('/referrals/referral_new.php', {
					title: "New Referral", afterHide: function () {
						postData["page"] = 0;
						reload('refIndData');
					}
				});
				e.handled = true;
			}
		});
		$('#newReferralCompBtn').live('click', function (e) {
			if (!e.handled) {
				Boxy.load('/referrals/referral_company_new.php', {
					title: "New Referral Company", afterHide: function () {
						postData["page"] = 0;
						reload('refCompanyData');
					}
				});
				e.handled = true;
			}
		})
	})
</script>
