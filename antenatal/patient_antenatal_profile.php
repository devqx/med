<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/functions/func.php";
sessionExpired();

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.antenatal.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
$patient = new Manager();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.labs.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$ante = new Antenatal();
$id = escape($_GET['id']);
if (!isset($_SESSION)) {
    session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();

if (isset($_GET['view']) && $_GET['view'] == "notes") {
    //$notes = (new VisitNotesDAO())->getPatientNotes($_GET['id'], 0, 100, FALSE, NULL, 'antenatal'); ///????!!!!
    echo $patient->getPatientVisitNotes($_GET['id'], 'antenatal');
    exit;
}

if (isset($_GET['view']) && $_GET['view'] == "vaccines") {
    include_once "tabs/vaccines.php";
    exit;
}
if (isset($_GET['view']) && $_GET['view'] == "labs") {
    sessionExpired();
    $_GET['id'] = $id;
    include_once $_SERVER['DOCUMENT_ROOT'] . '/labs/patientLabs.php';
    exit;
}
if (isset($_GET['view']) && $_GET['view'] == "riskAss") {
    $_GET['id'] = $id;
    include_once $_SERVER['DOCUMENT_ROOT'] . '/antenatal/tabs/risk_assessment.php';
    exit;
}
if (isset($_GET['view']) && $_GET['view'] == "assessments") {
    include_once "tabs/assessment.php";
    exit;
}
if (isset($_GET['view']) && $_GET['view'] == "medications") {
    exit;
}

if (isset($_GET['view']) && $_GET['view'] == "vitals") {
    exit;
}

if (isset($_GET['view']) && $_GET['view'] == "precon") {
    sessionExpired();

    $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
    $pageSize = 10;

    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/StaffDirectoryDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDiagnosisDAO.php';
    $this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

    $type = (isset($_REQUEST['type'])? $_REQUEST['type']: 'confirmed');
    $active = (isset($_REQUEST['active']) ? $_REQUEST['active']:'true');
    $severity = (isset($_REQUEST['severity']) ? $_REQUEST['severity']:'chronic');
    $data = (new PatientDiagnosisDAO())->one($id, $type, $active, $severity, $page, $pageSize );
    include_once $_SERVER['DOCUMENT_ROOT'] . '/diagnoses/patient_diagnoses.php';
    exit;
}

if (isset($_GET['view']) && $_GET['view'] == "imaging") {
    sessionExpired();
    $_GET['pid'] = $id;
    include_once $_SERVER['DOCUMENT_ROOT'] . '/imaging/patient_images.php';
    exit;
}

if (isset($_GET['view']) && $_GET['view'] == "gyn_history") {
    sessionExpired();
    $_GET['pid'] = $id;
    include_once $_SERVER['DOCUMENT_ROOT'] . '/antenatal/tabs/patient_gyn_history.php';
    exit;
}
if (isset($_GET['view']) && $_GET['view'] == "obstetrics_history") {
    sessionExpired();
    $_GET['pid'] = $id;
    include_once $_SERVER['DOCUMENT_ROOT'] . '/antenatal/tabs/patient_obstetrics_history.php';
    exit;
}

$page = "pages/antenatal/antenatal_profile.php";
$script_block = <<<EOF
\$(document).ready(function(){
    $(".container-fluid ul").droptabs({});
    showTabs(4);
    var tItems = $("#tabbedPane ul li");
    var ntot = tItems.length;
    tItems.css({'min-width': ($("#tabbedPane").width() / ntot) - 20});
    \$('img.passport').parent('a').click(function(){
        \$("#camera").show('slow');
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

    $('a.printNotes').live('click', function(e){
        if(!e.handled){
            window.open('/imaging/printNotes.php?id='+$(this).data("page-id"));
            e.handled=true;
        }
    });

    $('.submitToApprove').live('click', function(e){
        if(!e.handled) {
            var scanId = $(this).data('id');
            Boxy.ask("Submit for approval?", ["Yes", "No"], function(choice){
                if(choice == "Yes"){
                    $.post('/imaging/ajax.approve_.php', {id: scanId}, function (s) {
                        if (s.trim() == "ok") {
                            Boxy.info("Scan request sent for approval");
                            showTabs(10);
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

    \$('a.cancelLabLink').live('click', function(e){
        var id = $(this).data("id");
        if(e.handled != true){
            Boxy.ask("Are you sure you want to cancel this request line item?", ["Yes", "No"], function(choice){
                if(choice == "Yes"){
                    $.post('/api/labrequests.php', {id: id, action:"cancel"}, function(s){
                        if(s.trim()=="ok"){
                            //remove this cancel button, also remove the take specimen link
                            $('a.cancelLabLink[data-id="'+id+'"]').next('a:contains("Take specimen")').remove();
                            $('a.cancelLabLink[data-id="'+id+'"]').remove();
                        } else {
                            Boxy.alert("An error occurred");
                        }
                    });
                }
            });
            e.handled=true;
        }
    });

    \$(document).on('change','#_changeDiagnosisType_', function(event){
        k = $('#_changeDiagnosisStatus_').val();
        filter = "&active=" + k + "&type=" + this.value;
        showTabs(5);
    });
    \$(document).on('change','#_changeDiagnosisStatus_', function(event){
        k = this.value;
        filter = "&active=" + k + "&type=" + $('#_changeDiagnosisType_').val();
        showTabs(5);
    });
});
EOF;
$title = "Antenatal";
$extra_style = array("/style/vitals.css", "/assets/dataTables/media/css/jquery.dataTables.min.css","/assets/css/camStyles.css","/style/easy.tabs.custom.css","/style/tipTip.css","/style/vaccine.css");
$extra_script = array("/assets/droptabs/jquery.droptabs.js", "/assets/highcharts-4/js/highcharts.js", "/assets/dataTables/media/js/jquery.dataTables.min.js", "/assets/js/camScript.js","/assets/webcam/webcam.js","/js/jquery.easytabs.min.js","/js/jquery.tipTip.minified.js", "/assets/droptabs/jquery.droptabs.js");
include "../template.inc.in.php";