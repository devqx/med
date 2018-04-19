<?php require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Vaccine.php';?>
<div>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientVaccineDAO.php';
    $vaccine = (new PatientVaccineDAO())->getDirectUpdateVaccines($_GET['id']);

    if(count($vaccine) > 0){?>
    <form method="post" action="/immunization/ajax.take_vaccine.php"
          onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finished})">
        <div class="loader"></div>
        <?php
        $pid = $_GET['id'];
        $vaccineUpdateTypes = Vaccine::$vaccineUpdateTypes;
        ?>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Vaccine</th>
                <th>Stage</th>
                <th>Date</th>
                <th nowrap>In/Out <a href="javascript:;" title="If vaccine was administered in this hospital or elsewhere"><img
                            src="/assets/alert/notice.png"/></a></th>
                <th>Route</th>
                <th>Site</th>
                <th>Dosage</th>
            </tr>
            </thead>
            <?php
            $routes = Vaccine::$routes;
            $count_active_vaccines = 0;
            foreach ($vaccine as $i=>$pvId) {
                $pv = $pvId;//(new PatientVaccineDAO())->getPatientVaccine($pvId, TRUE);
                if($pv->getVaccine()->getActive()){
                    $count_active_vaccines++; ?>
                <tr><td nowrap>
                        <label><input type="checkbox" value="<?=$pvId->getId()?>" name="pv_id[]">
                        <?=$pv->getVaccine()->getName()?></label></td>
                    <td><?=$pv->getVaccineLevel()?></td>
                    <td><div class="input-append">
                            <input type="datetime" readonly="readonly" required="required" name="date[]" value="<?=date("Y-m-d", time())?>">
                            <button class="image_button btn btn-default" type="button"><span class="icon-calendar"></span></button>
                        </div></td>
                    <td><input type="checkbox" checked="checked" name="place[<?=$i?>]" value="<?=$pv->getId()?>"></td>
                    <td><select name="route[]" placeholder="select site ..."><option></option><?php foreach ($routes as $key=>$value) {
                                echo '<option value="'.$key.'">'.$value.'</option>';
                            }?></select></td>
                    <td><input type="text" name="site[]"> </td>
                    <td><input name="dosage[]" class="amount volume" type="number" step="0.01" min="0.01" placeholder="example: 0.5"></td>
                </tr>

            <?php } } ?></table><input type="hidden" name="take_type" value="n">
        <div class="console"></div>
        <input type="hidden" name="take_type" value="n">
        <div class="btn-block">
            <?php if ($count_active_vaccines > 0) { ?><button class="btn" type="submit">Update Records</button><?php } ?>
            <button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
    <?php } else {?>
        <div class="notify-bar">Nothing to show <a href="javascript:;" class="pull-right" onclick="Boxy.get(this).hideAndUnload()">Close</a> </div>
    <?php }?>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('input[type="datetime"]').datetimepicker({
            format: 'Y-m-d',
            formatDate: 'Y-m-d',
            closeOnDateSelect: true,
            maxDate: 0,
            timepicker: false
        });
        $('.image_button').on('click', function () {
            $(this).prev('input[type="datetime"]').datetimepicker('show');
        });
        $('#checkAll').on('click', function(){
            if($(this).html()=="select all"){
                $('input[name="vacc_ids[]"]').each(function(){
                    $(this).prop('checked', true).iCheck('update');
                });
                $(this).html("clear all");
            }else {
                $('input[name="vacc_ids[]"]').each(function(){
                    $(this).prop('checked', false).iCheck('update');
                });
                $(this).html("select all");
            }

        });
    });

    function start(){
        $(".boxy-content").animate({ scrollTop: 0 }, "slow");
        $('div.console').html('<img src="/img/loading.gif"> Please wait ');
    }
    function finished(s){
        $('div.console').html('');

        try {
            var data = JSON.parse(s);
            if(data.status=="success"){
                Boxy.info(data.message, function () {
                    Boxy.get($('.close')).hideAndUnload();
                    showTabs(1);
                });

            }else{
                var message;
                try {message = data.message;}catch(err){message="An error has occurred"}
                Boxy.alert(message);
            }
        }catch(err){
            Boxy.alert("unexpected data encountered")
        }
    }
</script>