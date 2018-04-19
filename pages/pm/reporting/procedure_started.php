<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 5/2/17
 * Time: 11:19 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ProcedureCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientProcedureDAO.php';

$procedure_categories = (new ProcedureCategoryDAO())->all();
$date=((isset($_REQUEST['from']) && $_REQUEST['from']!='' && isset($_REQUEST['to']) && $_REQUEST['to']!='')? TRUE:FALSE);
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 15;
$totalSearch = 0;
$procedureReport = array();
if($date ===TRUE){
    $data = (new PatientProcedureDAO())->getStartedProceduresReport($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['category_id'], $page, $pageSize);
    $totalSearch = $data->total;
    $procedureReport = $data->data;
}
?>
<style type="text/css">
    .filter .btn {float: right;margin-top: 24px;white-space: nowrap;}
    /*.filter .span1 {margin-left: 0;}*/
    #exportIT {margin-left:1%;width:8%;}
</style>
<div><a class="btn-link" href="/pm/reporting/index.php">&laquo; Back</a></div>

<form id="filterForm" class="document" method="post" action="/pm/reporting/procedure_started.php">
    <h4>Procedures Report</h4>
    <div class="clearfix filter row-fluid">
        <label class="span2">From<input type="text" name="from" value="<?=(isset($_REQUEST['from'])?$_REQUEST['from']:'') ?>" id="from" placeholder="Select start date"/></label>
        <label class="span2">To:<input type="text" name="to" value="<?=(isset($_REQUEST['to'])?$_REQUEST['to']:'') ?>" id="to" placeholder="Select end date" disabled="disabled"/></label>
        <label class="span3">
            Filter by Categories
            <select id="category_id" name="category_id" data-placeholder="Select category">
                <option></option>
                <?php foreach ($procedure_categories as $k=>$refs) { ?>
                    <option value="<?= $refs->getId() ?>"<?= isset($_REQUEST['category_id']) && $_REQUEST['category_id'] == $refs->getId() ? ' selected="selected"' : '' ?>><?= $refs->getName() ?></option>
                <?php } ?>
            </select>
        </label>
        <button class="btn span" id="exportIT" type="button"><i class="fa fa-file-excel-o"></i> Export</button>
        <button type="submit" class="btn span1">Show</button>
    </div>
</form>
<div class="document">
    <?php if(isset($_REQUEST['from']) && isset($_REQUEST['to']) && $_REQUEST['from']!=''){ ?>
        <h3 style="text-align: center">Procedures report for
            <?php if(isset($_REQUEST['category_id']) && $_REQUEST['category_id']!=''){ ?>
                <br>Category: <?= (new ProcedureCategoryDAO())->get($_REQUEST['category_id'])->getName() ?>
            <?php } ?>
            <br>PERIOD: <span> [<?php echo date("Y M d", strtotime($_REQUEST['from'])) . ' - ' .( ($_REQUEST['to']=='')? date('Y M d') : date("Y M d", strtotime($_REQUEST['to'])) ) ?>]</span></h3>
    <?php } ?>
    <div id="procedure_report_container">
        <?php if($totalSearch < 1){
            echo '<div class="notify-bar">There are no procedure reports</div>';
        } else { ?>
            <div class="notify-bar"><i class="icon-info-sign"></i> <?=$totalSearch ?> Procedure Requests</div>
            <table class="table table-striped table-hover no-footer">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>EMR ID</th>
                    <th>Age</th>
                    <th>Procedure</th>
                    <th>Body Part</th>
                    <th>Diagnosis</th>
                    <th>Participants</th>
                </tr>
                </thead>
                <?php if(isset($procedureReport) && sizeof($procedureReport)>0){
                    foreach ($procedureReport as $k=>$report) { ?>
                        <tr>
                            <td nowrap><?= date('jS M, Y', strtotime($report->getTimeStarted())) ?></td>
                            <td nowrap><?= ($report->getPatient()==null)? '' : $report->getPatient()->getFullname() ?></td>
                            <td><?= ($report->getPatient()==null)? '' : $report->getPatient()->getId() ?></td>
                            <td nowrap><?= ($report->getPatient()==null)? '' : $report->getPatient()->getAge() ?></td>
                            <td><?= $report->getProcedure()->getName() ?></td>
                            <td><?= $report->getBodyPart() !== NULL ? $report->getBodyPart()->getName() : '----' ?></td>
                            <td><?php $conditions=[]; foreach($report->getConditions() as $condition){ $conditions[]=$condition->getName(); } echo implode(', ', $conditions); ?></td>
                            <td><?php $participants=[]; foreach($report->getResources() as $resource){ $participants[]=$resource->getResource()->getShortname(); } echo implode(', ', $participants); ?></td>
                        </tr>
                    <?php }
                } ?>
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
                $.post('/api/find_procedures.php?from=<?=(isset($_REQUEST['from']))?$_REQUEST['from']:''?>&to=<?=(isset($_REQUEST['to']))?$_REQUEST['to']:''?>&category_id=<?=(isset($_REQUEST['category_id']))?$_REQUEST['category_id']:''?>', {'page':page}, function(s){
                    $('#procedure_report_container').html(s);
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
                window.open('/excel.php?dataSource=procedureStarted&filename=Procedure_Report&from=<?=(isset($_REQUEST['from']))?$_REQUEST['from']:''?>&to=<?=(isset($_REQUEST['to']))?$_REQUEST['to']:''?>&category_id=<?=(isset($_REQUEST['category_id']))?$_REQUEST['category_id']:''?>', '_blank');
                e.handled = true;
                e.preventDefault();
            }
        });
    });
</script>