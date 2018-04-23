<?php
/**
 * Created by PhpStorm.
 * User: oluwaseunpaul
 * Date: 4/6/18
 * Time: 1:53 PM
 */


//get all the health state possible
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientHealthDAO.php';

$health_states = ( new InPatientHealthDAO() )->getAllHealthStates();

//in_patient id
$pid = $_GET['pid'];

//fetch the old values of the health state and the risk to fall
$phealth = ( new InPatientHealthDAO() )->getInPatientHealthState($pid);




?>



<div style="width:400px">
<!--    <p> Update In Patient Health State </p>-->
    <form class="form" action="<?php $_SERVER['PHP_SELF'];?>" method="POST" id="health_state_form">
        <div class="form-group">
            <label class="control-label"> Patient Health Status </label>
            <select name="health_state">
                <option>...</option>

                <?php foreach( $health_states as $state ){?>

                    <option value="<?= $state->getId() ;?>" <?= $state->getState()  == $phealth->getHealthStatusId() ?  "selected" : ''?>  > <?= $state->getState() ;?> </option>


                <?php }?>

            </select>
        </div>
        <p></p>
        <div class="form-group">
            <label class="control-label"> Risk To Fall </label>
            <select name="risk_to_fall">

                <option>...</option>
                <option <?php echo !empty( $phealth->getRiskToFall() ) == 1 ?  "selected" : ''?> value="1">Yes</option>
                <option <?php echo !empty( $phealth->getRiskToFall() ) == 0 ?  "selected" : ''?> value="0">No</option>

            </select>
        </div>

        <input type="hidden" name="in_pid" value="<?php echo $pid ;?>" />

        <p class="clear"></p>
        <div class="btn-block">
            <button class="btn" id="update_health_state" type="submit">Save</button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>


    </form>
</div>


<script>


    $("#update_health_state").click( function(e){

        e.preventDefault();

        var data = $("#health_state_form").serialize();

        //send ajax request

        $.ajax('/admissions/ajax.inpatient_health_edit.php', {

            data: data,
            type: 'POST',
            success: function(response){
                console.log(response);
                Boxy.get($(".close")).hideAndUnload();
                window.location.reload();

            },
            error: function(err){
                console.log(err);

            }

        });


    });


</script>


