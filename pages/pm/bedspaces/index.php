<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';?>
<div>
    <div id="tabbedPane" style="/*margin-bottom: -6px;*/" class="mini-tab">
        <a href="javascript:void(0)" class="tab on" id="tab-1" onClick="showTabs(1)"> Room Categories </a>
        <a href="javascript:void(0)" class="tab" id="tab-3" onClick="showTabs(3)"> Rooms </a>
        <a href="javascript:void(0)" class="tab" id="tab-2" onClick="showTabs(2)"> Bed Spaces </a>
        <a href="javascript:void(0)" class="tab" id="tab-5" onClick="showTabs(5)"> Blocks </a>
        <a href="javascript:void(0)" class="tab" id="tab-4" onClick="showTabs(4)"> Wards </a>
        <a href="javascript:void(0)" class="tab" id="tab-6" onclick="showTabs(6)"> Daily Admission Fees</a>
        <a href="javascript:void(0)" class="tab" id="tab-7" onclick="showTabs(7)"> Nursing Services</a>
        <a href="javascript:void(0)" class="tab" id="tab-8" onclick="showTabs(8)"> Fluid Chart Settings</a>
    </div>
    <div>
        <span id="contentPane"></span>
    </div>
    <br clear="left" />
</div>

<script type="text/javascript">

    $(document).ready(function() {
        showTabs(1);
        var ntot = $("#tabbedPane a").length;
        $("#tabbedPane a").css({'min-width': ($("#tabbedPane").width() / ntot) - 30});
    });

    function showTabs(t) {
        if (t == 1){
            activateTab(t, '/pages/pm/bedspaces/addRoomType.php');
        }else if (t == 2){
            activateTab(t, '/pages/pm/bedspaces/addBed.php');
        }else if (t == 3){
            activateTab(t, '/pages/pm/bedspaces/addRoom.php');
        }else if (t == 4){
            activateTab(t, '/pages/pm/bedspaces/addWard.php');
        }else if (t == 5){
            activateTab(t, '/pages/pm/bedspaces/addBlock.php');
        }else if (t == 6){
            activateTab(t, '/pages/pm/bedspaces/nursingFee.php');
        }else if (t == 7){
            activateTab(t, '/pages/pm/bedspaces/nursingServices.php');
        }else if (t == 8){
            activateTab(t, '/pages/pm/bedspaces/fluid-chart/settings.php');
        }
    }

    function activateTab(t, url) {
        $('.tab').removeClass('on');
        $('#tab-' + t).addClass('on');
        $("#contentPane").load(url, function () {
            setTimeout(function () {
                $('table.table.table-striped').dataTable();
                $('.boxy-content select:not([aria-controls])').select2({width: '100%', allowClear:true});
            },100);
        });
    }
    
</script>
