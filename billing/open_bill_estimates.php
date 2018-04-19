<?php
/**
 * Created by PhpStorm.
 * User: nnamdi
 * Date: 4/24/17
 * Time: 1:52 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/EstimatedBillLineDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/EstimatedBillsDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.main.php';

$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;

$open_bills = (new EstimatedBillsDAO())->getOpenBillEstimates(null,$page,$pageSize);

$totalSearch = $open_bills->total;

?>

<div id="openBillEstimates">
    <table class="table table-striped">
        <thead>
        <tr>
            <td>ESB#</td>
            <td>Patient</td>
            <td>date created</td>
            <td>Created By</td>
            <td>Valid Till</td>
            <td>Total</td>
            <td>*</td>
        </tr>
        </thead>

        <?php
        foreach ($open_bills->data as $bill_est){
            ?>
            <tr>
                <td><?=$bill_est->escode?></td>
                <td><a href="/patient_profile.php?id=<?=$bill_est->PatientID?>"><?= $bill_est->Patient ?></a></td>
                <td><?=$bill_est->created_on?></td>
                <td><?=(new EstimatedBillsDAO())->getEstimatedBillById($bill_est->esid)->getCreatedBy()->getFullname()?></td>
                <td><?=$bill_est->period?></td>
                <td><?=$bill_est->total?></td>
                <td>  <a target="_blank"  href="/billing/estimated_bill_sheet.php?id=<?=$estimated_bill->esid?>&pid=<?=$estimated_bill->PatientID?>&reprint">View-Print</a></td>
            </tr>
            <?php
        }
        ?>


    </table>

    <div class="list12 dataTables_wrapper no-footer">
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
</div>
<script type="text/javascript">
    function post(e){
        if (!e.handled) {
            reload(0);
            e.handled = true;
        }
    }

    $(document).on('click', '.list12.dataTables_wrapper a.paginate_button', function (e) {
        if (!e.clicked) {
            var page = $(this).data("page");
            if (!$(this).hasClass("disabled")) {
                reload(page);
            }
            e.clicked = true;
        }
    });

    function reload(page) {
        data = {page: page, patient_id: $('[name="patient_id"]').val(), staff_id: $('[name="staff_id"]').val(), bill_source_ids: $('#bill_source_ids').val(), date_from: $('#date_start').val(), date_to: $('#date_stop').val()};
        $.post("/billing/open_bill_estimates.php", data, function (s) {
            $("#openBillEstimates").html($(s).filter("#openBillEstimates").html());
        });
    }
</script>