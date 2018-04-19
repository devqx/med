<?php
if (!isset($_SESSION)) {
	session_start();
}
require_once $_SERVER ['DOCUMENT_ROOT'] . "/functions/utils.php";
require_once $_SERVER ['DOCUMENT_ROOT'] . "/classes/InsuranceScheme.php";
require_once $_SERVER ['DOCUMENT_ROOT'] . "/classes/class.config.main.php";
require_once $_SERVER ['DOCUMENT_ROOT'] . "/classes/DAOs/InsurerDAO.php";
require_once $_SERVER ['DOCUMENT_ROOT'] . "/classes/DAOs/InsuranceSchemeDAO.php";
require_once $_SERVER ['DOCUMENT_ROOT'] . "/classes/DAOs/StaffDirectoryDAO.php";
require_once $_SERVER ['DOCUMENT_ROOT'] . "/classes/DAOs/BadgeDAO.php";
require_once $_SERVER ['DOCUMENT_ROOT'] . "/classes/DAOs/InsuranceTypeDAO.php";
$badges = (new BadgeDAO())->all();
$providers = (new InsurerDAO())->getInsurers();

if ($_POST) {
	// Retrieve Inputs
	$sch = new InsuranceScheme();

	if (!is_blank($_POST ['name'])) {
		$sch->setName($_POST ['name']);
	} else {
		exit("error:Scheme Name required");
	}

	if (!empty($_POST ['provider_id'])) {
		$sch->setInsurer((new InsurerDAO())->getInsurer($_POST ['provider_id'], FALSE));
	} else {
		exit("error:Payer required");
	}
	if (!empty($_POST ['pay_type'])) {
		$sch->setType($_POST ['pay_type']);
	} else {
		exit("error:Pay Type required");
	}
	if (!empty($_POST ['insurance_type_id'])) {
		$sch->setInsuranceType((new InsuranceTypeDAO())->get($_POST ['insurance_type_id']));
	}/*else{
        exit("error:Insurance Type required");
    }*/
	if (!is_blank($_POST['badge_id'])) {
		$sch->setBadge(new Badge($_POST['badge_id']));
	} else {
		$sch->setBadge(null);
	}

	if (!is_blank($_POST['pay_type'])) {
		$sch->setType($_POST['pay_type']);
	} else {
		exit("error:Pay type is missing");
	}

	if (!is_blank($_POST['reg_company'])) {
		$sch->setCompanyRegCost(parseNumber($_POST['reg_company']));
	} else {
		exit("error:Company Registration charge is missing");
	}

	if (!is_blank($_POST['reg_individual'])) {
		$sch->setIndividualRegCost(parseNumber($_POST['reg_individual']));
	} else {
		exit("error:Individual Registration charge is missing");
	}
	if (!is_blank($_POST['credit_limit'])) {
		$sch->setCreditLimit(parseNumber($_POST['credit_limit']));
	} else {
		exit("error:Credit Limit is required");
	}
	
	$emails = array_filter(explode(",", $_POST['email']));
	if (count($emails) == 0) {
		exit('error:At least one email address is required ');
	}
	
	$sch->setEmail($emails);
	
	if (!is_blank($_POST['phone'])) {
		$sch->setPhone($_POST['phone']);
	} else {
		exit("error:Contact Phone is required");
	}
	if (!is_blank($_POST['logo_url'])) {
		$sch->setLogoUrl($_POST['logo_url']);
	} else {
		$sch->setLogoUrl((isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST']. "/img/logo/logo.jpg");
	}
	if (!is_blank($_POST['clinical_services_rate'])) {
		$sch->setClinicalServicesRate(parseNumber($_POST['clinical_services_rate']));
	} else {
		$sch->setClinicalServicesRate(0);
	}
	if(!is_blank($_POST['enrolees_max'])){
		$sch->setEnroleesMax(parseNumber($_POST['enrolees_max']));
	} else {
		exit("error:Enrollees allowable maximum is required");
	}
	$sch->setIsReference(isset($_POST['is_reference']));
	$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], FALSE);

	$sch->setHospital($staff->getClinic());

	if (MainConfig::$erpEnabled) {
		if (!is_blank($_POST['receivable_account'])) {
			$sch->setReceivablesAccount($_POST['receivable_account']);
		} else {
			//exit("error:Receivable account is missing");
		}
		if (!is_blank($_POST['discount_account'])) {
			$sch->setDiscountAccount($_POST['discount_account']);
		} else {
			//exit("error:Discount account is missing");
		}
		if (!is_blank($_POST['partner'])) {
			$sch->setPartner($_POST['partner']);
		} else {
			//exit("error:Partner is missing");
		}
	} else {
		$sch->setReceivablesAccount(null);
		$sch->setDiscountAccount(null);
		$sch->setPartner(null);
	}

	$ret = (new InsuranceSchemeDAO())->addInsuranceScheme($sch);

	if ($ret != null) {
		exit("success:Insurance Scheme Created successfully");
	}
	exit("error:Failed to add scheme");
}
?>

