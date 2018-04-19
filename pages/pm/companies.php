<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/28/16
 * Time: 8:59 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CompanyDAO.php';
$companies = (new CompanyDAO())->all();
?>
<section>
	<div class="pull-right">
		<a href="javascript:" name="addNewBtn" class="company_add">Add New Company</a>
	</div>
	<table class="table table-striped">
		<thead><tr><th>Company Name</th><th>*</th></tr></thead>
		<?php foreach($companies as $company){?><tr><td><?= $company->getName()?></td><td><a data-id="<?=$company->getId()?>" href="javascript:" class="editCompany">Edit</a></td></tr><?php }?>
	</table>
</section>
<script type="text/javascript">
	$(document).on('click', 'a[href="javascript:"][name="addNewBtn"].company_add', function (e) {
		if(!e.handled){
			Boxy.load('/pages/pm/company_new.php', {afterHide: function () {
				loadCompanies();
			}});
			e.handled = true;
		}
	}).on('click','a[data-id][href="javascript:"].editCompany', function (e) {
		var $id = $(this).data("id");
		if(!e.handled){
			Boxy.load('/pages/pm/company_edit.php?id='+$id, {afterHide: function () {
				loadCompanies();
			}});
			e.handled = true;
		}
	});
</script>
