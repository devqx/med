<form name="findPatientFormImmu" action="" method="post" onSubmit="start()"><!-- return AIM.submit(this, {'onStart' : start, 'onComplete' : finished})"-->
    <h2>Patient LookUp</h2>

    <div class="input-append">
        <input style="width: 90%" autofocus="on" autocomplete="off" type="text" name="searchfield" id="searchfield" placeholder="Patient EMR ID">
        <button type="submit" class="btn remainder">Search</button>
    </div>
</form>

<form method="post" id="patient_to_be_enrolled_form" action=""
      onsubmit="return AIM.submit(this, {'onStart' : null, 'onComplete' : finished1})">
<input type="hidden" name="type" value="immunization">
<h2>Enroll Patient For Immunization</h2>
<label>
    <div class="input-append">
        <input style="width: 90%" autocomplete="off" type="text" name="searchfield1" id="searchfield1" placeholder="Patient EMR ID to enroll">
        <button type="button" class="btn remainder" onclick="finished1()">Search</button>
    </div>
</label>

<div id="container1"></div>
<div class="clearfix"></div>
<div class="btn-block">
    <button type="button" class="btn btn-primary" onclick="enroll()">Enroll Selected Patients</button>
</div>
</form>
