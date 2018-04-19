<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/14/14
 * Time: 3:38 PM
 */
//show scans that don't have attachments
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDentistryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';

$Requests = array();
if (isset($_GET['date']) && !is_blank($_GET['date'])){
    $date = explode(",", $_GET['date']);
    $start = $date[0];
    $stop  = $date[1];
}else {
    $start = null;
    $stop  = null;
}
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$patient = (!is_blank(@$_REQUEST['patient_id'])) ? $_REQUEST['patient_id'] : NULL;
$pageSize = 10;
$temp = (new PatientDentistryDAO())->getServices($start, $stop, $page, $pageSize, 'open', $patient);
$totalSearch = $temp->total;
foreach ($temp->data as $scan) {
    $Requests[] = $scan;
}
?><div class="row-fluid ui-bar-c">
    <div class="span6">
        Filter by date:
        <div class="input-prepend">
            <span class="add-on">From</span>
            <input class="span2" type="text" name="date_start" value="<?=isset($start)?$start:''?>"  placeholder="Start Date">
            <span class="add-on">To</span>
            <input class="span2" type="text" name="date_stop" value="<?=isset($stop)?$stop:''?>" placeholder="Stop Date">
            <button class="btn" type="button" id="date_filter">Apply</button>
        </div>
    </div>
    <div class="span6">
    Filter Open Requests by Patient
    <input type="hidden" name="patient_id" value="<?= (isset($_REQUEST['patient_id']) ? $_REQUEST['patient_id'] : '') ?>">
    </div>
</div>
<?php include_once 'template.php';?>
    <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page+1?> of <?= ceil($totalSearch / $pageSize)?>)</div>
    <div class="resultsPager no-footer dataTables_paginate">
        <div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
            <a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>

            <a id="DataTables_Table_1_last" class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_next" class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
        </div>
    </div>
    <!-- yes i know: the opening div is in template.php -->
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('input[name="date_start"]').datetimepicker({format:'Y-m-d', timepicker: false});
        $('input[name="date_stop"]').datetimepicker({format:'Y-m-d', timepicker: false});
        $('[name="patient_id"]').css({'font-weight':400}).select2({
            placeholder: "Search and select patient",
            minimumInputLength: 5,
            width: '100%',
            allowClear: true,
            ajax: {
                url: "/api/search_patients.php",
                dataType: 'json',
                data: function (term, page) {
                    return {
                        q: term
                    };
                },
                results: function (data, page) {
                    return {results: data};
                }
            },
            formatResult: function (data) {
	            var details = [];
	            details.push(data.patientId ? "EMR ID:"+data.patientId : null);
	            details.push(data.fname ? data.fname : null);
	            details.push(data.mname ? data.mname : null);
	            details.push(data.lname ? data.lname : null);
	            return implode(" ", details);
                //return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
            },
            formatSelection: function (data) {
	            var details = [];
	            details.push(data.patientId ? "EMR ID:"+data.patientId : null);
	            details.push(data.fname ? data.fname : null);
	            details.push(data.mname ? data.mname : null);
	            details.push(data.lname ? data.lname : null);
	            return implode(" ", details);
                //return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
            },
            id: function (data) {
                return data.patientId;
            },
            initSelection: function(element, callback) {
                var id = $(element).val();
                if (id !== "") {
                    $.ajax("/api/search_patients.php?pid=" + id, {
                        dataType: "json"
                    }).done(function(data) { callback(data); });
                }
            }
        }).change(function(e) {
          if(!e.handled){
             var url = "/dentistry/to_fulfil.php?patient_id="+$(this).val()+"&date="+encodeURIComponent($('input[name="date_start"]').val())+","+encodeURIComponent($('input[name="date_stop"]').val());
                $('.container > .inner').load(url, function (responseText, textStatus, req) { });
                e.handled = true;
          }
        });

        $('#date_filter').live('click', function (e) {
            if(!e.handled){
                var url = "<?=$_SERVER['REQUEST_URI'] ?>?date="+encodeURIComponent($('input[name="date_start"]').val())+","+encodeURIComponent($('input[name="date_stop"]').val());
                $('.container > .inner').load(url, function (responseText, textStatus, req) { });
                e.handled = true;
            }
        });
        $(document).on('click', '.resultsPager.dataTables_paginate a.paginate_button', function(e){
            var page = $(this).data("page");
            var date_start = $('input[name="date_start"]').val();
            var date_stop = $('input[name="date_stop"]').val();
            if(!$(this).hasClass("disabled") && !e.handled){
                var url = "/dentistry/to_fulfil.php?date="+encodeURIComponent(date_start)+","+encodeURIComponent(date_stop)+"&page="+page;
                $('.container > .inner').load(url, function (responseText, textStatus, req) {});
                e.handled = true;
            }
        });
    })
</script>
<?php exit;