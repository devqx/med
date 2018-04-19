<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/functions/func.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
sessionExpired();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.labs.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';

require_once $_SERVER   ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientVaccineBoosterDAO.php';
require_once $_SERVER   ['DOCUMENT_ROOT'] . '/classes/DAOs/VaccineBoosterHistoryDAO.php';
$pdo = (new MyDBConnector())->getPDO();
$patient = new Manager();
$id = escape($_GET['id']);
if (!isset($_SESSION)) {
	session_start();
}

function formatStatus($status)
{
	if ($status == 1) {
		return 'SENT';
	} else if ($status == 0) {
		return 'UNSENT';
	} else {
		return 'UNKNOWN';
	}
}

if (isset($_GET['view']) && $_GET['view'] == "immu-map") { ?>
	<style type="text/css">@import "/style/vaccine.css";
		@import "/style/fixed_table_rc.css";</style>
	<script type="text/javascript">
		$(document).ready(function () {
			$(".vaccine-block").on("click", function (e) {
				if ($(this).hasClass("red") || $(this).hasClass("yellow")) {
					Boxy.load("/immunization/boxy.select_administer_due_vaccines.php?id=<?=$id?>&direct_access_id=" + $(this).data("id"), {title: "Apply Due Vaccines"});
				}
				e.returnValue = false;
				e.preventDefault();
			});
		});
	</script>
	<?php echo $patient->getPatientVaccineMap($id);
	exit;
} else if (isset($_GET['view']) && $_GET['view'] == "reminders") {
	$msgQs = $patient->getPatientVaccineReminder($id);
	$html = '';
	$index = 0;
	if ($msgQs && count($msgQs) > 0) { ?>
		<div class="notify-bar">You have <strong><?= count($msgQs) ?></strong> notifications.</div>
		<table id="notificationTable" class="table table-bordered table-hover">
			<thead>
			<tr>
				<th>Date Created</th>
				<th>Status</th>
				<th>Channel</th>
				<th>Message Content</th>
			</tr>
			</thead>
			<?php while ($index < count($msgQs)) { ?>
				<tr>
					<td><?= date("Y M, d", strtotime($msgQs[$index]->getDateSent())) ?></td>
					<td><?= formatStatus($msgQs[$index]->getMessage_status()) ?></td>
					<td><?= strtoupper($msgQs[$index]->getSource()) ?></td>
					<td><?= $msgQs[$index]->getMessage_content() ?></td>
				</tr>
				<?php $index++;
			} ?>
		</table>
	<?php } else { ?>
		<div class="notify-bar">No Reminders.</div>
	<?php }
	exit;
} else if (isset($_GET['view']) && $_GET['view'] == "boosters") {
	include_once "patient_booster_page.php";
} else if (isset($_GET['view']) && $_GET['view'] == "update-vaccine") {
	echo "This function is not available at the moment";
	exit;
}

$page = "pages/vaccine/vaccine_profile.php";
$script_block = <<<EOF
\$(document).ready(function(){
    showTabs(1);
    \$('img.passport').parent('a').click(function(){
        \$("#camera").show('slow');
    });
    \$('#directUpdateWithoutPay').live('click', function(e){
        Boxy.load('/immunization/boxy.update_without_pay.php?id=$id');
        e.preventDefault();
    });

    \$('a.boosterHistory').live('click', function(e){
      if(!e.handled){
        var bid = $(this).data('id');
        Boxy.load('/immunization/boxy.show_vaccinebooster_history.php?id=' + bid);
        e.preventDefault();
        e.handled = true;
      }
    });

    \$('a.boosterCharge').live('click', function(e){
      if(!e.handled){
        var bid = $(this).data('id');
        Boxy.ask("Charge for this booster vaccine?", ["Yes", "No"], function(answer){
          if(answer=="Yes"){
            $.post("/immunization/ajax.charge_boostervaccine.php", { bv_id: bid }, function(ret){
              var j = JSON.parse(ret);
              console.log(j);
              if(j.status == "success"){showTabs(3);Boxy.info(j.message);}
              else if(j.status == "error"){Boxy.alert(j.message);}
            });
          }
        });
        e.preventDefault();
        e.handled = true;
      }
    });

    \$('a.dueNow').live('click', function(e){
      var bid = $(this).data('id');
      if(!e.handled){
        Boxy.ask("Administer this booster vaccine?", ["Yes", "No"], function(answer){
          if(answer=="Yes"){
            $.post("/immunization/ajax.take_boostervaccine.php", { b: bid }, function(ret){
              var j = JSON.parse(ret);
              Boxy.info(j.message);
              showTabs(3);
            });
          }
        });
        e.preventDefault();
        e.handled = true;
      }
    });

    \$('a[rel="contentPane"]').on('click', function(){
        var myWindow = window.open("/pages/vaccine/print_patient_vaccine_chart.php?id=$id","","width=1600,height=700");
        setTimeout(function(){
            myWindow.print();
            myWindow.close();
        },2000);
    });
});
EOF;

$extra_link = array("title" => "Immunization", "link" => "/immunization");
$extra_style = array("/style/vaccine.css", "/style/vitals.css");
include "../template.inc.in.php";
