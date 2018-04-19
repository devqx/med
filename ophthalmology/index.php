<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientOphthalmologyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyResultDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
$_centres = (new ServiceCenterDAO())->all('Ophthalmology');
$_categories = (new OphthalmologyCategoryDAO())->all();

$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

if (isset($_GET['incomplete'])) {
	$oph_centre = (isset($_POST['ophthalmology_centre_id'])) ? $_POST['ophthalmology_centre_id'] : null;
	$oph_category = (isset($_POST['ophthalmology_category_id'])) ? $_POST['ophthalmology_category_id'] : null;
	$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
	$pageSize = 10;
	$sort = (isset($_REQUEST['sort'])) ? $_REQUEST['sort'] : 'asc';
	$data = (new PatientOphthalmologyDAO())->getRequestsWithoutResult($page, $pageSize, $sort, $oph_centre, $oph_category, TRUE);
	$totalSearch = $data->total;
	if ($totalSearch < 1) { ?>
		<div class="row-fluid">
			<label class="span6"> Business Unit/Service Center
				<select name="ophthalmology_centre_id" placeholder="-- Select processing centre --">
					<option></option><?php foreach ($_centres as $l) { ?>
						<option value="<?= $l->getId() ?>" <?= (isset($_POST['ophthalmology_centre_id']) && $_POST['ophthalmology_centre_id'] === $l->getId()) ? ' selected="selected"' : '' ?>><?= $l->getName() ?></option><?php } ?>
				</select></label>
			<label class="span6">Optometry
				Categories<select name="ophthalmology_category_id" placeholder="-- Select Optometry category --">
					<option></option><?php foreach ($_categories as $lc) { ?>
						<option value="<?= $lc->getId() ?>"<?= isset($_POST['ophthalmology_category_id']) && $_POST['ophthalmology_category_id'] === $lc->getId() ? ' selected="selected"' : '' ?>><?= $lc->getName() ?></option><?php } ?>
				</select></label>
		</div>
		<div class="notify-bar">There are no Optometry requests to fulfil</div>
		<script>
			$('select[name="ophthalmology_centre_id"], select[name="ophthalmology_category_id"]').select2({
				width: '100%',
				allowClear: true
			}).change(function () {
				$.post('index.php?incomplete', {
					'page': 0,
					'ophthalmology_centre_id': $('select[name="ophthalmology_centre_id"]').val(),
					'ophthalmology_category_id': $('select[name="ophthalmology_category_id"]').val()
				}, function (s) {
					$('#opth_container').html(s);
				});
			});
		</script>
	<?php } else {
		?>
		<div class="row-fluid">
			<label class="span6"> Business Unit/Service Center
				<select name="ophthalmology_centre_id" placeholder="-- Select processing centre --">
					<option></option><?php foreach ($_centres as $l) { ?>
						<option value="<?= $l->getId() ?>" <?= (isset($_POST['ophthalmology_centre_id']) && $_POST['ophthalmology_centre_id'] === $l->getId()) ? ' selected="selected"' : '' ?>><?= $l->getName() ?></option><?php } ?>
				</select></label>
			<label class="span6">Optometry
				Categories<select name="ophthalmology_category_id" placeholder="-- Select Optometry category --">
					<option></option><?php foreach ($_categories as $lc) { ?>
						<option value="<?= $lc->getId() ?>"<?= isset($_POST['ophthalmology_category_id']) && $_POST['ophthalmology_category_id'] === $lc->getId() ? ' selected="selected"' : '' ?>><?= $lc->getName() ?></option><?php } ?>
				</select></label>
		</div>
		<div class="notify-bar"><i class="icon-info-sign"></i> <?= $totalSearch ?> Requests</div>
		<table class="table table-striped table-hover no-footer">
			<thead>
			<tr>
				<th class="sort <?= $sort ?>">Request Date <?php if ($sort == 'asc') { ?>
						<i class="icon-sort-up"></i><?php } else { ?><i class="icon-sort-down"></i><?php } ?></th>
				<th>ID</th>
				<th>Optometry Test</th>
				<th>Patient</th>
				<th>By</th>
				<th>*</th>
			</tr>
			</thead>
			<?php foreach ($data->data as $opthalms) { // $opthalms=new PatientOphthalmology();
				if ($opthalms->getPatient()) { ?>
					<tr>
						<td class="nowrap"><?= date("d M, Y h:iA", strtotime($opthalms->getOphthalmologyGroup()->getRequestTime())) ?></td>
						<td><?= $opthalms->getOphthalmologyGroup()->getGroupName() ?></td>
						<td><?= $opthalms->getOphthalmology()->getName() ?></td>
						<td>
							<span data-pid="<?= $opthalms->getPatient()->getId() ?>" class="profile"><?= $opthalms->getPatient()->getShortName() ?></span>
						</td>
						<td>
							<?= ($opthalms->getOphthalmologyGroup()->getReferral() !== null) ? '<span title="Referred from ' . $opthalms->getOphthalmologyGroup()->getReferral()->getName() . '(' . $opthalms->getOphthalmologyGroup()->getReferral()->getCompany()->getName() . ')"><i class="icon-info-sign"></i></span>' : '' ?>

							<span title="<?= $opthalms->getOphthalmologyGroup()->getRequestedBy()->getFullname() ?>"><?= $opthalms->getOphthalmologyGroup()->getRequestedBy()->getShortName() ?></span>
						</td>
						<td class="nowrap">
							<?php if ($opthalms->getOphthalmologyResult() === null /* && $opthalms->getReceived()===FALSE*/) { ?>
								<a href="javascript:;" class="cancelOphthalmologyLink" data-id="<?= $opthalms->getId() ?>">Cancel</a> |
							<?php } ?>

							<?php if ($opthalms->getOphthalmologyResult() === null) { ?>
								<a href="javascript:void(0)" onclick="Boxy.load('boxy.fillTestResult.php?testId=<?= $opthalms->getId() ?>&testType=<?= $opthalms->getOphthalmology()->getId() ?>', {title: 'Fill Result for <?= escape($opthalms->getOphthalmology()->getName()) ?>', afterHide: function () {
									location.reload();
									}})">Fill Result</a>
							<?php } ?>
						</td>
					</tr>
				<?php }
			} ?>
		</table>
		<div class="list1 dataTables_wrapper no-footer">
			<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
				results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
			</div>
			<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
				<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
					records</a>
				<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
					records</a>
				<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?>
					records</a>
				<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?>
					records</a>
			</div>
		</div>
		<script>
			$('select[name="ophthalmology_centre_id"], select[name="ophthalmology_category_id"]').select2({
				width: '100%',
				allowClear: true
			}).change(function () {
				$.post('index.php?incomplete', {
					'page': 0,
					'ophthalmology_centre_id': $('select[name="ophthalmology_centre_id"]').val(),
					'ophthalmology_category_id': $('select[name="ophthalmology_category_id"]').val()
				}, function (s) {
					$('#opth_container').html(s);
				});
			});
			$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
				if (!e.clicked) {
					var page = $(this).data("page");
					if (!$(this).hasClass("disabled")) {
						$.post('index.php?incomplete', {
							'page': page,
							'ophthalmology_centre_id': $('select[name="ophthalmology_centre_id"]').val(),
							'ophthalmology_category_id': $('select[name="ophthalmology_category_id"]').val()
						}, function (s) {
							$('#opth_container').html(s);
						});
					}
					e.clicked = true;
				}
			});
			$(function () {
				$('th.sort').on('click', function (e) {
					if (!e.clicked) {
						var sortDate = ($(this).hasClass('asc')) ? 'desc' : 'asc';
						$.post('index.php?incomplete', {
							'page': 0,
							'ophthalmology_centre_id': $('select[name="ophthalmology_centre_id"]').val(),
							'ophthalmology_category_id': $('select[name="ophthalmology_category_id"]').val(),
							'sort': sortDate
						}, function (s) {
							$('#opth_container').html(s);
						});
					}
					e.clicked = true;
				});
			});
		</script>
		<?php
	}
	exit;
} else if (isset($_GET['search'])) { ?>
	<form method="post" action="ajax.find.php" onsubmit="return AIM.submit(this, {'onStart':start, onComplete: loadResult});">
		<div class="row-fluid">
			<label class="span10"><input type="search" name="q" id="q" class="bigSearchField"
			                             placeholder="search requests by request id, or patient emr" autocomplete="off"></label>
			<button type="submit" class="btn span2">Search &raquo;</button>
		</div>
	</form>
	<div id="searchBox"></div>
	<script>
		document.getElementById('q').focus();
		$(document).on('click', '.resultsPager.dataTables_wrapper a.paginate_button', function (e) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.post('ajax.find.php', {'q': $('#q').val(), 'page': page}, function (s) {
					$('#searchBox').html(s);
				});
			}
		});
	</script>

	<?php exit;
} else if (isset($_GET['datesearch'])) {
	if (isset($_POST['date_start']) && $_POST['date_stop']) {
		$start = $_POST['date_start'];
		$stop = $_POST['date_stop'];
	} else {
		$start = date("Y-m-d");
		$stop = date("Y-m-d");
	}
	?>
	<div class="ui-bar-c">
		Filter by date:
		<form method="post" name="dateFilter" action="ajax.findlab_bydate.php" onsubmit="return AIM.submit(this, {'onStart':start, 'onComplete':loadResult});">
			<div class="input-prepend">
				<span class="add-on">From</span>
				<input class="span2" type="text" name="date_start" value="<?= isset($start) ? $start : '' ?>" placeholder="Start Date">
				<span class="add-on">To</span>
				<input class="span2" type="text" name="date_stop" value="<?= isset($stop) ? $stop : '' ?>" placeholder="Stop Date">
				<button class="btn" type="submit" id="date_filter">Apply</button>
			</div>
		</form>
	</div>
	<div id="searchBox"></div>
	<script>
		$(document).on('click', '.bydatelist.dataTables_wrapper a.paginate_button', function (e) {
			var page = $(this).data("page");
			var date_start = $('form[name="dateFilter"] > .input-prepend > input[name="date_start"]').val();
			var date_stop = $('form[name="dateFilter"] > .input-prepend > input[name="date_stop"]').val();
			if (!$(this).hasClass("disabled")) {
				$.post('ajax.findlab_bydate.php', {
					'page': page,
					'date_start': date_start,
					'date_stop': date_stop
				}, function (s) {
					$('#searchBox').html(s);
				});
			}
		});
	</script>
	<?php exit;
} else if (isset($_GET['approve']) && $this_user->hasRole($protect->lab_super)) {
	$oph_centre = (isset($_POST['ophthalmology_centre_id'])) ? $_POST['ophthalmology_centre_id'] : null;
	$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
	$pageSize = 10;
	$data = (new OphthalmologyResultDAO())->getUnApprovedResult($page, $pageSize, $oph_centre, true);
	$totalSearch = $data->total;
	if ($totalSearch > 0) {
		?>
		<label> Business Unit/Service Center
			<select name="ophthalmology_centre_id" placeholder="-- Select processing Optometry centre --">
				<option></option><?php foreach ($_centres as $l) { ?>
					<option value="<?= $l->getId() ?>" <?= (isset($_POST['ophthalmology_centre_id']) && $_POST['ophthalmology_centre_id'] === $l->getId()) ? ' selected="selected"' : '' ?>><?= $l->getName() ?></option><?php } ?>
			</select></label>
		<table class="table table-striped approveList">
			<thead>
			<tr>
				<th class="hide">sort time</th>
				<th>Request Date</th>
				<th>Request ID</th>
				<th>Optometry Service</th>
				<th>Patient</th>
				<th>Result</th>
				<th>Notes</th>
				<th>*</th>
			</tr>
			</thead>
			<tbody><?php

			$group = "";
			$groupList = [];
			foreach ($data->data as $l) { //$l=new OphthalmologyResult();
				?>
				<tr>
					<td class="hide"><?= strtotime($l->getPatientOphthalmology()->getOphthalmologyGroup()->getRequestTime()) ?></td>
					<td class="nowrap"><?= date("d M, Y h:iA", strtotime($l->getPatientOphthalmology()->getOphthalmologyGroup()->getRequestTime())) ?>
						<!--<?= $l->getId() ?>--></td>
					<td><?= $l->getPatientOphthalmology()->getOphthalmologyGroup()->getGroupName() ?></td>
					<td><?= $l->getPatientOphthalmology()->getOphthalmology()->getName() ?></td>
					<td>
						<?= ($l->getPatientOphthalmology()->getOphthalmologyGroup()->getReferral() !== null) ? '<span title="Referred from ' . $l->getPatientOphthalmology()->getOphthalmologyGroup()->getReferral()->getName() . '(' . $l->getPatientOphthalmology()->getOphthalmologyGroup()->getReferral()->getCompany()->getName() . ')"><i class="icon-info-sign"></i></span>' : '' ?>

						<span data-pid="<?= $l->getPatientOphthalmology()->getOphthalmologyGroup()->getPatient()->getId() ?>" class="profile"><?= $l->getPatientOphthalmology()->getOphthalmologyGroup()->getPatient()->getShortName() ?></span>
					</td>
					<td>
						<a class="<?= $l->getPatientOphthalmology()->getOphthalmologyGroup()->getGroupName() ?>" href="javascript:;" onclick="Boxy.load('/ophthalmology/viewOphthalmologyResults.php?id=<?= $l->getId() ?>', {title:'<?= $l->getPatientOphthalmology()->getOphthalmology()->getName() ?> Result'})">Values</a>
					</td>
					<td>
						<a href="javascript:;" onclick="Boxy.load('/ophthalmology/ophthalmology.notes.php?id=<?= $l->getPatientOphthalmology()->getId() ?>', {title: 'Optometry Notes'})">Notes</a>
					</td>
					<?php
					if ($group !== $l->getPatientOphthalmology()->getOphthalmologyGroup()->getGroupName()) {
						$group = $l->getPatientOphthalmology()->getOphthalmologyGroup()->getGroupName();
						$groupList[count($groupList)] = 1;
						?>
						<td data-group='<?= $group ?>' rowspan="0" style="display: table-cell; vertical-align: middle; text-align: center">
							<a href="/ophthalmology/printOphthalmology.php?gid=<?= $group ?>" title="Print this result" target="_blank"><i class="icon-print"></i></a>
						</td>
						<?php
					} else {
						$groupList[count($groupList) - 1] = $groupList[count($groupList) - 1] + 1;
					}
					?>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<div class="list3 dataTables_wrapper no-footer">
			<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
				results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
			</div>

			<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
				<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
					records</a>
				<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
					records</a>
				<?php /*<span>
                <?php if(ceil($data->total/$pageSize) >= 1 ){?><a class="paginate_button <?= (1 == $page) ?"current":""?>" data-page="1">1</a><?php }?>
                <?php if(ceil($data->total/$pageSize) >= 2){?><a class="paginate_button <?= (2 == $page) ?"current":""?>" data-page="2">2</a><?php }?>
                <?php if(ceil($data->total/$pageSize) > 2){?><span>&hellip;</span> <a class="paginate_button" data-page="<?= ceil($data->total/$pageSize) ?>"><?= ceil($data->total/$pageSize) ?></a><?php }?>
            </span> */ ?>
				<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?>
					records</a>
				<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?>
					records</a>
			</div>
		</div>
		<script>
			$('select[name="ophthalmology_centre_id"]').select2({width: '100%', allowClear: true}).change(function () {
				$.post('index.php?approve', {
					'page': 0,
					'ophthalmology_centre_id': $('select[name="ophthalmology_centre_id"]').val()
				}, function (s) {
					$('#opth_container').html(s);
				});
			});
			$(document).on('click', '.list3.dataTables_wrapper a.paginate_button', function (e) {
				var page = $(this).data("page");
				if (!$(this).hasClass("disabled")) {
					$.post('index.php?approve', {'page': page}, function (s) {
						$('#opth_container').html(s);
					});
				}
			});

		</script>
	<?php } else { ?>
		<label> Business Unit/Service Center<select name="ophthalmology_centre_id" placeholder="-- Select processing centre --">
				<option></option><?php foreach ($_centres as $l) { ?>
					<option value="<?= $l->getId() ?>" <?= (isset($_POST['ophthalmology_centre_id']) && $_POST['ophthalmology_centre_id'] === $l->getId()) ? ' selected="selected"' : '' ?>><?= $l->getName() ?></option><?php } ?>
			</select></label>

		<script>
			$('select[name="ophthalmology_centre_id"]').select2({width: '100%', allowClear: true}).change(function () {
				$.post('index.php?approve', {
					'page': 0,
					'ophthalmology_centre_id': $('select[name="ophthalmology_centre_id"]').val()
				}, function (s) {
					$('#opth_container').html(s);
				});
			});
		</script>
		<div class="notify-bar">No "approve-pending" requests</div>
	<?php } ?>
	<script>
		var groupList =<?= json_encode($groupList) ?>;
		$("table tr td[data-group]").each(function (ind) {
			//console.log(groupList[ind] + "\t" + ind)
			$(this).attr("rowspan", groupList[ind]);
		});
		//$('table.approveList').dataTable();
	</script>
	<?php
	exit;
} else if (isset($_GET['itemsrequests'])) {
	include_once "items/items_requests.php";
	exit;
}

$script_block = <<<EOF
function aTab(o){
    container = $('#opth_container');
    $('a.tab').each(function(){
        $(this).removeClass('on');
    });
    if(o===1){
        $('a.tab.incomplete').addClass('on');
        url = $('a.tab.incomplete').attr('data-href');
    }else if(o === 2){
        $('a.tab.search').addClass('on');
        url = $('a.tab.search').attr('data-href');
    }
    else if(o === 3){
       $('a.tab.addnew').addClass('on');
       Boxy.load('/ophthalmology/new.php',{afterHide:function(){location.reload()}});
       return true;
    }else if(o===4) {
        $('a.tab.approve').addClass('on');
        url = $('a.tab.approve').attr('data-href');
    }else if(o===5) {
        $('a.tab.datesearch').addClass('on');
        url = $('a.tab.datesearch').attr('data-href');
    }else if(o===6) {
        $('a.tab.itemsrequests').addClass('on');
        url = $('a.tab.itemsrequests').attr('data-href');
    }
    LoadDoc(container, url);
}

function LoadDoc(container, url){
    \$.ajax({
        url:url,
        beforeSend: function(){
            loading(container);
        },
        complete:function(s){
            loaded(container, s);

            $('input[name="date_start"]').datetimepicker({
                format:'Y-m-d',
                timepicker:false
            });$('input[name="date_stop"]').datetimepicker({
                format:'Y-m-d',
                timepicker:false
            });
            $("*[title]").tooltipster();
            $(document).trigger("ajaxStop");
        },
    });
    return false;
}
function loading(container){
    container.html('<div align="center"><img src="/img/loading.gif" /> Loading Data ...</div>').show();
}
function loaded(container, respObj){
    container.html(respObj.responseText);
    $(document).trigger("ajaxStop");
}
function start(){
	\$('#searchBox').html('<img src="/img/loading.gif"/> Please wait ...');
}

function loadResult(s){
    \$('#searchBox').html(s);
    $(document).trigger("ajaxStop");
}

$(document).ready(function(){
    aTab(1);
    \$('a.__aprove').live('click',function(){
        var id_ = $(this).data("id");
        var lid_ = $(this).data("ophthalmology-id");
        Boxy.confirm("Are you sure to approve this result", function(){
            \$.ajax({
                url:'/ophthalmology/result.action.php',
                data:{id:id_, action:'approve'},
                type:'post',
                dataType:'json',
                beforeSend:function(){
                    \$.blockUI({message: '<h6 class="fadedText" style="font-size:200%">Please wait ...</h6>',css: {borderWidth: '0',backgroundColor:'transparent',}});
                },
                success:function(data){
                    \$.unblockUI();
                    if(data.status==="ok"){
                        Boxy.info(data.message, function(){
                            Boxy.confirm("Do you want to print this result?", function(){
                                window.open("/pdf.php?page=/ophthalmology/printOphthalmology.php?gid="+lid_+"&title="+lid_, "_blank");
                            });
                        });
                        Boxy.get($('.close')).hideAndUnload();
                        aTab(4);
                    }else {
                        Boxy.alert(data.message);
                    }
                },
                error:function(){
                    Boxy.alert('Failed to approve result. A server error occurred');
                    \$.unblockUI();
                }
            });
        });
    });

    \$('a.__reject').live('click',function(){
        var id_ = $(this).data("id");
        Boxy.confirm("Are you sure to reject this result; <br>this will remove the results attached to this request.", function(){
        \$.ajax({
            url:'/ophthalmology/result.action.php',
            data:{id:id_, action: 'reject'},
            type:'post',
            dataType:'json',
            beforeSend:function(){
                \$.blockUI({message: '<h6 class="fadedText" style="font-size:200%">Please wait ...</h6>',css: {borderWidth: '0',backgroundColor:'transparent',}});
            },
            success:function(data){
                \$.unblockUI();
                if(data.status==="ok"){
                    Boxy.info(data.message);
                    Boxy.get($('.close')).hideAndUnload();
                    aTab(4);
                }else {
                    Boxy.alert(data.message);
                }
            },
            error:function(){
                Boxy.alert('Action failed. A server error occurred');
                \$.unblockUI();
            }
        });});
    });

    \$('a.cancelOphthalmologyLink').live('click', function(e){
        var id = $(this).data("id");
        if(e.handled != true){
            Boxy.ask("Are you sure you want to cancel this request line item?", ["Yes", "No"], function(choice){
                if(choice == "Yes"){
                    $.post('/api/ophthalmologyrequests.php', {id: id, action:"cancel"}, function(s){
                        if(s.trim()=="ok"){
                            //remove this cancel button, also remove the take specimen link
                            $('a.cancelOphthalmologyLink[data-id="'+id+'"]').next('a:contains("Take specimen")').remove();
                            $('a.cancelOphthalmologyLink[data-id="'+id+'"]').parent().parent().remove();
                            //todo: show "Cancelled" status text
                        } else {
                            Boxy.alert("An error occurred [The request might have been cancelled already] OR [Service Bills has been transferred,Please Reverse the bill AND try again]");
                        }
                    });
                }
            });
            e.handled=true;
        }
    });

    \$('a.receive').live('click', function(e){
        var id = $(this).data("id");
        var lab = $(this).data("ophthalmology");
        if(e.handled != true){
            Boxy.ask("Acknowledge to receive specimen: "+lab, ["Yes", "No"], function(choice){
                if(choice == "Yes"){
                    $.post('/api/ophthalmologyrequests.php', {id: id, action:"receive"}, function(s){
                        if(s.trim()=="ok"){
                            location.reload();
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
$page = "pages/ophthalmology/index.php";
$title = "Optometry";
include "../template.inc.in.php";
?>