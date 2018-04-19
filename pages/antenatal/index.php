<form method="post" action="/boxy.patients-searchresults.php" onSubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finished})">
    <h2>Antenatal Patient LookUp</h2>
    <div class="input-append">
        <input style="width: 90%" type="text" name="searchfield" id="searchfield" placeholder="Patient EMR ID/ANC #">
        <button type="submit" class="btn remainder">Search &raquo;</button>
    </div>
</form>

<form method="post" id="patient_to_be_enrolled_form" action=""
      onsubmit="return AIM.submit(this, {'onStart' : null, 'onComplete' : finished1})">
<input type="hidden" name="type" value="antenatal">
<h2>Enroll Patient For Antenatal</h2>
<!--<label>-->
    <div class="input-append">
        <input style="width: 90%" type="text" name="searchfield1" id="searchfield1" placeholder="Patient EMR ID to enroll">
        <button type="button" class="btn remainder" onclick="finished1()">Search &raquo;</button>
    </div>
<!--</label>-->

<div id="container1"></div>
</form>