<?php
//require_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
//@session_start();
//$protect = new Protect();
//$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
//
//
//
//if(!$this_user->hasRole($protect->pharmacy)){
//    exit($protect->ACCESS_DENIED);
//}
if (!isset($_SESSION)) {
	session_start();
}

if (isset($_GET['incomplete'])) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/pharmaceuticals/incompletePrescription.php';
	exit;
}
if (isset($_GET['filled'])) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/pharmaceuticals/unfilledPrescription.php';
	exit;
}
if (isset($_GET['search'])) { ?>
	<form method="post" action="ajax.searchprescription.php" onsubmit="return AIM.submit(this, {'onStart':start, 'onComplete':loadResult});">
		<div class="row-fluid ui-bar-c">
			<div class="span2">
				<label>
					Search
					<input type="text" name="q" id="q" placeholder="Filter Requests by Request #">
				</label>
			</div>

			<div class="span4">
				<label>
					Search
					<input type="hidden" name="patient_id" id="patient_id" placeholder="Filter Requests by Patient Name/EMR #">
				</label>
			</div>

			<div class="span6">
				Filter by request date:
				<div class="input-prepend">
					<span class="add-on">From</span>
					<input class="span4" type="text" name="date_start" placeholder="Start Date">
					<span class="add-on">To</span>
					<input class="span4" type="text" name="date_stop" placeholder="Stop Date">
					<button class="btn" type="submit" id="date_filter">Search</button>
				</div>
			</div>
		</div>
	</form>

	<div id="searchBox"></div>
	<script type="text/javascript">
		$('[name="patient_id"]').css({'font-weight': 400}).select2({
			placeholder: "Filter list by patient",
			minimumInputLength: 3,
			width: '100%',
			allowClear: true,
			ajax: {
				url: "/api/search_patients.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:"+data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			formatSelection: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:"+data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			id: function (data) {
				return data.patientId;
			},
			initSelection: function (element, callback) {
				var id = $(element).val();
				if (id !== "") {
					$.ajax("/api/search_patients.php?pid=" + id, {
						dataType: "json"
					}).done(function (data) {
						callback(data);
					});
				}
			}
		}).change(function (e) {
			//if (!e.handled) {
			$.post('ajax.searchprescription.php', {
				page: 0,
				patient_id: $(this).val(),
				q: $('#q').val(),
				date_start: $('input[name="date_start"]').val(),
				date_stop: $('input[name="date_stop"]').val()
			}, function (s) {
				$('#searchBox').html(s);
			});
			//	e.handled = true;
			//}
		});

		$(document).on('click', '.list2.dataTables_wrapper a.paginate_button', function (e) {
			if (!e.clicked) {
				var page = $(this).data("page");
				if (!$(this).hasClass("disabled")) {
					$.post('ajax.searchprescription.php', {
							page: page,
							q: $('#q').val(),
							patient_id: $('[name="patient_id"]').val(),
							date_start: $('input[name="date_start"]').val(),
							date_stop: $('input[name="date_stop"]').val()
//							date: encodeURIComponent($('input[name="date_start"]').val()) + "," + encodeURIComponent($('input[name="date_stop"]').val())
						},
						function (s) {
							//console.warn($(s).find('#searchResults'));
							$('#searchBox').html(s);
						});
				}
				e.clicked = true;
			}
		});
	</script>
	<?php exit;
}


$host = $_SERVER['HTTP_HOST'];
$uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

$_SESSION['pharm_url'] = "http://$host$uri/";
$script_block = <<< EOF

function aTab(o) {
    container = $('#prescription_container');
    $('a.tab').each(function () {
        $(this).removeClass('on');
    });
    if (o === 1) {
        $('a.tab.incomplete').addClass('on');
        url = '/pharmaceuticals/incompletePrescription.php';
    } else if (o === 2) {
        $('a.tab.search').addClass('on');
        url = $('a.tab.search').attr('data-href');
    } else if (o === 3) {
        $('a.tab.filled').addClass('on');
        url = $('a.tab.filled').attr('data-href');
    }
    LoadDoc(container, url)
}

