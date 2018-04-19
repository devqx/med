<?php
/**
 * Created by PhpStorm.
 * User: oluwaseunpaul
 * Date: 4/4/18
 * Time: 10:54 AM
 */



?>


<section style="width: 500px;">
    <form method="post" id="early_warning_form" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: saveEarlyWarning})">

        <label>
            Respiration Rate
            <input type="number" required min="0" max="25" name="respiration_rate"/>
        </label>

        <label>
            Oxygen Saturations
            <input type="number" required min="0" max="25" name="oxygen_saturations"/>
        </label>

        <label>
            Any Supplemental Oxygen ?
            <select name="supplemental_oxygen" required >
                <option>...</option>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </label>

        <label>
            Temperature
            <input type="number" required min="0" max="25" name="temperature"/>
        </label>

        <label>
            Systolic BP
            <input type="text" pattern="^[0-9]{1,3}/[0-9]{1,3}$" required placeholder=" Systolic / Diastolic"  name="systolic_bp"/>
        </label>

        <label>
            Heart Rate
            <input type="number" required min="0" max="25" name="heart_rate"/>
        </label>


        <label>
            Level Of Consciousness

            <select name="loc" required >
                <option>...</option>
                <option value="0">A</option>
                <option value="3">V</option>
                <option value="3">P</option>
                <option value="3">U</option>
            </select>

        </label>

        <input type="hidden" name="patient_id" value="<?php   echo $_REQUEST['pid'] ;?>" />
        <input type="hidden" name="admission_id" value="<?php echo $_REQUEST['aid'];?>" />
        <input type="hidden" name="taken_by_id" value="<?php  echo $_REQUEST['pid'] ;?>" />



        <p class="clear"></p>
        <div class="btn-block">
            <button class="btn" type="submit">Save</button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>

    </form>
</section>


<script>

function saveEarlyWarning(s) {

    //serialize the data
    var data = $("#early_warning_form").serialize();

    //send an ajax request
    $.ajax('/admissions/ajax.early_warning.php', {
        data: data,
        type: 'POST',
        success: function(data){
            console.log(data);
            if(data == "Early Warning Sign saved successfully"){
                Boxy.get($(".close")).hideAndUnload();
            }
        },
        error: function(err){
            console.log( err);
        }
    })


}


</script>


