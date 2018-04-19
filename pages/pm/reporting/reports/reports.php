<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/NurseReportDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
$scheme_id = (isset($_REQUEST['scheme']) ? $_REQUEST['scheme'] : NULL);

$scheme = (!is_null($scheme_id) ? (new InsuranceSchemeDAO())->get($scheme_id, TRUE) : NULL);

$visits = (new NurseReportDAO())->getVisits($_REQUEST['from'], $_REQUEST['to'], $scheme_id);
$enrollments = (new NurseReportDAO())->getEnrollments($_REQUEST['from'], $_REQUEST['to'], $scheme_id);

$schemes = (new InsuranceSchemeDAO())->getInsuranceSchemes();
?>
<style type="text/css">
.filter .btn {float: right;margin-top: 24px;}
.filter .span1 {margin-left: 0;}
</style>
<div class="mini-tab"><a href="?view=visits" class="tab<?=(isset($_GET['view']) && $_GET['view']=='visits'?' on':'') ?>">Visits</a>
    <a href="?view=enrollments" class="tab<?=(isset($_GET['view']) && $_GET['view']=='enrollments'?' on':'') ?>">Enrollments</a></div>

<?php if (isset($_GET['view'])) { ?>
    <div class="document">
        <form id="filterForm" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>">
            <div class="row-fluid filter">
                <label class="span7">Scheme:<select name="scheme" id="scheme" data-placeholder="--- select scheme ---">
                        <option></option>
                        <?php foreach($schemes as $is){
                            if(!empty($_REQUEST['scheme']) && $is->getId()===$_REQUEST['scheme']){
                                $schemeName=$is->getName();
                            }
                            echo '<option value="' . $is->getId() . '" ' . (!empty($_REQUEST['scheme']) && $is->getId() === $_REQUEST['scheme'] ? ' selected' : '') . '>' . $is->getName() . '</option>';
                        }?>
                    </select></label>
                <label class="span2">From:<input type="text" value="<?=(isset($_REQUEST['from'])?$_REQUEST['from']:'') ?>" name="from" id="from" placeholder="From:"></label>
                <label class="span2">To:<input type="text" value="<?=(isset($_REQUEST['to'])?$_REQUEST['to']:'') ?>" name="to" id="to" placeholder="To:" disabled="disabled"></label>

                <!--<button class="btn span1" id="exportIT">Export</button>-->
                <button type="submit" class="btn span1">Show</button>
            </div>
        </form>
        <div class="printable">
            <h4><?= ($_GET['view'] == 'visits')?'Visits':''?><?= ($_GET['view'] == 'enrollments')?'Enrollments':''?> Report<?php if(isset($_REQUEST['from']) && isset($_REQUEST['to'])){ ?> for period [<?=(isset($_REQUEST['from'])?date("Y M d", strtotime($_REQUEST['from'])):'') ?> - <?=(isset($_REQUEST['to'])? (($_REQUEST['to']=='')?date("Y M d"):date("Y M d", strtotime($_REQUEST['to']))) :date("Y M d")) ?>]<?php } ?></h4>
            <?php if ($_GET['view'] == 'visits') { ?>
                <table class="table" id="visits_table">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Number of visits</th>
                        <th>Insurance Program</th>
                        <th>*</th>
                    </tr>
                    </thead>
                    <?php if (sizeof($visits) > 0) {
                        foreach ($visits as $d) {
                            ?>
                            <tr>
                                <td><?= date('Y M d', strtotime($d->getDate())) ?></td>
                                <td><?= $d->getCount() ?></td>
                                <td><?= $d->getScheme()->getName() ?></td>
                                <td><a class="view" href="javscript:;" data-date="<?= $d->getDate() ?>" data-id="<?= $d->getScheme()->getId() ?>">view</a> | <a class="export" href="javscript:;" data-date="<?= $d->getDate() ?>" data-id="<?= $d->getScheme()->getId() ?>">export</a></td>
                            </tr>
                        <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="4"><span class="warning-bar">No patients were seen during this period</span></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } ?>
            <?php if ($_GET['view'] == 'enrollments') { ?>
                <table class="table" id="enroll__">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Number of enrollments</th>
                        <th>Insurance Program</th>
                        <th>*</th>
                    </tr>
                    </thead>
                    <?php if (sizeof($enrollments) > 0) {
                        foreach ($enrollments as $d) {
                            ?>
                            <tr>
                                <td><?= date('Y M d', strtotime($d->getDate())) ?></td>
                                <td><?= $d->getCount() ?></td>
                                <td><?= $d->getScheme()->getName() ?></td>
                                <td><a class="view" href="javscript:;" data-date="<?= $d->getDate() ?>" data-id="<?= $d->getScheme()->getId() ?>">view</a> | <a class="export" href="javscript:;" data-date="<?= $d->getDate() ?>" data-id="<?= $d->getScheme()->getId() ?>">export</a></td>
                            </tr>
                        <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="4"><span class="warning-bar">No patients were enrolled during this period</span></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } ?>
        </div>

    </div>
<script type="text/javascript">
$(document).ready(function () {
    $('select').select2({allowClear:true, width:'100%'});
    $('#visits_table').dataTable();
    $('#enroll__').dataTable();
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

    $(document).on('click', '.export', function(e){
        if(!e.handled) {
            window.open('/pages/reports/export_reports.php?ex_=xsl&type=<?= ((isset($_GET['view']))? $_GET['view']:'') ?>&scheme=' + $(this).data('id') + '&date='+ $(this).data('date'),'_blank');
            e.handled = true;
            e.preventDefault();
        }
    });

    $(document).on('click', '.view', function(e){
        if(!e.handled){
            Boxy.load('/pages/reports/view_report.php?type=<?= ((isset($_GET['view']))? $_GET['view']:'') ?>&scheme=' + $(this).data('id') + '&date='+ $(this).data('date'), {title:'View <?= ((isset($_GET['view']))? ucfirst($_GET['view']):'') ?>'});
            e.handled=true;
            e.preventDefault();
        }
    });
});
</script>
<?php } ?>
