<?php include('protect.php');
//require_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
if ($_POST) {
	//anyone of the fields on the create patient page will work
	$ret = validatePatient($_POST);
	if (sizeof($ret) > 1 && $ret[0] == "success") {
		$pat = (new PatientDemographDAO())->updatePatient($ret[1], $_POST['my_scheme_id']);
		if ($pat === null) {
			exit("error:Unable to save changes");
		} else {
			exit("success:" . $pat->getId());
		}
	} else {
		exit($ret);
	}
}
if(isset($_GET['aid'])){
	$url = "'/admissions/inpatient_profile.php?aid={$_GET['aid']}&pid='+s0[1]";
} else {
	$url = "'/patient_profile.php?id='+s0[1]";
}
$extra_link = array("title" => "Patients", "link" => "/patient_find-or-create.php");
$script_block = <<<EOF

var states;
var states,state,lga,h_lga,clinics,hid,insSchemeId,insExpDate;
$(document).ready(function(){
$('select').select2({width:'100%', allowClear: true});
$('#dob').datetimepicker({
    format:'d/m/Y',
    formatDate:'d/m/Y',
    timepicker:false,
    onShow:function( ct ){
        this.setOptions({  maxDate: new Date().toISOString().split('T')[0] });
    }
});
\$('#insuranceExpiration_').datetimepicker({
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
                html=html+"<option value='"+lgas[i].id+"'"+ ((lga_res==lgas[i].id)? " selected":"") +">"+lgas[i].name+"</option>";
            }
        }else{
            html="<option value='0'>-- select state of origin --</option>";
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
       \$("#currentHealthCenter").html(html2);
   });
  \$("#h_lga").change(function(){
        html="<option value='0'>-- select healthcare center --</option>";
        if(\$("#h_lga").val() != 0 && \$("#h_state").val() != 0){
            for(var i=0; i< clinics.length; i++){
                if(clinics[i].lga.id == \$("#h_lga").val()){
                    html=html+"<option value='"+clinics[i].id+"'"+ ((hid==clinics[i].id)? " selected":"") +">"+clinics[i].name+"</option>";
                }
            }
        }else{
            html="<option value='0'>--select LGA first--</option>";
        }
       \$("#currentHealthCenter").html(html);
   });

placePassport();
  \$("#showSnap").click(function(){
	  \$("#camera").show('slow');
  });
    resetInsurance($('#insuranceClass'))
});
function placePassport(){
	\$("#camera_b").css({
	  'position':'fixed',
	  'z-index':10,
	  'top':'50px',
	  'left':( ((window.screen.width)- ((window.screen.width - \$("#camera_b").width())/2)+\$("#camera_b").width()-11) ) +'px'
	});
}
\$(window).resize(function(e) {
    placePassport();
});
function start(){
	\$('span#output').html('<img src="/img/loading.gif" /> Please wait...');
    /*if(\$("#insuranceType").val().toLowerCase()!=="self" && \$("#insuranceExpiration").val()=='0000-00-00'){
        \$("#insuranceExpiration").css({'border':'1px solid #c00'})
        return false;
    }*/
}
function finished(s){
  $('span#output').html('');
  if(s.indexOf("success")!=-1){
		s0=s.split(":");
		if(s0[0]=="success"){
			location.href=$url;
		}
	} else {
    s1=s.split(":");
    if(s1[0]=="error"){
      \$('span#output').html('<div class="warning-bar">'+s1[1]+'</div>');
      Boxy.alert(s1[1]);
    }
  }
}

function resetInsurance(e){
    var opt = $(e).find(":selected").data("type");
    if(opt==="self"){
        \$('*[data-class="ins"]').hide('fast');
    } else {
        \$('*[data-class="ins"]').show('fast');
    }
    $('#insuranceExpiration').val("");
}


function getLGAState(lid){
    for(var i=0; i<states.length; i++){
        for(var j=0; j<states[i].lgas.length; j++){
            lg=states[i].lgas[j];
            if(lg.id==lid){
                return states[i].id;
            }
        }
    }
    return 0;
}
EOF;
if (!isset($_SESSION)) {
	@session_start();
}
$page = "pages/edit_patient_profile.php";

include ("template.inc.in.php");
