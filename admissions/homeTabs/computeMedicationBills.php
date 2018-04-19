<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/19/15
 * Time: 12:17 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/BillDAO.php';
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 500;
$data = (new InPatientDAO())->getUncomputedBillInPatients(TRUE, $page, $pageSize);

$totalSearch = $data->total;
$c_bills = $data->data;
?>
<div id="admission_container"><table class="table table-striped">
    <thead><tr>
        <th>Patient</th>
        <th>*</th>
    </tr></thead>
    <?php foreach($c_bills as $ip){?>
        <tr>
            <td><a href="/patient_profile.php?id=<?= $ip->getPatient() ? $ip->getPatient()->getId() : '' ?>"><?= ( $ip->getPatient()->getFullname() ) ?></a></td>
            <td><a href="javascript:;" onclick="openBoxy(<?= $ip->getId() ?>)">Compute Bills &raquo;</a> </td>
        </tr>
    <?php }?>
</table>
	<div class="list1 dataTables_wrapper no-footer">
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

<script type="text/javascript">
    var openBoxy = function(ipId){
        Boxy.load('/admissions/dialogs/boxy.computeDrugBill.php?ipid='+ipId);
    }

    $(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
	    if (!e.clicked) {
		    var page = $(this).data("page");
		    if (!$(this).hasClass("disabled")) {
			    $.post("/admissions/homeTabs/computeMedicationBills.php", {page: page}, function (s) {
				    $("#admission_container").html($(s).filter("#admission_container").html());
			    });
		    }
		    e.clicked = true;
	    }
    });
</script>