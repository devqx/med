<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/18/15
 * Time: 2:47 PM
 */
//if both patients are same, error
require_once $_SERVER['DOCUMENT_ROOT'] .'/functions/utils.php';

if($_POST){
    if(is_blank($_POST['from'])){exit("error:Input source not found");}
    if(is_blank($_POST['to'])){exit("error:Output destination not defined");}
    if($_POST['from']===$_POST['to']){exit("error:Source and destination should not be same");}
    require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';
    $pDAO = new PatientDemographDAO();
    $output = $pDAO->mergePatients( $pDAO->getPatient($_POST['from']), $pDAO->getPatient($_POST['to']) );
    exit($output);
}
?>


<p class="clear"></p>
<form id="mergeForm_" method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return f1()">
    <label>Merge From:
        <span class="pull-right fadedText">After merge, this account will not be accessible anymore</span>
        <input type="text" name="from" required="required"> </label>
    <label>Into: <input type="text" name="to" required="required"></label>

    <div class="btn-block">
        <button type="button" onclick="f1()" class="btn">Merge Records</button>
        <button type="button" class="btn-link">Cancel</button>
    </div>
</form>
<script>
    $('input[name="from"], input[name="to"]').select2({
        placeholder: "Search and select patient",
        minimumInputLength: 3,
        width: '100%',
        allowClear: true,
        ajax: {
            url: "/api/search_patients.php",
            dataType: 'json',
            data: function (term, page) {
                return {
                    q: term
                };
            },
            results: function (data, page) {
                return {results: data};
            }
        },
        formatResult: function (data) {
	        var details = [];
	        details.push(data.patientId ? "EMR ID:"+data.patientId : null);
	        details.push(data.fname ? data.fname : null);
	        details.push(data.mname ? data.mname : null);
	        details.push(data.lname ? data.lname : null);
	        return implode(" ", details);
            //return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
        },
        formatSelection: function (data) {
	        var details = [];
	        details.push(data.patientId ? "EMR ID:"+data.patientId : null);
	        details.push(data.fname ? data.fname : null);
	        details.push(data.mname ? data.mname : null);
	        details.push(data.lname ? data.lname : null);
	        return implode(" ", details);
            //return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
        },
        id: function (data) {
            return data.patientId;
        }
    });

    var f0 = function(){

    };
    var f1 = function () {
        form = $("#mergeForm_");
        options = ["Yes, so sure", "Let me rethink"];
        Boxy.ask("Are you sure you want to do this? You cannot undo this action", options, function(choice){
            if(choice === options[0]){
                $.post(form.attr("action"), form.serialize()).done(function (data) {
                    data = data.split(":");
                    if(data[0]==="error"){
                        Boxy.alert(data[1]);
                    } else if(data[0]==="success"){
                        $('input[name="from"], input[name="to"]').select2('data', null);
                        Boxy.info(data[1]);
                    }
                });
            }
        }, {title:"Merge Patient accounts"});
    };
</script>
