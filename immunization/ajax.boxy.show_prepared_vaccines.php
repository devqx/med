<script type="text/javascript">
    function start() {
        $(".loader").html('<img src="/img/loading.gif"> registering the vaccines...please wait...');
    }
    function finished(s) {
        console.info(s);
        var data = JSON.parse(s);
        var loader = $(".loader");
        loader.html('');
        if(data.status == "success"){
            Boxy.info(data.message, function(){
                showTabs(1);
                Boxy.get($('.close')).hideAndUnload();
            });
        }else if(data.status == "error") {
            Boxy.alert(data.message);
        }
    }
</script>
<div>
    <form method="post" action="/immunization/ajax.take_vaccine.php"
          onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finished})">
        <div class="loader"></div>
        <?php
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Vaccine.php';
        $vaccineUpdateTypes = Vaccine::$vaccineUpdateTypes;
        parse_str(rawurldecode($_REQUEST['data']));
        if ($action == "take_vaccine") {
        if (count($vaccine) > 0){
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientVaccineDAO.php';?>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>Vaccine</th>
                <th>Stage</th>
                <th>Date</th>
                <th>In/Out <a href="javascript:;" title="If vaccine was administered in this hospital or elsewhere"><img
                            src="/img/icons/ask.png" style="height: 16px"/></a></th>
                <th>Route</th>
                <th>Site</th>
                <th>Dosage</th>
            </tr>
            </thead>
            <?php
            $routes = Vaccine::$routes;
            foreach ($vaccine as $i=>$pvId) {
                $pv = (new PatientVaccineDAO())->getPatientVaccine($pvId, TRUE);?>
                <tr><td><input style="display:none" type="checkbox" value="<?=$pvId?>" name="pv_id[]" checked="checked" ><?=$pv->getVaccine()->getName()?></td><td><?=$pv->getVaccineLevel()?></td><td><div class="input-append">
                      <input type="datetime" readonly="readonly" required="required" name="date[]" value="<?=date("Y-m-d", time())?>">
                      <button class="image_button btn btn-default" type="button"><span class="icon-calendar"></span></button>
                      </div></td><td><input type="checkbox" checked="checked" name="place[<?=$i?>]" value="<?=$pv->getId()?>"></td>
                      <td><select name="route[]" placeholder="select site ..."><option></option><?php foreach ($routes as $key=>$value) {
                                  echo '<option value="'.$key.'">'.$value.'</option>';
                      }
                              ?></select></td><td><input type="text" name="site[]"> </td><td><input name="dosage[]" class="amount volume" type="number" step="0.01" min="0.01" placeholder="example: 0.5"></td></tr>

            <?php }?></table><input type="hidden" name="take_type" value="n"><?php
        } else {
            echo '<span class="warning-bar">Oops! You selected no vaccine. Close this dialog and try again</span>';
        }
        }?>

<!--        TODO: if this page is accessed from the online portal, it should be set to 'p'=>patient submitted, and then request-->
<!--        TODO: authorization code of some sort-->
            <div class="btn-block">
                <?php if (count($vaccine) > 0) { ?>
                <button class="btn" type="submit">Administer</button><?php } ?>
                <button type="button" class="btn-link" onclick="Boxy.get(this).hide()">Cancel</button>
            </div>


    </form>

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

    });
</script>