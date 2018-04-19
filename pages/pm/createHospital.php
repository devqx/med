<?php
$_GET['suppress']=0;
include_once $_SERVER['DOCUMENT_ROOT'].'/api/state_lga.php';
$states = (new StateDAO())->getStates(true);

if($_POST){
    echo "";
}
?><script type="text/javascript">
    var states=<?php echo json_encode($states) ?>;
    $(document).ready(function(){
        $("#state").change(function(){
            var html="<option value='0'>-- select LGA --</option>",lgas,lga;
            if($("#state").val() != 0){
                lgas=states[$("#state").val()-1].lgas
                for(var i=0; i< lgas.length; i++){
                    html=html+"<option value='"+lgas[i].id+"'"+ ((lga==lgas[i].id)? " selected":"") +">"+lgas[i].name+"</option>";
                }
            }else{
                html="<option value='0'>-- select state of origin --</option>";
            }
            $("#lga").html(html);
        });
        $("#creator select").select2({
            width:'100%'
        });
    });
</script>
<form action="/pages/pm/createHospital.php" method="post" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done})">
        <label for="clinic">Hospital/Clinic name
        <input name="clinic" type="text" id="clinic" value="" /></label>
        <label for="code">Code
        <input name="code" type="text" id="code" value="" /></label>
        <label class="sub">State<select name="state" id="state">
            <option value="0">-- select state of origin --</option>
            <?php
            foreach ($states as $st) {
                $selected = (isset($_POST['state']) && ($_POST['state'] == $st->getId())) ? " selected" : "";
                echo("<option value='" . $st->getId() . "'" . $selected . ">" . $st->getName() . "</option>");
            }
            ?></select></label>
        <label class="sub">Local Government Area of Origin<select name="lga" id="lga">
            <option value="0">-- select LGA --</option>
            <?php
            if(isset($_POST['lga'])){ $lgas = getLgas($states, $_POST['lga']); }
            if(isset($lgas)) {
                foreach ($lgas as $lg) {
                    $selected = (isset($_POST['lga']) && ($_POST['lga'] == $lg->getId())) ? " selected" : "";
                    echo("<option value='" . $lg->getId() . "'" . $selected . ">" . $lg->getName() . "</option>");
                }
            }
            ?></select></label>
        <label>Address
        <input name="clinic_address" id="clinic_address" type="text" value="" /></label>
        <label>Location: Longitude
        <input name="long" id="long" type="text" value="" /></label>
        <label>Location: Latitude
        <input name="lat" id="long" type="text" value="" /></label>
        <label>Class
        <select name="class">
                <option value="">Select Class</option>
                <option value="Hosp">Hospital</option>
                <option value="PHC">Primary Health Care Center</option>
        </select></label>
        <div align="left">
            <button class="btn" name="newclinic" type="submit">Create hospital</button>
            <button class="btn-link" type="button" onclick="$('#creator').attr('data', 'none').html('');$('span.error').html('');">Cancel</button>
        </div>
</form>
<script>
function start(){}
function done(s){
    console.log(s);
}
</script>