function LoadDoc(container, url) {
    $.ajax({
        url: url,
        beforeSend: function () {
            loading(container);
        },
        complete: function (s) {
            loaded(container, s);
        }
    });
    return false;
}
function loading(container) {
    container.show();
    container.html('<div align="center"><img src="/img/loading.gif" /> Loading Data</div>');
}
function loaded(container, respObj) {
    container.html(respObj.responseText);
    format();
}
function start() {
    $('#searchBox').html('Please wait ...<img src="/img/loading.gif"/>');
}
function loadResult(s) {
  $('#searchBox').html(s);
  format();
}
function format() {
	$('input[name="date_start"]').datetimepicker({format:'Y-m-d', timepicker: false});
  $('input[name="date_stop"]').datetimepicker({format:'Y-m-d', timepicker: false});
}
var r;
$(document).ready(function () {
  $('.head-link').live('click', function () {
    r = $('.' + $(this).attr('id'));
    r.fadeToggle('fast');
  });
  aTab(1);
  $('a.tab.new').click(function () {
    Boxy.load('/boxy.addRegimen.php', {title: 'New Prescription'});
  });
  $('a.transfer').live('click', function(e){
    Boxy.load($(this).data('href'), {title: 'Transfer Prescription'});
  });
  $('a._p_action').live('click', function (e) {
    var action = $(this).data("action");
    var item_id = $(this).data("id");
    clicked = $(this);
    if (action == "cancel") {
      cancelPrescription(item_id);
    } else if (action == "print") {
      printPres();
    } else if (action == "hide") {            
      Boxy.confirm("This line will be active but will not be processed now, until you cancel or fill/complete it.", function(){
       clicked.parent().parent().remove();
      });
    } else if (action == "substitute") {
      Boxy.confirm("Do you really want to substitute this prescription line with another specification?", function(){
       Boxy.load('/pharmaceuticals/boxy.substitute.line.php?line_id='+item_id);
      });
    }
    e.preventDefault();
  });
  $('a.printReceipt').live('click', function(e){
  Boxy.load($(this).data('href'), {title:'Print Filled Prescription Packing Slip'});
  });
  $('a._pres_action').live('click', function () {
    Boxy.load($(this).data('href'), {title: $(this).data('title')});
  });
  $('tr.pres_details, td.pres_details').live('click', function () {
    Boxy.load(\$(this).data('href'), {title: "Prescription Details", afterHide:function(){
      //\$('a.tab.on').click();
    }});
  });
});

function cancelPrescription(id) {
	if (confirm("Are you sure you want to cancel this prescription?")) {
		vex.dialog.prompt({
			message: 'Please enter your reason for cancellation',
			placeholder: 'Regimen Cancellation note',
			value: null,
			overlayClosesOnClick: false,
			beforeClose: function (e) {
				e.preventDefault();
			},
			callback: function (value) {
				if (value !== false && value !== '') {
					$.ajax({
						url: '/api/regimens.php',
						data: {action:'cancel',id:id,reason:value},
						type: 'POST',
						complete: function (xhr, status) {
							if (status == "success" && xhr.responseText == "true") {
								$('[data-id="' + id + '"]').prev().html("cancelled");
								$('[data-id="' + id + '"]').parents('tr').find('td input').prop('disabled', true);
								$('[data-id="' + id + '"]').parents('tr').find('td select').prop('disabled', true);
								$('[data-id="' + id + '"]').remove();
							}
						}
					});

				} else {

				}
			}, afterOpen: function (\$vexContent) {
				$('.vex-dialog-prompt-input').attr('autocomplete', 'off');
				\$submit = $(\$vexContent).find('[type="submit"]');
				\$submit.attr('disabled', true);
				\$vexContent.find('input').on('input', function () {
					if ($(this).val()) {
						\$submit.removeAttr('disabled');
					} else {
						\$submit.attr('disabled', true);
					}
				});
			}
		});
	}
}


function printPres() {
    //todo: print this dialog
    alert("print only this page");
}
EOF;
$page = "pages/pharmaceuticals/index.php";
$extra_script = ['/assets/moment/moment.min.js'];
$title = "Pharmaceuticals";
include "../template.inc.in.php";