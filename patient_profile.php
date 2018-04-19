<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
$protect = new Protect();
sessionExpired();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

require_once 'classes/class.patient.php';
//require_once 'classes/class.labs.php';
require_once 'classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Encounter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SpokenLanguageDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/functions/func.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
$pdo = (new MyDBConnector())->getPDO();
$patient = new Manager();
$bills = new Bills();
$id = escape($_GET['id']);

$ip = (new InPatientDAO())->getActiveInPatient($id, TRUE);

$antenatal = is_dir($_SERVER['DOCUMENT_ROOT'] . "/antenatal") ? (new AntenatalEnrollmentDAO())->getActiveInstance($id) : null;

if (isset($_GET['view']) && $_GET['view'] == "encounters") {
	sessionExpired();
	$encounter = (new Encounter());
	if (!$encounter::$useLight) {
		include_once 'encounters.php';
	} else {
		include_once 'encounters_light.php';
	}
	exit;
}
if (isset($_GET['view']) && $_GET['view'] == "visits") {
	sessionExpired();
	include_once 'visit_notes.php';
	exit;
} else if (isset($_GET['view']) && $_GET['view'] == "vitalsigns") {
	sessionExpired();
	exit;
} else if (isset($_GET['view']) && $_GET['view'] == "rx") {
	sessionExpired();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/prescriptionDetails.php';
	exit;
} else if (isset($_GET['view']) && $_GET['view'] == "allergens") {
	sessionExpired();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/patient_allergens.php';
	exit;
} else if (isset($_GET['view']) && $_GET['view'] == "labs") {
	sessionExpired();
	//$lab=new Labs();
	//role managed in the below function
	$_GET['id'] = $id;
	include_once $_SERVER['DOCUMENT_ROOT'] . '/labs/patientLabs.php';
	exit;
}else if(isset($_GET['view']) && $_GET['view'] == "item_request") {
	sessionExpired();
	exit;
}
else if (isset($_GET['view']) && $_GET['view'] == "precon") {
	sessionExpired();

	$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
	$pageSize = 10;

	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
	$type = (isset($_REQUEST['type']) ? $_REQUEST['type'] : 'confirmed');
	$active = (isset($_REQUEST['active']) ? $_REQUEST['active'] : 'true');
	$severity = (isset($_REQUEST['severity']) ? $_REQUEST['severity'] : null); //acute should show when none was specified?
	$data = (new PatientDiagnosisDAO())->one($id, $type, $active, $severity, $page, $pageSize);
	include_once $_SERVER['DOCUMENT_ROOT'] . '/diagnoses/patient_diagnoses.php';

	exit;
} else if (isset($_GET['view']) && $_GET['view'] == "billings") {
	sessionExpired();
	exit;
} else if (isset($_GET['view']) && $_GET['view'] == "procedures") {
	sessionExpired();
	$_GET['pid'] = $id;
	include_once $_SERVER['DOCUMENT_ROOT'] . '/procedures/patient_procedures.php';
	exit;
} else if (isset($_GET['view']) && $_GET['view'] == "med") {
	sessionExpired();
	exit;
} else if (isset($_GET['view']) && $_GET['view'] == "imaging") {
	sessionExpired();
	$_GET['pid'] = $id;
	
	include_once $_SERVER['DOCUMENT_ROOT'] . '/imaging/patient_images.php';
	exit;
} else if (isset($_GET['view']) && $_GET['view'] == "visit_summary") {
	sessionExpired();
	if (!$this_user->hasRole($protect->doctor_role) && !$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->records)) {
		exit($protect->ACCESS_DENIED);
	} else {
		$_GET['pid'] = $id;
		include_once $_SERVER['DOCUMENT_ROOT'] . '/visit-summary.php';
		exit;
	}
} else if (isset($_GET['view']) && $_GET['view'] == "appointments") {
	sessionExpired();
	exit;
} else if (isset($_GET['view']) && $_GET['view'] == "dentistry") {
	sessionExpired();
	$_GET['pid'] = $id;
	include_once $_SERVER['DOCUMENT_ROOT'] . '/dentistry/patient_requests.php';
	exit;
} else if (isset($_GET['view']) && $_GET['view'] == "ophthalmology") {
	sessionExpired();
	$_GET['pid'] = $id;
	if (!isset($_GET['pane'])) {
		include_once $_SERVER['DOCUMENT_ROOT'] . '/ophthalmology/patient_ophthalmology.php';
		exit;
	} else if (isset($_GET['pane'])) {
		include_once $_SERVER['DOCUMENT_ROOT'] . '/ophthalmology/items/patient_list.php';
		exit;
	}
} else if (isset($_GET['view']) && $_GET['view'] == "physio") {
	sessionExpired();
	$_GET['pid'] = $id;
	include_once $_SERVER['DOCUMENT_ROOT'] . '/physiotherapy/patient_bookings.php';
	exit;
} else if (isset($_GET['view']) && $_GET['view'] == "medical_exam") {
	sessionExpired();
	$_GET['pid'] = $id;
	include_once $_SERVER['DOCUMENT_ROOT'] . '/medical_exam/patient_requests.php';
	exit;
}

