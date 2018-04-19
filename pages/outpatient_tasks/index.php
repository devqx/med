<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/17/15
 * Time: 3:12 PM
 */
?>
<div class="mini-tab">
    <a class="tab on" id="rounding" onclick="loadTab(1)" href="javascript:;" title="Roundings">Clinical Tasks</a>
</div>

<div id="contentPane_">
    Loading ...
</div>

<script>
    $(document).ready(function () {
       loadTab(1);
    });
    var loadTab=function(i){
        var urn;
        $('.tab.on').removeClass('on');
        if (i == 1) {
            urn = "/outpatient_tasks/rounds.php?outpatient=true";
            $('#rounding').addClass('on');
        }
        $("#contentPane_").load(urn, function (response, status, xhr) {
            if(status==="error"){
                $("#contentPane_").html("ERROR: ("+xhr.status + ") " + xhr.statusText);
            }
            $('#_container table').dataTable();
        });
    }
</script>