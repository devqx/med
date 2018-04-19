<?php
$script_block = <<<EOF
\$(document).ready(function(e) {
    \$('.hide').hide();
});

function start(){
    \$('#container1 div').remove();
    Boxy.load("/boxy.patients-searchresults.php?id="+\$('#searchfield').val()+"&type=antenatal",
    {
        title: "Search for Antenatal Patients",
        afterShow:function(){
            //$('#container1 div').remove();
            //$('#resultsTable').tableScroll({height:200});
        }
    });
}
function finished(s){}
function start1(){
    \$('#container1').html('<img src="/img/loading.gif"/>');
}
function finished1(){
    \$('#container1').load("ajax.find_patients_for_antenatal_enrollment.php?id="+\$('#searchfield1').val());
}
EOF;
$title = "Antenatal";
$page = "pages/antenatal/index.php";
include $_SERVER['DOCUMENT_ROOT']."/template.inc.in.php";