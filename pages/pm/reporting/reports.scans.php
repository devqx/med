<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 3/18/15
 * Time: 3:41 PM
 */

include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ScanCategoryDAO.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';

$date=((isset($_REQUEST['from']) && $_REQUEST['from']!='' && isset($_REQUEST['to']) && $_REQUEST['to']!='')? TRUE:FALSE);
$from = $_POST['from'];
$to = $_POST['to'];
$category_id = $_POST['category_id'];

$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 15;
$totalSearch = 0;
$imagingReport = array();
if($date ===TRUE){
    $data = (new PatientScanDAO())->findScansByDateCategory($from, $to, $category_id, $page, $pageSize);
    $totalSearch = $data->total;
    $imagingReport = $data->data;
}

$imaging_categories = (new ScanCategoryDAO())->getCategories();
?>
<style type="text/css">
    .filter .btn {float: right;margin-top: 24px;white-space: nowrap;}
    /*.filter .span1 {margin-left: 0;}*/
    /*#exportIT {margin-left:1%;width:8%;}*/
</style>
<div ><a class="btn-link" href="/pm/reporting/index.php">&laquo; Back</a></div>


<form id="filterForm" class="document" method="post" action="/pm/reporting/reports.scans.php">
    <h4>Imaging/Radiology Report</h4>
    <div class="clearfix filter row-fluid">
        <label class="span3">From<input type="text" name="from" value="<?=(isset($_REQUEST['from'])?$_REQUEST['from']:'') ?>" id="from" placeholder="Select start date"/></label>
        <label class="span3">To:<input type="text" name="to" value="<?=(isset($_REQUEST['to'])?$_REQUEST['to']:'') ?>" id="to" placeholder="Select end date" disabled="disabled"/></label>
        <label class="span4">
            Filter by Category
            <select id="category_id" name="category_id" data-placeholder="Select category">
                <option></option>
                <?php foreach ($imaging_categories as $k=>$cats) { ?>
                    <option value="<?= $cats->getId() ?>"<?= isset($_REQUEST['category_id']) && $_REQUEST['category_id'] == $cats->getId() ? ' selected="selected"' : '' ?>><?= $cats->getName() ?></option>
                <?php } ?>
            </select>
        </label>
        <button class="btn span1 " id="exportIT" type="button">Export</button>
        <button class="btn span1 " id="showIT" type="submit">Show</button>
    </div>
</form>
<div class="document">
    <?php if(isset($_REQUEST['from']) && isset($_REQUEST['to']) && $_REQUEST['from']!=''){ ?>
    <h3 style="text-align: center">Imaging/Radiology report for
        <?php if(isset($_REQUEST['category_id']) && $_REQUEST['category_id']!=''){ ?>
            <br><?= (new ScanCategoryDAO())->getCategory($_REQUEST['category_id'])->getName() ?>
        <?php } else { ?>All categories<?php } ?>
        <br>PERIOD: <span> [<?php echo date("Y M d", strtotime($_REQUEST['from'])) . ' - ' .( ($_REQUEST['to']=='')? date('Y M d') : date("Y M d", strtotime($_REQUEST['to'])) ) ?>]</span></h3>
    <?php } ?>
    <div id="scan_report_container">
    <?php if($totalSearch < 1){
        echo '<div class="notify-bar">There are no imaging reports</div>';
    } else { ?>
    <div class="notify-bar"><i class="icon-info-sign"></i> <?=$totalSearch ?> Imaging Requests</div>
    <table class="table table-striped table-hover no-footer">
        <thead>
        <tr>
            <th>Request Date</th>
            <th>Approved Date</th>
            <th>Scan</th>
            <th>Staff</th>
            <th>Patient</th>
            <th>Scheme</th>
            <th>Amount</th>
        </tr>
        </thead>
        <tbody>
        <?php
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
        $iItem = new InsuranceItemsCostDAO();
        foreach ($imagingReport as $k=>$report) { ?>
            <tr>
                <td><?= date('jS M, Y', strtotime($report->request_date)) ?></td>
                <td><?= $report->approved_date ? date('jS M, Y', strtotime($report->approved_date)) : "" ?></td>
                <td><?= $report->scanName ?></td>
                <td><?= $report->staffFullName ?></td>
                <td><?= $report->patientFullName ?></td>
                <td><?= $report->scheme_name ?></td>
                <td><?= $iItem->getItemPriceByCode($report->billing_code, $report->patient_id, TRUE)  ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <div class="list1 dataTables_wrapper no-footer">
        <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page+1?> of <?= ceil($totalSearch / $pageSize)?>)</div>
        <div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
            <a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_last" class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_next" class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
        </div>
    </div>
    <?php } ?>
    </div>
</div>
<script type="text/javascript">
    $(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function(e){
        if(!e.clicked){
            var page = $(this).data("page");
            if(!$(this).hasClass("disabled")){
                $.post('/pages/pm/reporting/reports.scans.php',{from:'<?= $_POST['from'] ?>', to:'<?= $_POST['to'] ?>', category_id: '<?= $_POST['category_id'] ?>', page:page }, function(s){
                    $('#scan_report_container').html($(s).find('#scan_report_container'));
                });
            }
            e.clicked=true;
        }
    });
    $(document).ready(function () {
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
//                $("#showIT").show();
            },
            onSelectDate:function(ct,$i){

            }
        });

        if($("#from").val().trim()!=""){
            $("#to").removeAttr('disabled');
        }

        $("#category_id").select2({
            allowClear: true,
            width: '100%'
        });
        $('#exportIT').on('click', function(e){
            if(!e.handled) {
                window.open('/excel.php?dataSource=scans&filename=Scans_Report&from=<?=(isset($_REQUEST['from']))?$_REQUEST['from']:''?>&to=<?=(isset($_REQUEST['to']))?$_REQUEST['to']:''?>&category=<?=(isset($_REQUEST['category_id']))?$_REQUEST['category_id']:''?>', '_blank');
                e.handled = true;
                e.preventDefault();
            }
        });
    });
</script>