<div id="addScheme" style="min-width: 800px; max-width:1000px">
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done});">
		<label><span id="msg"></span></label>
		<label>Scheme Name:<input name="name" type="text"></label>
		<label>Payer:<select name="provider_id">
				<?php
				foreach ($providers as $vals) {
					echo '<option value="' . $vals->getId() . '">' . $vals->getName() . '</option>';
				} ?>
			</select>
		</label>
		<label>
			Badge <span class="pull-right" id="badge_icon"></span><select name="badge_id" placeholder=" - Select badge -">
				<option></option>
				<?php foreach ($badges as $badge) { ?>
					<option value="<?= $badge->getId() ?>" data-badge="<?= $badge->getIcon() ?>"><?= $badge->getName() ?></option>
				<?php }
				?>
			</select>
		</label>
		<label>Billing Type
			<select name="pay_type">
				<option value="self">SELF PAY</option>
				<option value="insurance">INSURANCE</option>
			</select>
		</label>
		<label>Insurance Type <select name="insurance_type_id">
				<?php foreach ((new InsuranceTypeDAO())->all() as $type) { ?>
					<option value="<?= $type->getId() ?>"><?= $type->getName() ?></option><?php } ?>
			</select></label>
		<label class="fees">Individual Registration Fee <span class="pull-right"><i class="icon-info-sign"></i> Help</span>
			<input type="number" name="reg_individual" min="0" value="0"> </label>
		<label class="fees">Company Registration Fee <span class="pull-right"><i class="icon-info-sign"></i> Help</span>
			<input type="number" name="reg_company" min="0" value="0"> </label>

		<label>Credit Limit
			<input name="credit_limit" type="number" min="0" required="required" value="<?= number_format(MainConfig::$defaultSchemeCreditLimit, 2, '.', '') ?>"></label>
		<div class="row-fluid">
			<label class="span6">Contact Phone <input type="text" name="phone" required value=""> </label>
			<label class="span6">Contact Email
				<span class="pull-right fadedText small">
				<a href="javascript:" id="_delEmailLnk_">Reset</a>
			</span>
				<input type="text" name="email" readonly required id="email_">
				<input type="hidden" name="email_" value="" id="multiple_email">

			</label>
		</div>
		<label>Logo: (paste URL of a hosted image resource) <input type="url" name="logo_url"> </label>
		<label>Clinical Service Charge Rate (%)<input type="number" min="0" max="100" name="clinical_services_rate">
		</label>
		<label>Maximum # of enrollees allowable <input type="number" data-decimals="0" required name="enrolees_max" value="1000"> </label>
		<label><input type="checkbox" name="is_reference"> Check this box if you want this scheme to serve as a reference scheme (Prices can be copied from this scheme to other non-reference schemes) </label>

		<?php if (MainConfig::$erpEnabled) { ?>
			<label>Receivables Account
				<select name="receivable_account">

				</select>
			</label>
			<label>Discount Account
				<select name="discount_account">

				</select>
			</label>
			<label>Partner
				<select name="partner">

				</select>
			</label><?php } ?>

		<div class="btn-block">
			<button type="submit" class="btn">Create Scheme</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hide()">cancel</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	$('select[name="pay_type"]').live('change', function (e) {
		e.preventDefault();
	}).trigger('change');
	function start() {
		$('#msg').html('<img src="/img/loading.gif"/> <em>Please Wait ...</em>');
	}
	function done(s) {
		var status_ = s.split(":");
		if (status_[0] === 'success') {
			showTabs(2);
			Boxy.info(status_[1], function () {
				Boxy.get($(".close")).hideAndUnload();
			});
			$('#msg').html('<span class="alert alert-info">' + +'</span>');

		} else {
			$('#msg').html('<span class="alert alert-error">' + status_[1] + '</span>');
		}
	}
	
	$('#email_').click(function (e) {
		if (!e.handled) {
			Boxy.load('/pages/pm/insurance/add_email.php');
			e.handled = true;
		}
	});
	
	$('[id^=_delEmailLnk_]').live('click', function (e) {
		if (!e.handled) {
			$('#email_').val('');
			$('[name="email_"]').val('');
			$('#displayPanel').empty();
			e.handled = true;
		}
	});
</script>