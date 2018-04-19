<?php

if (!isset($_SESSION)) {
	session_start();
}
$host = $_SERVER['HTTP_HOST'];
//$port = ':'.$_SERVER['SERVER_PORT'].'/';
$uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$_SESSION['bill_url'] = "http://$host$uri/";

if (isset($_GET['id'], $_GET['action'])) {

}

if (isset($_GET['outstanding']) && $_GET['outstanding'] == 'true' && !isset($_GET['fText'])) {
	$open_action = "$('a.tab:nth-child(2)').click();";
} else if (isset($_GET['outstanding']) && $_GET['outstanding'] == 'true' && isset($_GET['fText'])) {
	$open_action = '$("a.tab:nth-child(2)").click();setTimeout(function(){$(".dataTables_filter input").val($.querystring(document.URL).fText).trigger("keyup");},500)';
} else {
	$open_action = "loadFindBills();";
}


$script_block = <<<EOF
\$(document).ready(function(){
  var x=new Image();
  x.src='/img/loading.gif';


  \$("#outStandingLink").live('click',function(e){
    //alert("clear filter");
    loadOutStandingBills();
    $('a.tab').each(function(){
        $(this).removeClass('on');
    });
    $(this).addClass('on');

    $('#billDoc').fadeOut().html('');
    e.preventDefault();
  });
  
  
  \$("#insuranceBillInk").live('click',function(e){
    loadInsuranceBills();
    $('a.tab').each(function(){
        $(this).removeClass('on');
    });
    $(this).addClass('on');
    $('#billDoc').fadeOut().html('');
    e.preventDefault();
  });
  
  
  
  \$("a.billViewSummary").live('click',function(e){
    loadDoc($('#billContent'),$(this).attr('data-href'));
    $('a.tab').each(function(){
        $(this).removeClass('on');
    });
    $(this).addClass('on');
    e.preventDefault();
  });
  \$("a#addBillInk").bind('click',function(e){
    Boxy.load('/billing/boxy.misc.charge.php',{title:'Add Miscellaneous Bill'});
    e.preventDefault();
  });
  
  $("#linkUnReviewedBills").bind('click', function(e){
  	loadDoc($('#billContent'),$(this).attr('data-href'));
  	$('#billDoc').fadeOut().html('');
    $('a.tab').each(function(){
        $(this).removeClass('on');
    });
    $(this).addClass('on');
    e.preventDefault();
  });
  
  $("#estimated_bills").bind('click', function(e){
    loadDoc($('#billContent'),$(this).attr('data-href'));
    $('#billDoc').fadeOut().html('');
     $('a.tab').each(function(){
        $(this).removeClass('on');
    });
    $(this).addClass('on');
    e.preventDefault();
  });
  

  
  $("#pa_codes").bind('click', function(e){
  	loadDoc($('#billContent'),$(this).attr('data-href'));
  	$('#billDoc').fadeOut().html('');
    $('a.tab').each(function(){
        $(this).removeClass('on');
    });
    $(this).addClass('on');
    e.preventDefault();
  });
  
});

 var loading_str='<table width="100%" height="200"><tr><td align="center">Loading Document...<br><br><img src="/img/loading.gif"></td></tr></table>';
 var failed_To_load = '<div class="warning-bar">The requested document failed to load.</div>';

function loadDoc(where, url){
    \$.ajax({
		type:'GET',
		url:url,
		beforeSend:function(){where.html(loading_str).show();},
		success:function(s){
			where.html(s);
		},
		error:function(){
		    where.html(failed_To_load);
		}
	});
}



function loadOutStandingBills(){
	\$.ajax({
		type:'GET',
		url:'all_outstandingbills.php',
		beforeSend:function(){\$('#billContent').html(loading_str);},
		success:function(s){
		    \$('#billContent').html(s);
		},
		error:function(){
		    \$('#billContent').html(failed_To_load);
		}
	});
}


function loadInsuranceBills(){
	\$.ajax({
		type:'GET',
		url:'insurance_cont.php',
		beforeSend:function(){\$('#billContent').html(loading_str);},
		success:function(s){
		    \$('#billContent').html(s);
		},
		error:function(){
		    \$('#billContent').html(failed_To_load);
		}
	});
}

function loadFindBills(){
	\$.ajax({
		type:'GET',
		url:'find_bills.php',
		beforeSend:function(){\$('#billContent').html(loading_str);},
		success:function(s){
			\$('#billContent').html(s);
		}
	});
}
function selectAllBills(s){
	//eval("\$('input[name="+s+"]').attr('checked', true).iCheck('update');");
}
function deSelectAllBills(s){
	eval("\$('input[name="+s+"]').attr('checked', false).iCheck('update');");
}


\$(document).ready(function(e) {
    {$open_action}
});
EOF;

$page = "pages/billing/index.php";
$title = "Billing";
$extra_script = ['/assets/jquery.querystring.js'];

include "../template.inc.in.php";
