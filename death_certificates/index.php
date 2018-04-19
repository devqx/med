<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/28/15
 * Time: 10:17 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DeathDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';

$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

if (isset($_GET['certs'])) {
	$page = isset($_POST['page']) ? $_POST['page'] : 0;
	$pageSize = 10;
	$certificates = (new DeathDAO())->all($page, $pageSize);
	$totalSearch = $certificates->total;
	if ($totalSearch < 1) { ?>
		<div class="notify-bar">No death certificates yet</div>
	<?php } else { ?>
		<div class="notify-bar"><i class="icon-info-sign"></i> <?= $totalSearch ?> Documents</div>
		<table class="table table-striped table-hover no-footer">
			<thead>
			<tr>
				<th>Date</th>
				<th>Certificate #</th>
				<th>Patient</th>
				<th>Time of Death</th>
				<th>*</th>
				
			</tr>
			</thead>
			<tbody>
			<?php foreach ($certificates->data as $c) { ?>
				<tr>
					<td><?= date(MainConfig::$dateTimeFormat, strtotime($c->getCreateDate())) ?></td>
					<td>
						<a href="javascript:void(0)" class="viewerContent" data-href="/death_certificates/viewDeathCert.php?id=<?= $c->getId() ?>"> <?= $c->getCertNumber() ?> </a>
					</td>
					<td><span class="profile" data-pid="<?= $c->getPatient()->getId() ?>"><?= $c->getPatient()->getFullname() ?></span></td>
					<td><?= date(MainConfig::$dateTimeFormat, strtotime($c->getTimeOfDeath())) ?></td>
					<td><?php if ($c->getValidatedBy() == NULL) { ?>
						<a href="javascript:void(0)" class="editCert" data-id="<?= $c->getId() ?>" data-pat="<?= $c->getPatient()->getShortname() ?>">Edit</a> |
						<a href="javascript:void(0)" class="validateCert" data-id="<?= $c->getId() ?>" data-pat="<?= $c->getPatient()->getShortname() ?>">Validate</a>
						<?php } ?>
					</td>
					<td>
						<a  href="/death_certificates/printDeathCert.php?id=<?= $c->getId() ?>" title="Print death certificate"  target="_blank">Print</a>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<div class="list1 dataTables_wrapper no-footer">
			<div class="dataTables_info" id="DataTables_Table_0_info" role="status"
			     aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?>
				of <?= ceil($totalSearch / $pageSize) ?>)
			</div>
			<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
				<a id="DataTables_Table_1_first" data-page="0"
				   class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
					records</a>
				<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>"
				   class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
					records</a>
				<a id="DataTables_Table_1_last"
				   class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
				   data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
				<a id="DataTables_Table_1_next"
				   class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
				   data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
			</div>
		</div>
		<script>
			$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
				if (!e.clicked) {
					var page = $(this).data("page");
					if (!$(this).hasClass("disabled")) {
						$.post('index.php?certs', {'page': page}, function (s) {
							$('#deathCert_container').html(s);
						});
					}
					e.clicked = true;
				}
			}).on('click', 'a.validateCert', function (e) {
				var id = $(this).data("id");
				var patient = $(this).data("pat");
				if (e.handled !== true) {
					Boxy.ask("Validate certificate for the death of: " + patient, ["Yes", "No"], function (choice) {
						if (choice === "Yes") {
							$.post('/api/death_cert.php', {id: id, action: "validate"}, function (s) {
								if (s.trim() === "ok") {
									reloadThisPage();
								} else {
									Boxy.alert("An error occurred");
								}
							});
						}
					});
					e.handled = true;
				}
			}).on('click', 'a.editCert', function (e) {
				var id = $(this).data("id");
				if (e.handled !== true) {
					Boxy.load('/death_certificates/edit_certificate.php?id='+id);
					e.handled = true;
				}
			}).on('click', 'a.viewerContent', function (e) {
				var href = $(e.target).data("href");
				if (!e.handled) {
					Boxy.load(href);
					e.handled = true;
				}
			});

			var reloadThisPage = function () {
				var page = '#DataTables_Table_1_next';
				$.post('index.php?certs', {'page': eval($(page).data("page") - 1)}, function (s) {
					$('#deathCert_container').html(s);
				});
			};
		</script>
		<?php
	}
	exit;
} else if (isset($_GET['search'])) {
	?>
	<form method="post" action="ajax.findDeathCerts.php"
	      onsubmit="return AIM.submit(this, {'onStart':start, onComplete: loadResult});">
		<div class="row-fluid">
			<label class="span10"><input type="search" name="q" id="q" class="bigSearchField"
			                             placeholder="search death certificates by patient name or emr"
			                             autocomplete="off"></label>
			<button type="submit" class="btn span2">Search &raquo;</button>
		</div>
	</form>
	<div id="searchBox"></div>
	<script>
		document.getElementById('q').focus();
		$(document).on('click', '.resultsPager.dataTables_wrapper a.paginate_button', function (e) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.post('ajax.findDeathCerts.php', {'q': $('#q').val(), 'page': page}, function (s) {
					$('#searchBox').html(s);
				});
			}
		});

		$(document).ready(function () {
			var a = 0;
			// $('a[href^="/pdf.php"]:first').get(0).click();
		});
	</script>
	<?php
	exit;
}


$script_block = <<<EOF
function aTab(o){
    container = $('#deathCert_container');
    $('a.tab').each(function(){
        $(this).removeClass('on');
    });
    if(o===1){
        $('a.tab.certs').addClass('on');
        url = $('a.tab.certs').attr('data-href');
    }else if(o === 2){
        $('a.tab.search').addClass('on');
        url = $('a.tab.search').attr('data-href');
    }
    else if(o === 3){
       $('a.tab.addnew').addClass('on');
       Boxy.load('/death_certificates/new.php',{title:"New Death Certificate", afterHide:function(){location.reload()}});
       return true;
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
            $("*[title]").tooltipster();
        },
    });
    return false;
}
function loading(container){
    container.html('<div align="center"><img src="/img/loading.gif" /> Loading Data ...</div>').show();
}
function loaded(container, respObj){
    container.html(respObj.responseText);
}
function start(){
	\$('#searchBox').html('<img src="/img/loading.gif"/> Please wait ...');
}
function loadResult(s){
    \$('#searchBox').html(s);
	\$("*[title]").tooltipster();
}
$(document).ready(function(){
    aTab(1);
});
EOF;
$extra_link = array('title' => 'Death Certificates', 'link' => '/death_certificates/');
$page = "pages/death/index.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/template.inc.in.php";