$page = $_SERVER['DOCUMENT_ROOT'] . "/pages/patient_profile.php";

$script_block = <<<EOF
\$(function(){
    $(".container-fluid ul").droptabs({});
    $('#newProc').live('click', function(e){
        if(e.handled!==true){
            Boxy.load($(this).data("href"),{title:"New Procedure"});
            e.handled = true;
        }
    });

    $('tr[id*="_sc_an_tr_"] a.boxy').live('click', function(e){
        id = $(this).data("id");
        if(!e.handled){
            Boxy.load($(this).data("href"), {afterHide: function () {
                if(typeof id !== "undefined")
                    Boxy.load("/imaging/scan.details.php?id="+id);

            }});
            e.handled = true;
            e.preventDefault();
        }
    });
    $('tr[id*="_dt_an_tr_"] a.boxy').live('click', function(e){
        id = $(this).data("id");
        if(!e.handled){
            Boxy.load($(this).data("href"), {afterHide: function () {
                if(typeof id !== "undefined")
                    Boxy.load("/dentistry/request.details.php?id="+id);

            }});
            e.handled = true;
            e.preventDefault();
        }
    });
    $('a._newDialog_[data-href]').live('click',function(e){
        if(!e.handled){
            id = $(this).data("id");
            Boxy.load($(this).data("href"), { afterHide:function(){
                setTimeout(function(){Boxy.get($(".close")).hideAndUnload();}, 50);
            }});
            e.handled = true;
            e.preventDefault();
        }
    });
    $('a._editDialog_').live('click',function(e){
        if(!e.handled){
            id = $(this).data("id");
            title_ = $(this).data("title");
            Boxy.load($(this).data("href"), { afterHide:function(){
                setTimeout(function(){Boxy.get($(".close")).hideAndUnload();}, 50);
            }});
            e.handled = true;
            e.preventDefault();
        }
    });
    /*$('a.printNotes').live('click', function(e){
        if(!e.handled){
            window.open('/imaging/printNotes.php?id='+$(this).data("page-id"));
            e.handled=true;
        }
    });*/

    $('a.printDentistryNotes').live('click', function(e){
        if(!e.handled){
            window.open('/dentistry/printNotes.php?id='+$(this).data("page-id"));
            e.handled=true;
        }
    });

    $('.submitToApproveDentistry').live('click', function(e){
        if(!e.handled) {
            var scanId = $(this).data('id');
            Boxy.ask("Submit for approval?", ["Yes", "No"], function(choice){
                if(choice == "Yes"){
                    $.post('/dentistry/ajax.approve_.php', {id: scanId}, function (s) {
                        if (s.trim() == "ok") {
                            Boxy.info("Scan request sent for approval");
                            showTabs(11);
                        } else {
                            Boxy.alert("An error occurred");
                        }
                    });
                }
                else {
                }
            });
            e.handled = true;
        }
    });

    $('a[data-key]').live('click', function (e) { //procedure link
        if(!e.handled){
            Boxy.load('/procedures/tabs/home.php?id='+$(this).data("key"));
            //load a new window, patient procedures, not this particular
            e.handled = true;
        }
        e.preventDefault();
    });
    
    $('#printSummaryBtn').live('click', function(){
        myWindow=window.open("/print.visit-summary.php?pid=$id&from="+$('#from').val()+"&to="+$('#to').val(),"width=900,height=700");
        setTimeout(function(){
            myWindow.print();
            myWindow.close();
        },2000);
    });

    \$(document).on('change','#_changeDiagnosisType_', function(event){
        filter = "&active=" + $('#_changeDiagnosisStatus_').val() + "&type=" + $('#_changeDiagnosisType_').val() + "&severity=" + $('#_changeDiagnosisSeverity_').val();
        showTabs(5);
    });
    \$(document).on('change','#_changeDiagnosisSeverity_', function(event){
        filter = "&active=" + $('#_changeDiagnosisStatus_').val() + "&type=" + $('#_changeDiagnosisType_').val() + "&severity=" + $('#_changeDiagnosisSeverity_').val();
        showTabs(5);
    });
    \$(document).on('change','#_changeDiagnosisStatus_', function(event){
        filter = "&active=" + $('#_changeDiagnosisStatus_').val() + "&type=" + $('#_changeDiagnosisType_').val() + "&severity=" + $('#_changeDiagnosisSeverity_').val();
        showTabs(5);
    });
});
EOF;

$extra_link = array("title" => "Patients", "link" => "/patient_find-or-create.php");
$extra_style = array("/style/vitals.css", "/style/patient.procedure.css", "/assets/dataTables/media/css/jquery.dataTables.min.css", "/style/patient.procedure.css", "/style/easy.tabs.custom.css");
$extra_script = array("/assets/highcharts-4/js/highcharts.js","/assets/highcharts-4/js/highcharts-more.js", "/assets/dataTables/media/js/jquery.dataTables.min.js", "/js/jquery.easytabs.min.js");
include "template.inc.in.php";
//"http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js","/js/highcharts.js",
//"http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css",
