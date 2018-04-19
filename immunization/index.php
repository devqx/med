<?php include '../protect.php';
$script_block = <<<EOF
\$(document).ready(function(){
    $('form[name="findPatientFormImmu"]').submit(function(e){
        Boxy.load("/boxy.patients-searchresults.php?id="+\$('#searchfield').val()+"&type=immunization",
        {
            title: "Search for Immunization Patients",
            afterShow:function(){
                //$('#resultsTable').tableScroll({height:600});
                //$('#numCount').html($("#resultsTable tr").length + ' records found');
            }
        });
        e.preventDefault();
        return false;
    });
});
    //\$('input[name="patient_to_be_enrolled[]"]').click(function(){
    //    console.log("patients to be enrolled: length: "+\$(this).length);
    //});
    function selectAll(x){
        if($(x).is(":checked")){
            \$('input[name="patient_to_be_enrolled[]"]').each(function(){\$(this).prop('checked',true).iCheck('update');});
        }else{
            \$('input[name="patient_to_be_enrolled[]"]').each(function(){\$(this).prop('checked',false).iCheck('update');});
        }
    }
    function start(){ \$('#container1 div').remove();}
    function finished(s){}
    function start1(){
        \$('#container1').html('<img src="/img/loading.gif"/>');
    }
    function finished1(){
        $("#content_loader").show();
        \$('#container1').load("ajax.find_patients_for_immunization_enrollment.php?id="+\$('#searchfield1').val(),function(){
            $("#content_loader").hide();
        });
    }
    function enroll(){
        \$("#content_loader").show();
        \$.ajax({
            url: 'ajax.enroll_patients.php',
            type: 'POST',
            data: \$('#patient_to_be_enrolled_form').serialize(),
            success:function(s){
                finished1();
                if(s=='ok'){Boxy.info("Patient(s) enrolled for immunization successfully");}
                else{Boxy.alert("Patient(s) enrollment for immunization failed");}
            }
        });
    }
EOF;
$page = "pages/vaccine/index.php";
include "../template.inc.in.php";
