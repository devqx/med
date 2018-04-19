<?php
//error_reporting(0);
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.main.php';
include_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';

if (isset($_POST['mname'])) {
	//anyone of the fields on the create patient page will work
	$ret = validatePatient($_POST);
	if (sizeof($ret) > 1 && $ret[0] == "success") {
		$fakeId = !is_blank($_POST['fake_patient']) ? $_POST['fake_patient'] : null;
		$pat = (new PatientDemographDAO())->addPatient($ret[1], $fakeId);

		if ($pat == null) {
			exit("error:Unable to create patient");
		} else {
			exit("success:" . $pat->getId());
		}

	} else {
		exit($ret);
	}

}
$extra_link = array("title"=>"Patients","link"=>"/patient_find-or-create.php");
$script_block = <<< EOF
var states;
\$(document).ready(function(){
\$('select').select2({allowClear: true, width:'100%'});
        
\$('#dob').datetimepicker({
    format:'d/m/Y',
    formatDate:'d/m/Y',
    timepicker:false,
    onShow:function( ct ){
        this.setOptions({  maxDate: new Date().toISOString().split('T')[0] });
    }
});
\$('#insuranceExpiration').datetimepicker({
    format:'d/m/Y',
    formatDate:'d/m/Y',
    timepicker:false,
    onShow:function( ct ){
        this.setOptions({  minDate: new Date().toISOString().split('T')[0] });
    }
});

\$('#insuranceClass').change(function(){
    resetInsurance(\$('#insuranceClass'));
    $('[name="coverage_type"]').change();
});
\$("#state").change(function(){
    html="<option value='0'>-- select LGA --</option>";
    if(\$("#state").val() != 0){
        lgas=states[\$("#state").val()-1].lgas
        for(var i=0; i< lgas.length; i++){
            html=html+"<option value='"+lgas[i].id+"'"+ ((lga==lgas[i].id)? " selected":"") +">"+lgas[i].name+"</option>";
        }
    }else{
        html="<option value='0'>-- select state of origin --</option>";
    }
   \$("#lga").html(html);
});

\$("#state_r").change(function(){
    html="<option value='0'>-- select LGA --</option>";
    if(\$("#state_r").val() != 0){
        lgas=states[\$("#state_r").val()-1].lgas
        for(var i=0; i< lgas.length; i++){
            html=html+"<option value='"+lgas[i].id+"'"+ ((lga==lgas[i].id)? " selected":"") +">"+lgas[i].name+"</option>";
        }
    }else{
        html="<option value='0'>-- select state of residence --</option>";
    }
   \$("#lga_r").html(html);
});
       
\$("#h_state").change(function(){
    html="<option value='0'>-- select LGA --</option>";
    html2="<option value='0'>-- select LGA first --</option>";
    if(\$("#h_state").val() != 0){
        lgas=states[\$("#h_state").val()-1].lgas
        for(var i=0; i< lgas.length; i++){
            html=html+"<option value='"+lgas[i].id+"'"+ ((h_lga==lgas[i].id)? " selected":"") +">"+lgas[i].name+"</option>";
        }
    }else{
        html="<option value='0'>-- select state first --</option>";
    }
   \$("#h_lga").html(html);
   \$("#hospital_").html(html2);
});
       
  \$("#h_lga").change(function(){
        html="<option value='0'>-- select health care center --</option>";
        if(\$("#h_lga").val() != 0 && \$("#h_state").val() != 0){
            for(var i=0; i< clinics.length; i++){
                if(clinics[i].lga.id == \$("#h_lga").val()){
                    html=html+"<option value='"+clinics[i].id+"'"+ ((hid==clinics[i].id)? " selected":"") +">"+clinics[i].name+"</option>";
                }
            }
        }else{
            html="<option value='0'>-- select LGA first --</option>";
        }
       \$("#hospital_").html(html);
   });
    resetInsurance($('#insuranceClass'));
});
function resetInsurance(e){
  var opt = $(e).find(":selected").data("type");
  if(opt=="self"){
    \$('*[data-class="ins"]').hide('fast');
  } else if(opt=="insurance") {
    \$('*[data-class="ins"]').show('fast');
  } else {
    \$('*[data-class="ins"]').hide('fast');
  }
  $('[name="coverage_type"]').trigger('change');
  $('#insuranceExpiration').val("");
}
function start(){
  \$.blockUI({ message: '<h6 class="fadedText" style="font-size:200%">Creating Patient Record...</h6>',
     css: {
        borderWidth: '0',
        backgroundColor:'transparent'
    }
  });
  \$('span#output').html('<img src="/img/loading.gif" /> Please wait...');
}
function finished(s){
  \$.unblockUI();
	if(s.indexOf("success")!=-1){
		s0=s.split(":");
		if(s0[0]=="success"){
			\$('span#output').html('');
			str1 = 'Created patient EMR ID: <strong><a href="/patient_profile.php?id='+s0[1]+'">'+s0[1]+'</a></strong>';
			\$('#createForm').html('Created patient EMR ID: <strong><a href="/patient_profile.php?id='+s0[1]+'">'+s0[1]+'</a></strong>');
			Boxy.ask("Do you want to start a new appointment for this patient? <br/>"+str1, ["Yes", "No"], function(val){
        if(val=="Yes"){
          Boxy.load('/appointments/boxy_createAppointment.php?pid='+s0[1],{title: 'Schedule an appointment', unloadOnHide:false});
          \$('.close').click();
          
          //location.href="/patient_profile.php?id="+s0[1];
          //Boxy.load('boxy.startnewvisit.php?id='+s0[1],{title: 'Start New Visit'});\$('.close').click();
        }else{
          location.href="/patient_profile.php?id="+s0[1];
        }
			}, {title: "Schedule New appointment..."});
        return false;
	    }
	}else {
    s1=s.split(":");
    if(s1[0]=="error"){
      Boxy.alert('ERROR: '+s1[1]);
        \$('span#output').html('<div class="warning-bar">'+s1[1]+'</div>');
      }
    }
  }
EOF;

$page = "pages/create-patient.php";
include ("template.inc.in.php");
