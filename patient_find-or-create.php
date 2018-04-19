<?php
$script_block = <<<EOF
function start(){
	Boxy.load("boxy.patients-searchresults.php?id="+\$('#searchfield').val()+"",
	{
	    title: "Search for Patients",
	    afterShow:function(){
            //$('#resultsTable').tableScroll({height:600});
            //$('#numCount').html($("#resultsTable tr").length + ' records found');
        }
    });
}
function finished(s){
	//not useful
}
EOF;

$page = "pages/patient_find-or-create.php";
include ("template.inc.in.php");
