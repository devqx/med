<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/14/16
 * Time: 12:06 PM
 */?>
<form method="post" action="/sti_clinic/boxy.patients-searchresults.php" onSubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finished})">
    <h2>Enrolled Patient LookUp</h2>
    <div class="input-append">
        <input style="width: 90%" type="text" name="searchfield" id="searchfield" placeholder="Patient EMR ID/IHVN #">
        <button type="submit" class="btn remainder">Search &raquo;</button>
    </div>
</form>

<form method="post" id="patient_to_be_enrolled_form" action=""
      onsubmit="return AIM.submit(this, {'onStart' : finished1, 'onComplete' : null})">
    <input type="hidden" name="type" value="sti">
    <h2>Enroll Patient Into STI Clinic</h2>

    <div class="input-append">
        <input style="width: 90%" type="text" name="searchfield1" id="searchfield1" placeholder="Patient EMR ID to enroll">
        <button type="button" class="btn remainder" onclick="finished1()">Search &raquo;</button>
    </div>

    <div id="container1"></div>
</form>
<script>
    $(document).ready(function(e) {
        $('.hide').hide();
    });

    function start(){
        Boxy.load("/boxy.patients-searchresults.php?id="+$('#searchfield').val()+"&type=sti",
        {
            title: "Enrolled Patients",
            afterShow:function(){
                $('#container1 div').remove();
            }
        });
    }
    function finished(s){}
    function start1(){
    $('#container1').html('<img src="/img/loading.gif"/>');
    }
    function finished1(){
    $('#container1').load("find_patients_for_sti_enrollment.php?id="+$('#searchfield1').val());
    }
</script>
