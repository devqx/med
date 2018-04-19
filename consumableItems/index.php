<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/6/17
 * Time: 11:23 AM
 */
if (isset($_GET['open'])) {
	// Requested Items that are open/filled
	include_once $_SERVER['DOCUMENT_ROOT'] . '/consumableItems/open_patient_item.php';
	exit;
} elseif (isset($_GET['filled'])) {
	include_once $_SERVER['DOCUMENT_ROOT'] . '/consumableItems/index.php';
	exit;
} elseif (isset($_GET['search'])) { ?>
	<form method="post" action="ajax.searchedItems.php" onsubmit="return AIM.submit(this, {'onStart':start, 'onComplete':loadResult});">
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
				details.push(data.patientId ? "EMR ID:" + data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
			},
			formatSelection: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:" + data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
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
			$.post('ajax.searchedItems.php', {
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
					$.post('ajax.searchedItems.php', {
							page: page,
							q: $('#q').val(),
							patient_id: $('[name="patient_id"]').val(),
							date_start: $('input[name="date_start"]').val(),
							date_stop: $('input[name="date_stop"]').val()
						},
						function (s) {
							$('#searchBox').html(s);
						});
				}
				e.clicked = true;
			}
		});
	</script>
	<?php exit;
}


$script_block = <<<EOF
function aTab(o) {
    container = $('#prescription_container');
    $('a.tab').each(function () {
        $(this).removeClass('on');
    });
    if (o === 1) {
        $('a.tab.open').addClass('on');
        url = '/consumableItems/open_patient_item.php';
    } else if (o === 2) {
         $('a.tab.filled').addClass('on');
         url = '/consumableItems/filled_request.php';
    } else if (o === 3) {
        $('a.tab.search').addClass('on');
        url = $('a.tab.search').attr('data-href');
      
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
$(document).ready(function(){
			// open the first tab
			aTab(1);
			$('a.tab.new').click(function () {
				Boxy.load('/boxy.newItemRequest.php', {title: 'New Request'});
			});

			$('tr.pres_details, td.pres_details').live('click', function () {
				Boxy.load(\$(this).data('href'), {title: "Item Request Details", afterHide:function(){
					//\$('a.tab.on').click();
				}});
			});

			$('a._p_action').live('click', function (e) {
				var action = $(this).data("action");
				var item_id = $(this).data("id");
				clicked = $(this);
				if (action == "cancel") {
					cancelPrescription(item_id);
				} else if (action == "print") {
					printPres();
				}
				e.preventDefault();
			});

		});


function cancelPrescription(id) {
			if (confirm("Are you sure you want to cancel this Request?")) {
				vex.dialog.prompt({
					message: 'Please enter your reason for cancellation',
					placeholder: 'Request Cancellation note',
					value: null,
					overlayClosesOnClick: false,
					beforeClose: function (e) {
						e.preventDefault();
					},
					callback: function (value) {
						if (value !== false && value !== '') {
							$.ajax({
								url: '/api/item.php',
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
					},
					afterOpen: function (\$vexContent) {
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
$page = "pages/consumableItems/index.php";
$title = "Consumables";
include "../template.inc.in.php";