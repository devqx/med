<?php
if(!isset($_SESSION))@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] .'/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID']);
if(!$this_user->hasRole($protect->dentistry))
    exit ($protect->ACCESS_DENIED);?>
<div id="scanHomeMenuLinks" class="mini-tab">
    <a class="tab" href="javascript:;" data-href="to_fulfil.php">Open Requests</a>
    <a class="tab approve" href="javascript:;" data-href="to_approve.php">Approval List</a>
    <a class="tab on" href="javascript:;" data-href="search.php"> Search Procedures</a>
    <a class="tab pull-right" id="newScanLink" href="javascript:;" data-title="New dentistry Request" data-href="/dentistry/boxy.new_request.php">New Service Request</a>
</div>
<div class="inner dataTables_wrapper" id="contentPane_" style="margin-top: 5px"></div>
<script type="text/javascript">
    $('tr[id*="_sc_an_tr_"] a.boxy').live('click', function(e){
        id = $(this).data("id");
        if(!e.handled){
            Boxy.load($(this).data("href"), {title: $(this).data("title"),afterHide: function () {
                if(typeof id !== "undefined")
                    Boxy.load("/dentistry/request.details.php?id="+id);

            }});
            e.handled = true;
            e.preventDefault();
        }
    });
</script>