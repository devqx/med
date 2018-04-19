<?php
require_once("../protect.php");
$script_block = <<<EOF

function showAdmissionSearch(){
	Boxy.load('boxy.admission.search.php',{title:'Search'});
}
//TODO: used to reload patient_round page as well as the roundings counter indicator on the sub-menu
function refreshRoundCount(){}

$(document).ready(function(){
    x = setInterval(refreshRoundCount, 2500);
    $('#adm_search').keyup(function(){
        alert("instant search not available");
    });

    $('a.patient_round_list_link').live('click',function(e){
        LoadDoc($('#admission_container'), $(this).attr('data-href'));
        e.preventDefault();
    });
});
EOF;


$page = "home_.php";
//$page = "pages/admissions/index.php";
$title = "Admissions";
$extra_style = array('/style/vitals.css', '/style/range.css','/font/medical/medical.css');
$extra_scripts = array('/js/highcharts.js', '/js/jquery.tools.min.js');

include "../template.inc.in.php";
