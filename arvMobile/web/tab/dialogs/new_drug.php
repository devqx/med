<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/1/16
 * Time: 3:01 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ArvDrugDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$types = getTypeOptions('type', 'arv_drug_data');
$drugs = json_encode((new ArvDrugDAO())->all());
?>
<section style="width: 700px;">
<form method="post" id="__arv_drugs">
    <label>Line <select required name="type"><?php foreach($types as $type){?><option value="<?= $type?>"><?= $type?></option><?php }?></select></label>
    <label>Drug <input type="hidden" required name="arv_drug_id"></label>
    <label>Dose <input type="text" name="dose" required></label>
    <div class="btn-block">
        <button class="btn" type="button" onclick="addArvDrug()">Add Drug</button>
        <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
    </div>
</form>
</section>
<script>
    var drugs = <?=$drugs?>;
    setTimeout( function(){
        $('#__arv_drugs input[name="arv_drug_id"]').select2({
            data: function() {
                return {results: drugs, text: 'name'};
            },
            width: '100%',
            placeholder: '--- Select Drug ---',
            formatResult: function(source){return source.name; },
            formatSelection: function(source){return source.name; }
        });
    }, 1);

    function addArvDrug() {
        var _arvDrugs = $('#arv_drug_data').val();
        if(_arvDrugs == ""){
            _arvDrugs = [];
        } else {
            _arvDrugs = $.parseJSON(_arvDrugs);
        }

        var dose = $('#__arv_drugs [name="dose"]').val();
        var type = $('#__arv_drugs [name="type"]').val();
        var drug = $('#__arv_drugs [name="arv_drug_id"]').select2("data");

        if(dose && type && drug){
            var newDrug = {};
            newDrug.dose = dose;
            newDrug.type = type;
            newDrug.drug = drug;

            _arvDrugs.push(newDrug);
            $("#arv_drug_data").val(JSON.stringify(_arvDrugs));
            Boxy.get($(".close")).hideAndUnload();
        }

    }
</script>
