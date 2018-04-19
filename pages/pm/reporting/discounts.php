<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/21/15
 * Time: 12:41 PM
 */

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VoucherBatchDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VoucherDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/json2csv.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$vouchers=array();

$from = isset($_POST['from']) && !is_blank($_POST['from']) ? $_POST['from']:'1970-01-01';

$to = (isset($_POST['to']) && !is_blank($_POST['to']))? $_POST['to'] : $to = date("Y-m-d");

$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 0;
$pageSize = 10;
$generator = isset($_POST['staff_id']) && !is_blank($_POST['staff_id']) ? $_POST['staff_id'] : NULL;
$Vouchers = (new VoucherDAO())->byTypeAndDate(['discount'], $from, $to, $generator);
$totalSearch = $Vouchers->total;
$generators = (new StaffDirectoryDAO())->getStaffs();

if(isset($_REQUEST['ex_']) && isset($Vouchers->data) && $Vouchers->total >0) {
    foreach ($Vouchers->data as $k => $report) {
        $rpt = array();
        $rpt['Description'] = $report->getBatch()->getDescription();
        $rpt['Generator'] = $report->getBatch()->getGenerator()->getFullname();
        $rpt['Amount'] = $report->getBatch()->getAmount();
        $rpt['Used Date'] = date("Y/m/d hA", strtotime($report->getUsedDate()));
        $vouchers[] = $rpt;
    }
}
if(isset($_REQUEST['ex_'])) {
    ob_end_clean();
    $JSON2CSV = new JSON2CSVutil;
    $JSON2CSV->readJSON(json_encode($vouchers));
    $JSON2CSV->flattenDL("Discount_Reports.csv");
    exit;
} ?>
<style type="text/css">
    .filter .btn {float: right;margin-top: 24px;}
    .filter .span1 {margin-left: 0;}
    #exportIT {margin-left:1%;width:8%;}
</style>

<div ><a class="btn-link" href="/pm/reporting/index.php">&laquo; Back</a></div>
<form id="filterForm" class="document" method="post" action="/pm/reporting/discounts.php">
    <h4>Discounts Report</h4>
    <div class="clearfix filter row-fluid">
        <label class="span2">From<input type="text" name="from" value="<?=(isset($_REQUEST['from'])?$_REQUEST['from']:'') ?>" id="from" placeholder="Select start date"/></label>
        <label class="span2">To:<input type="text" name="to" value="<?=(isset($_REQUEST['to'])?$_REQUEST['to']:'') ?>" id="to" placeholder="Select end date" disabled="disabled"/></label>
        <label class="span4">
            Filter by Generator
            <select id="staff_id" name="staff_id" data-placeholder="Select Voucher Generator">
                <option></option>
                <?php foreach ($generators as $k=>$s) { //$s=new StaffDirectory()?>
                    <option value="<?= $s->getId() ?>"<?= isset($_REQUEST['staff_id']) && $_REQUEST['staff_id'] == $s->getId() ? ' selected="selected"' : '' ?>><?= $s->getFullname() ?></option>
                <?php } ?>
            </select>
        </label>
        <label class="span2">
            Type
        </label>
        <button class="btn span1" id="exportIT" type="button"><i class="fa fa-file-excel-o"></i> Export</button>
        <button type="submit" class="btn span1">Show</button>
    </div>
</form>
<div class="document">
    <?php if(isset($_REQUEST['from']) && isset($_REQUEST['to']) && $_REQUEST['from']!=''){ ?>
        <h3 style="text-align: center">Discount Report for
            <?php if(isset($_REQUEST['staff_id']) && $_REQUEST['staff_id']!=''){ ?>
                <br>Discount Vouchers by: <?= (new StaffDirectoryDAO())->getStaff($_REQUEST['staff_id'])->getFullname() ?>
            <?php } ?>
            <br>PERIOD: <span> [<?php echo date("Y M d", strtotime($_REQUEST['from'])) . ' - ' .( ($_REQUEST['to']=='')? date('Y M d') : date("Y M d", strtotime($_REQUEST['to'])) ) ?>]</span></h3>
    <?php } ?>
    <div id="area10">
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>Description</th>
                <th>Generator</th>
                <th class="amount">Amount</th>
                <th>Used Date</th>
            </tr>
            </thead>
            <?php if(isset($Vouchers) && ($Vouchers->total)>0){
                foreach ($Vouchers->data as $k=>$report) { // $report=new Voucher(); /*4156*/?>
                    <tr>
                        <td><?= $report->getBatch()->getDescription()?></td>
                        <td><?= $report->getBatch()->getGenerator()->getFullname()?></td>
                        <td class="amount"><?= $report->getBatch()->getAmount()?></td>
                        <td><?= date("Y/m/d ha", strtotime($report->getUsedDate())) ?></td>
                    </tr>
                <?php }
            } ?>
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

</div>
<script type="text/javascript">
    $(document).ready(function () {
        // $('table').dataTable();
        $("#from").datetimepicker({
            format:'Y-m-d',
            formatDate:'Y-m-d',
            timepicker:false,
            onChangeDateTime:function(dp,$input){
                if($input.val().trim()!=""){
                    $("#to").val('').removeAttr('disabled');}
                else {
                    $("#to").val('').attr({'disabled':'disabled'});
                }
            }
        });
        $("#to").datetimepicker({
            format:'Y-m-d',
            formatDate:'Y-m-d',
            timepicker:false,
            onShow:function(ct){
                this.setOptions({ minDate: $("#from").val()? $("#from").val():false});
            },
            onSelectDate:function(ct,$i){

            }
        });

        if($("#from").val().trim()!=""){$("#to").removeAttr('disabled');}

        $("#staff_id").select2({
            allowClear: true,
            width: '100%'
        }).change(function () {
            $('form').submit();
        });
        $('#exportIT').on('click', function(e){
            if(!e.handled) {
                window.open('/pages/pm/reporting/discounts.php?ex_=csv&from='+$("#from").val()+'&to='+$("#to").val()+'&staff_id='+$("#staff_id").val(), '_blank');
                e.handled = true;
                e.preventDefault();
            }
        });
    });
    $(document).on('click', '.list11.dataTables_wrapper a.paginate_button', function (e) {
        if (!e.clicked) {
            var page = $(this).data("page");
            if (!$(this).hasClass("disabled")) {
                $.get("/pages/pm/reporting/discounts.php", {page: page}, function (s) {
                    $("#area10").html( $(s).filter("#area10").html() );
                });
            }
            e.clicked = true;
        }
    });
</script>