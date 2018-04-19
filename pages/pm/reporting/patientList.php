<?php
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';

$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 500;
$pats = (new PatientDemographDAO())->getPatientFlatLists($page, $pageSize);
$totalSearch = $pats->total;
$p_lists = $pats->data;
?>
<h3>List of all patients (<?= number_format(sizeof($p_lists))?>)</h3>
<div id="patient_container">
<table id="inventoryreport" class="table table-striped document">
    <thead>
        <tr><th>Patient Name</th><th>Sex</th><th>Phone</th><th>Email</th><th>Address</th></tr>
    </thead>
    <tbody>
        <?php 
            if(sizeof($p_lists)> 0){
                foreach($p_lists as $key=>$pat){ ?>
                    <tr>
                        <!--<td></td>-->
                        <td align="left"><a href="/patient_profile.php?id=<?= $pat->getId() ?>"><?php echo $pat->getLname()." ".$pat->getFname()." ".$pat->getMname() ?></a></td>
                        <td><?= $pat->getSex() ?></td>
                        <td> <?= $pat->getPhoneNumber() ?></td>
                        <td> <?= $pat->getEmail() ?></td>
                        <td> <?= $pat->getAddress() ?></td>
                    </tr>
                <?php }
             }else{
                 echo "<tr><td colspan='5' align='center'><em>Zero patients found!!!</em></td></tr>";
             }?>
    </tbody>
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
<script>
	$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.post("/pages/pm/reporting/patientList.php", {page: page}, function (s) {
					$("#patient_container").html($(s).filter("#patient_container").html());
				});
			}
			e.clicked = true;
		}
	});
</script>