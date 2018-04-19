<?php

if (!isset($_SESSION)) {
    session_start();
}
//require_once 'protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.labs.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
$patient = new Manager();
$bills = new Bills();
$id = escape($_GET['pid']);
$antenatal = is_dir($_SERVER['DOCUMENT_ROOT'] . "/antenatal") ? (new AntenatalEnrollmentDAO())->getActiveInstance($id) : null;
$page = $_SERVER['DOCUMENT_ROOT'] . "/pages/admissions/inpatient_profile.php";

$script_block = <<<EOF
\$(function(){
    $("#tabbedPane ul").droptabs({});
    $('a[title="more details"]').live('click', function (e) { //procedure link
        if(!e.handled){
            Boxy.load('/procedures/tabs/home.php?id='+$(this).data("key"));
            //load a new window, patient procedures, not this particular
            e.handled = true;
        }
        e.preventDefault();
    });
    $('a[data-key]').live('click', function (e) { //procedure link
        if(!e.handled){
            Boxy.load('/procedures/tabs/home.php?id='+$(this).data("key"));
            //load a new window, patient procedures, not this particular
            e.handled = true;
        }
        e.preventDefault();
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
});
EOF;

$extra_link = array("title" => "Admissions", "link" => "/admissions");
$extra_style = array("/style/vitals.css", "/assets/dataTables/media/css/jquery.dataTables.min.css","/assets/css/camStyles.css", "/style/patient.procedure.css","/style/easy.tabs.custom.css");
$extra_script = array("/assets/highcharts-4/js/highcharts.js", "/assets/dataTables/media/js/jquery.dataTables.min.js", "/assets/js/camScript.js","/assets/webcam/webcam.js","/js/jquery.easytabs.min.js");
include ("../template.inc.in.php");
