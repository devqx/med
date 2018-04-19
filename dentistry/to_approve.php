<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/14/14
 * Time: 3:39 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDentistryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;
$patient = (!is_blank(@$_REQUEST['patient_id'])) ? @$_REQUEST['patient_id'] : NULL;
$temp = (new PatientDentistryDAO())->getServices(NULL, NULL, $page, $pageSize, 'approval', $patient);
$totalSearch = $temp->total;
$data = array();

foreach ($temp->data as $scan) {
    $data[] = $scan;
}?>

<div class="row-fluid ui-bar-c">
    <div class="span6 offset6">
        Filter Approval List by Patient
        <input type="hidden" name="patient_id" value="<?= isset($_REQUEST['patient_id']) ? $_REQUEST['patient_id'] : '' ?>">
    </div>
</div>
<div class="clear"></div>
<div class="dataTables_wrapper">
    <table class="table table-striped scantable_">
        <thead><tr><th>Request Date</th><th>RQ #</th><?php if(!isset($_GET['pid'])){?><th>Patient</th><?php }?><th>Requester</th><th>Type</th><th>Approve</th><th>Reject</th></tr></thead>
        <?php foreach ($data as $ps) { // $ps=new PatientDentistry();?>
            <tr id="_sc_an_tr_<?= $ps->getId() ?>">
                <td><time datetime="<?= strtotime($ps->getRequestDate())?>"><?=date("d M, Y h:iA", strtotime($ps->getRequestDate()))?></time></td>
                <td><a data-title="<?= $ps->getRequestCode() .": ". $ps->getServices()[0]->getName()?>"  class="boxy" href="javascript:;" data-href="/dentistry/request.details.php?id=<?= $ps->getId() ?>"><?= $ps->getRequestCode()?></a></td>
                <td><a title="<?=$ps->getPatient()->getFullname();?>" href="/patient_profile.php?id=<?=$ps->getPatient()->getId()?>"><?=$ps->getPatient()->getShortname();?></a></td>
                <td><span ><?=$ps->getRequestedBy()->getUsername()?></span></td>
                <td><?php $dd = []; foreach ($ps->getServices() as $rq) {
                        $dd[] = $rq->getName();
                    } echo implode(", ", $dd)?></td>
                <td><i class="icon-ok"></i><a class="approve-link" href="javascript:;" data-id="<?=$ps->getId()?>"> Approve</a> </td>
                <td><i class="icon-remove"></i><a class="reject-link" href="javascript:;" data-id="<?=$ps->getId()?>"> Reject</a> </td>
            </tr>
        <?php }?>
    </table>
    <div class="dataTables_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page+1?> of <?= ceil($totalSearch / $pageSize)?>)</div>
    <div class="resultsPager2 no-footer dataTables_paginate">
        <div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
            <a data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
            <a data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>
            <a class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
            <a class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
        </div>
    </div>
</div>


<script>
    $(document).ready(function(){
        $(document).on('click', '.resultsPager2.dataTables_paginate a.paginate_button', function(e){
            var page = $(this).data("page");
            if(!$(this).hasClass("disabled") && !e.handled){
                var url = "/dentistry/to_approve.php?page="+page;
                $('.container > .inner').load(url, function (responseText, textStatus, req) {
                });
                e.handled = true;
            }
        });
        $('a.approve-link').live('click', function (e) {
            var $id = $(this).data("id");
            if(!e.handled){
                vex.dialog.confirm({
                    message: "Are you sure to approve?",
                    callback: function (evt) {
                        if(evt){
                            $.ajax({
                                url:"ajax.approve_.php",
                                data: {full:true, id:$id},
                                type:"post",
                                beforeSend:function(){},
                                success: function (s) {
                                    setTimeout(function(){$('#scanHomeMenuLinks a.approve').click();},10);
                                }, error:function(){
                                    Boxy.alert("Approval failed");
                                }
                            });
                        }
                    }
                });
                e.handled = true;
            }
        });
        $('a.reject-link').live('click', function (e) {
            var $id = $(this).data("id");
            if(!e.handled){
                $.ajax({
                    url:"ajax.reject.php",
                    data: {id:$id},
                    type:"post",
                    beforeSend:function(){},
                    success: function (s) {
                        Boxy.info("Dentistry request rejected");
                        setTimeout(function(){$('#scanHomeMenuLinks a.approve').click();},10);
                    }, error:function(){
                        Boxy.alert("Reject failed");
                    }
                });
                e.handled = true;
            }
        });

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
                var url = "/dentistry/to_approve.php?patient_id="+$(this).val();
                $('.container > .inner').load(url, function (responseText, textStatus, req) { });
                e.handled = true;
            }
        });
    });
</script>
