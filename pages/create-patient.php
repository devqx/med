<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/state_lga.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/socioEconomics.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/lifeStyles.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/clinics.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/get_staff.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/insuranceSchemes.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/KinRelationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SpokenLanguageDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Title.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/CoverageType.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Country.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CompanyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SelfRegisterPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IndustryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$fPat = isset($_GET['x']) ? (new SelfRegisterPatientDAO())->get($_GET['x']) : null;
$existingTitles = [];
$companies = (new CompanyDAO())->all();
$countries = (new Country())->all();
$contactTypes = getTypeOptions('type', 'contact');

$companies = (new CompanyDAO())->all();

$referrals = (new ReferralDAO())->all($page=0, $pageSize=10000);
$referralCompanies = (new ReferralCompanyDAO())->all($page=0, $pageSize=10000);
$kins = (new KinRelationDAO())->all();
$industries = (new IndustryDAO())->all();
$demo = (new PatientDemographDAO());
$contacts_ = [];
if ($fPat && $fPat->getContact() != null) {
	 $contacts = $fPat->getContact();

	foreach ($contacts as $contact) {
		if ($contact->getPrimary()) {
			$_['primary'] = 'on';
		}
		$_['country_id'] = $contact->getNation()->getId();
		$_['phone'] = $contact->getPhone();
		$_['type'] = $contact->getType();
		$contacts_[] = $_;
		unset($_);
	}
}

if ($fPat && $fPat->getTitle() != null && $fPat->getTitle() != ""){
	$existingTitles = [];
	foreach (explode("|", $fPat->getTitle()) as $t_) {
		$titles__['id'] = $t_;
		$titles__['text'] = $t_;
		$existingTitles[] = $titles__;
		unset($titles__);
	}
	$existingTitles = count($existingTitles)==0 ? null : $existingTitles;
}

?>
<script type="text/javascript">

	state = "<?= @$_POST['state']; ?>";
	lga = "<?= @$_POST['lga']?>";
	h_lga = "<?= @$_POST['h_lga']; ?>";
	hid = "<?= @$_POST['h_lga']; ?>";
	states =<?= json_encode($states) ?>;
	clinics =<?= json_encode($clinics) ?>;
	var countries = JSON.parse('<?=json_encode($countries) ?>');
</script>
<div><span id="output"></span></div>

<form method="post" id="createForm" action="<?= $_SERVER['PHP_SELF'] ?>"
      onSubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finished})">
	<h4>New Patient Registration</h4>
	<!-- <label>Create Patient</label> -->
	<div class="row-fluid">
		<label class="sub span8">Patient Folio #
			<input type="text" name="legacy_num" id="legacy_num" title="Automatically generated by the system" disabled value="<?= $demo->_genFolioNumber(null, true) ?>"/></label>
		<label class="sub span4">
			Title
			<select id="_title" multiple data-placeholder="Title/Salutation">
				<option></option>
				<?php
				$TITLES = $fPat ? array($fPat->getTitle()) : [];
				foreach (Title::$titles as $title) { ?>
					<option<?php foreach ($TITLES as $t_) {
						if ($title == $t_) {
							echo ' selected';
						}
					} ?>><?= $title ?></option>
				<?php } ?>
			</select>
		</label>
		<input type="hidden" name="title">
	</div>
	<div class="row-fluid">
		<label class="sub span4">First Name <span class="required-text">*</span>
			<input type="hidden" name="fake_patient" id="fake_patient" value="<?= @$_GET['x'] ?>">
			<input type="text" name="fname" id="fname" value="<?= $fPat && $fPat->getFname() ? $fPat->getFname() : @$_POST['fname'] ?>"/></label>

		<label class="sub span4">Middle Name<input type="text" name="mname" id="mname"></label>
		<label class="sub span4">Last Name<span class="required-text">*</span>
			<input type="text" name="lname" id="lname" value="<?= $fPat && $fPat->getLname() ? $fPat->getLname() : @$_POST['lname'] ?>"/></label>
	</div>

	<div class="row-fluid">
		<label class="sub span6">Date of Birth
			<span class="required-text">*</span><input type="text" name="dob" id="dob" value="<?= $fPat && $fPat->getDateOfBirth() ?  DateTime::createFromFormat('Y-m-d', $fPat->getDateOfBirth())->format('d/m/Y') : @$_POST['dob'] ?>" readonly="readonly" placeholder="Click/touch to select a date of birth"></label>
		<label class="sub span3">Age
			<input type="text" step="1" name="age" id="age" value="" placeholder="Patient age (in years)"></label>
		<label class="sub span3 no-label"><input class="checkbox" type="checkbox" name="estimated_dob">
			Estimated Date</label>
	</div>
	<div class="row-fluid">
		<label class="sub span3">Sex <span class="required-text">*</span><select name="sex" id="sex">
				<option value="--" <?php if ($fPat && $fPat->getSex() == null) { ?> selected<?php } ?>>Select</option>
				<option value="male" <?php if ($fPat && $fPat->getSex() == 'Male') { ?> selected<?php } ?>>Male</option>
				<option value="female" <?php if ($fPat && $fPat->getSex() == 'Female') { ?> selected<?php } ?>>Female</option>
			</select></label>

		<label class="sub span3">Email<input type="email" name="email" id="email" value="<?= $fPat && $fPat->getEmail() ? $fPat->getEmail() : "" ?>"></label>
		<!--<label class="sub span3">Phone Numbers
			<span class="required-text">*</span><input type="text" name="phonen" id="phonen"></label>
		<label class="sub span3">International/Other
			Number<input type="text" name="patIntNumber" id="patIntNumber"></label>-->
		<label class="span6">Phone Numbers
			<span class="pull-right fadedText small">
				<a href="javascript:" id="_addNumLnk">Add Phone Number</a> |
				<a href="javascript:" id="_delNumLnk_">Reset</a>
			</span>
			<input type="text" name="phonen_" readonly id="phonen">
			<input type="hidden" name="phonen" value="<?= htmlentities(json_encode($contacts_)) ?>" id="multiple_phone">
		</label>
	</div>
	<h5>Address of Origin</h5>
	<label>Nationality <select name="nationality">
			<?php
			foreach ((new Country())->all() as $country) {
				echo '<option value="' . $country->id . '"' . ($fPat && $fPat->getCountry() && $fPat->getCountry()->getId() == $country->id ? ' selected="selected"' : '') . '>' . $country->country_name . '</option>';
			}
			?></select></label>
	<label>Occupation <input type="text" name="occupation" value="<?= $fPat && $fPat->getOccupation() ? $fPat->getOccupation(): '' ?>"> </label>
	<label>Work Address <textarea name="work_address"><?= $fPat && $fPat->getWorkAddress()? $fPat->getWorkAddress(): '' ?></textarea> </label>
	

	<label>Industry
		<select name="industry_id" data-placeholder="--Work Industry--">
			<option></option>
			<?php foreach ($industries as $industry){?>
				<option value="<?=$industry->getId()?>"<?= $fPat && $fPat->getIndustry() && $fPat->getIndustry()->getId() == $industry->getId() ? ' selected' : '' ?>><?= $industry->getName()?></option>
			<?php }?>
		</select>
	</label>
   <label>Ethnic Origin
     <input type="text" name="ethnic" placeholder="Enter Ethnic of Origin">
   </label>
    <label>Spoken Language
		<select name="language_id" data-placeholder="Patient's spoken language">
			<option></option>
			<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SpokenLanguageDAO.php';
			$language = (new SpokenLanguageDAO())->all();
			foreach ($language as $l) { ?>
				<option value="<?= $l->getId() ?>"<?= $fPat && $fPat->getSpokenLang() && $fPat->getSpokenLang()->getId() == $l->getId() ? ' selected' : '' ?>><?= $l->getName() ?></option>
			<?php } ?>
		</select> </label>
    <label>Religion
		<select name="religion_id" data-placeholder="Patient's religion">
			<option></option>
			<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReligionDAO.php';
			$religions = (new ReligionDAO())->getReligions();
			foreach ($religions as $r) { ?>
				<option value="<?= $r->getId() ?>"<?= $fPat && $fPat->getReligion() && $fPat->getReligion()->getId() == $r->getId() ? ' selected' : '' ?>><?= $r->getName() ?></option>
			<?php } ?>
		</select> </label>
	<div class="row-fluid">
		<label class="sub span6">State/Regions<select name="state" id="state">
				<option value="0">-- select state of origin --</option>
				<?php
				foreach ($states as $st) {
					echo("<option value='" . $st->getId() . "' " . (($fPat && $fPat->getState() && $fPat->getState()->getId() == $st->getId()) ? "selected" : "") . " >" . $st->getName() . "</option>");
				}
				?></select></label>
		<label class="sub span6">Local Government Area of Origin<select name="lga" id="lga">
				<option value="0">-- select LGA --</option>
				<?php
				$lgas = getLgas($states, $fPat && $fPat->getLga() ? $fPat->getLga()->getId() : @$_POST['lga']);
				foreach ($lgas as $lg) {
					echo("<option value='" . $lg->getId() . "' " . (($fPat && $fPat->getLga() && $fPat->getLga()->getId() == $lg->getId()) ? " selected" : "") . " >" . $lg->getName() . "</option>");
				}
				?></select></label>

	</div>

	<h5>Address of Residence </h5>
	<div class="row-fluid">
		<label class="sub span4">State<select name="state_r" id="state_r">
				<option value="0">-- select state of residence --</option>
				<?php
				foreach ($states as $st) {
					echo("<option value='" . $st->getId() . "' " . (($fPat && $fPat->getResState() && $fPat->getResState()->getId() == $st->getId() || $st->getId() == 25) ? "selected" : "") . " >" . $st->getName() . "</option>");
				}
				?></select></label>
		<label class="sub span4">
			District <input type="hidden" name="district_res_id" placeholder="-- Select district--" value="<?= $fPat && $fPat->getResDistrict() ? $fPat->getResDistrict()->getId() : '' ?>">
		</label>
		<label class="sub span4">Local Government Area<select name="lga_r" id="lga_r">
				<option value="0">-- select LGA --</option>
				<?php
				$lgas = getLgas($states, $fPat && $fPat->getResLga() ? $fPat->getResLga()->getId() : @$_POST['lga_r']);
				foreach ($lgas as $lg) {
					echo("<option value='" . $lg->getId() . "' " . (($fPat && $fPat->getResLga() && $fPat->getResLga()->getId() == $lg->getId()) ? 'selected="selected"' : '') . " >" . $lg->getName() . "</option>");
				}
				?></select></label>
	</div>

	<label class="sub">Address<input type="text" name="address" id="address" value="<?= $fPat && $fPat->getResAddress() ? $fPat->getResAddress() : '' ?>"></label>

	<div class="row-fluid">
		<label class="sub span6">Blood Group<span class="required-text">*</span>
			<select name="bloodg" id="bloodg">
				<?php $groups = PatientDemograph::$bloodGroups;
				for ($r = 0; $r < count($groups); $r++) {
					?>
					<option<?= $fPat && $fPat->getBloodGroup() && $fPat->getBloodGroup() == $groups[$r] ? ' selected="selected"' : '' ?>><?= $groups[$r] ?></option>
				<?php } ?>
			</select></label>

		<label class="sub span6">GenoType/Sickling Status <span class="required-text">*</span>
			<select name="bloodt" id="bloodt">
				<?php
				require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/BloodTypeDAO.php';
				$groups = (new BloodTypeDAO())->all();
				for ($r = 0; $r < count($groups); $r++) {?>
					<option<?= $fPat && $fPat->getGenotype() && $fPat->getGenotype() == $groups[$r]->getName() ? ' selected="selected"' : '' ?>><?= $groups[$r]->getName() ?></option>
				<?php } ?>
			</select></label>
	</div>


	<h5>Next of Kin details</h5>
	<div class="row-fluid">
		<label class="sub span4">First Name<input value="<?=$fPat && $fPat->getNextKinFname() ? $fPat->getNextKinFname():'' ?>" type="text" name="kinfname" id="kinfname"></label>
		<label class="span4">Last Name<input value="<?=$fPat && $fPat->getNextKinLname() ? $fPat->getNextKinLname():'' ?>" type="text" name="kinlname" id="kinlname"></label>
		<label class="span4">Relationship
			<select name="kin_relation_id" data-placeholder="Kin Relationship">
				<option></option><?php foreach ($kins as $k) { ?>
					<option <?= (@$_POST['kin_relation_id'] == $k->getId() ? ' selected="selected"' : ($fPat && $fPat->getRelationship() && $fPat->getRelationship()->getId() == $k->getId() ? ' selected="selected"' : '')) ?> value="<?= $k->getId() ?>"><?= $k->getName() ?></option><?php unset($k); } ?>
			</select></label>
	</div>
	
	<h5>Phone</h5>
	<div class="row-fluid">
		<label class="span4">Type <select name="type"><?php foreach ($contactTypes as $type) { ?>
					<option value="<?= $type ?>"><?= ucwords($type) ?></option><?php } ?>
			</select> </label>
		<label class="span4">
			Country
			<input type="hidden" name="kin_phone_country_id" placeholder="select country">
		</label>

		<label class="span4">
			Phone
			<input type="text" name="kinphone" value="<?= $fPat && $fPat->getNextKinPhone() ? $fPat->getNextKinPhone() : ''?>" placeholder="8021234567" pattern="^[0-9]*$">
		</label>

	</div>
	
	<div>Address
		<label style="display:inline-block;margin-left: 20px;"><input type="checkbox" class="checkbox" data-same-address="true"> The same as Patient's address</label>
		
		<label>
			<input type="text" class="kinaddress" name="kinaddress" id="kinaddress" value="<?= $fPat && $fPat->getNextKinAddress() ? $fPat->getNextKinAddress() : ''?>"></label>
	</div>

	<h4>Current HealthCare Center</h4>
	<label for="hospital_">Facility
		<select name="hospital" size="1" id="hospital_">
			<option value="0">-- select health care center --</option>
			<?php foreach ($clinics as $cl) {
				echo('<option value="' . $cl->getId() . '"' . ((@$_POST['hospital'] == $cl->getId() || $staff->getClinic()->getId() == $cl->getId()) ? " selected" : "") . " >" . $cl->getName() . "</option>");
			} ?>
		</select></label>
	<h3>Billing Profile</h3>
	<label class="sub">Payer<span class="required-text">*</span>
		<select name="insuranceClass" id="insuranceClass" required="required" data-placeholder="Select bills Payer">
			<option></option>
			<?php foreach ($insSchemes as $insScheme) { ?>
				<option data-type="<?= $insScheme->getType() ?>"
				        value="<?= $insScheme->getId() ?>" <?= (@$_POST['insuranceClass'] == $insScheme->getId() ? 'selected="selected"' : "") ?>><?= $insScheme->getName() ?>
					(<?= ucwords($insScheme->getType()) ?>)
				</option>
			<?php } ?></select></label>
	<label class="sub" data-class="ins">Insurance Expiration <span class="required-text">*</span></label>
	<label data-class="ins"><input type="text" name="insuranceExpiration" id="insuranceExpiration" readonly="readonly" value="<?= @$_POST['insuranceExpiration'] ?>" placeholder="Click/touch to select expiration date"></label>
	<div data-class="ins" class="row-fluid">
		<label class="span6">Company
			<select name="company_id" data-placeholder="Select Company">
				<option value=""></option>
				<?php foreach ($companies as $company) { ?>
					<option value="<?= $company->getId() ?>"><?= $company->getName() ?></option>
				<?php } ?>
			</select>
		</label>
		<label class="span3">Policy
			Number<input type="text" name="policy_number"></label>
		<label class="span3">Enrollee #<span class="required-text">*</span>
			<input type="text" name="enrollee_number"></label>
	</div>
	<label data-class="ins" class="row-fluid">
    <span class="span12">Coverage Type <span class="required-text">*</span><select name="coverage_type">
        <?php foreach (CoverageType::$types as $type) { ?>
	        <option><?= $type ?></option>
        <?php } ?>
    </select></span>
	</label>

	<div data-class="ins" class="row-fluid" id="dependentBlock">
		<label class="span10">
			Insurance Dependent<span class="required-text">*</span>
			<input type="hidden" name="dependent_id">
			<input type="text" name="parent_enrollee_id" placeholder="Principal Enrollee's ID or other details" class="hide">
		</label>
		<label class="span2 no-label">
			<input type="checkbox" name="external_parent"> External Patient
		</label>
	</div>

	<label>Referring Company
		<select name="referral_company_id" data-placeholder="Select referring facility where applicable">
			<option></option>
			<?php foreach ($referralCompanies->data as $ref) {/*$ref = new Referral();*/ ?>
				<option value="<?= $ref->getId() ?>"><?= $ref->getName() ?></option>
			<?php } ?>
		</select>
	</label>
	<label>Referring Doctor
		<select name="referral_id" data-placeholder="Select referring person where applicable">
			<option></option>
			<?php foreach ($referrals->data as $ref) {/*$ref = new Referral();*/ ?>
				<option value="<?= $ref->getId() ?>"><?= $ref->getName() ?>
					(<?= $ref->getCompany()->getName() ?>)
				</option>
			<?php } ?>
		</select>
	</label>

	<label class="hide">Care Manager
		<input type="hidden" name="care_manager_id" id="care_manager_id" class="select2">
	</label>


	<div class="btn-block"></div>
	<div class="btn-block">
		<button type="submit" class="btn wide">Continue</button>
	</div>

</form>

<script type="text/javascript">
	var districts = [];
	var districts_res = [];
	$(document).ready(function () {
		$('#_title').select2({width: '100%'}).change(function () {
			var vars = [];
			_.each($('#_title').select2('data'), function (obj, idx) {
				vars.push(obj.text);
			});
			$('[name="title"]').val(vars.join('|'));
		}).select2('data', <?= json_encode($existingTitles)?>);

		$('#age').on('change', function () {
			$newDate = moment().subtract("years", Number($(this).val())).format('DD/MM/YYYY');
			$('#dob').val($newDate);
			$('input[name="estimated_dob"]').prop('checked', true).trigger('change').iCheck('update');
		});

		$('[name="lifestyle[]"]').iCheck({checkboxClass: 'icheckbox_square-blue'}).on('ifChanged', function (event) {
			$(event.currentTarget).trigger('change');
		});
		$('.checkbox').iCheck({checkboxClass: 'icheckbox_square-blue'}).live('ifChanged', function (event) {
			$(event.currentTarget).trigger('change');
		});

		$('[name="external_parent"]').live('change', function (e) {
			if(!e.handled){
			if ($(e.target).is(":checked")) {
				$('[name="dependent_id"]').prev('.select2-container').addClass('hide');
				$('[name="parent_enrollee_id"]').removeClass('hide');
			} else {
				$('[name="dependent_id"]').prev('.select2-container').removeClass('hide');
				$('[name="parent_enrollee_id"]').addClass('hide');
			}
			e.handled=true;
			}
		}).trigger('change');
		
		setInterval(function () {
			$('[name="external_parent"]').trigger('change');
		}, 50);

		setTimeout(function(){$('#state_r').trigger('change');}, 50);
		$('[name="coverage_type"]').select2({width: '100%'}).change(function (e) {
			if ($(this).val() !== 'Principal') {
				$('#dependentBlock').slideDown();
			} else {
				$('#dependentBlock').slideUp();
				$('[name="dependent_id"]').select2('val', '').val('');
			}
		}).trigger('change');

		$('[name="dependent_id"]').select2({
			placeholder: "Search and select the principal patient",
			minimumInputLength: 3,
			width: '100%',
			allowClear: true,
			ajax: {
				url: "/api/search_insurance_principals.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term,
						scheme_id: $('#insuranceClass').val()
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
			}
		});

		$("#care_manager_id").select2({
			placeholder: "Staff Name (Specialization [Staff ID])",
			allowClear: true,
			minimumInputLength: 3,
			width: '100%',
			formatResult: function (data) {
				return data.fullname + " (" + (data.specialization == null ? "" : data.specialization.name) + " [" + data.id + "]) " + data.phone;
			},
			formatSelection: function (data) {
				return data.fullname + " (" + (data.specialization == null ? "" : data.specialization.name) + " [" + data.id + "])";
			},
			formatNoMatches: function (term) {
				return "Sorry no record found for '" + term + "'";
			},
			formatInputTooShort: function (term, minLength) {
				return "Please enter the staff name or ID or phone or specialization";
			},
			ajax: {
				url: '/api/search_staffs.php',
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term, // search term
						limit: 100,
						asArray: true
					};
				},
				results: function (data, page) {
					//console.log(data)
					return {results: data};
				}
			}
		});//End Staff Select2

		$('[data-same-address="true"]').change(function (e) {
			var add = $("#address").val();
			if ($(this).is(':checked') && add !== '') {
				$("#kinaddress").val(add).hide();
			} else if ($(this).is(':checked') && add === '') {
				setTimeout(function () {
					$(e.target).prop('checked', false).trigger('change').iCheck('update');
				}, 50);
				Boxy.warn("Fill the patient address field");
			} else {
				$("#kinaddress").val("").show();
			}
		});

		$('#state_r').change(function (e) {
			$('[name="district_res_id"]').select2('val', '');
			$.post('/api/get_district.php', {
				'type': 'list',
				state_id: e.val || $(e.target).val()
			}, function (data) {
				districts_res = JSON.parse(data);
			});
		}).trigger('change');

		$('[name="district_res_id"]').select2('val', '').select2({
			width: '100%',
			allowClear: true,
			placeholder: "--select district--",
			data: function () {
				return {results: districts_res, text: 'name'};
			},
			formatResult: function (source) {
				return source.name;
			},
			formatSelection: function (source) {
				return source.name;
			},
			initSelection: function (element, callback) {
				var id = $(element).val();
				if (id !== "") {
					$.post('/api/get_district.php', {
						type: 'single',
						id: id
					}, function (data) {
						callback(data);
					}, 'json');
				}
			}
		});

		$('#_addNumLnk, #phonen').click(function (e) {
			if (!e.handled) {
				Boxy.load('/patient_contacts.php');
				e.handled = true;
			}
		});
		$('[id^=_delNumLnk]').live('click', function (e) {
			if (!e.handled) {
				$('#phonen').val('');
				$('[name="phonen_"]').val('');
				$('#displayPanel').empty();
				e.handled = true;
			}
		});

		var countries = JSON.parse('<?=json_encode($countries, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>');
		$('input[name="kin_phone_country_id"]').select2({
			width: '100%',
			allowClear: true,
			data: function () {
				return {results: countries, text: 'country_name'};
			},
			formatResult: function (source) {
				return source.country_name + (source.dialing_code ? ' (' + source.dialing_code + ')' : '');
			},
			formatSelection: function (source) {
				return source.dialing_code;
			}
		});

		loadContacts();
	});

	function loadContacts() {
		var values = $("#multiple_phone").val();
		var phones = values !== '' ? JSON.parse(values) : [];
		$("#multiple_phone").val(JSON.stringify(phones));

		var vales = [];
		_.each(phones, function (obj, indx) {
			var country = _.find(countries, ['id', obj.country_id]);
			vales.push('(' + country.dialing_code + ')' + obj.phone + (obj.primary && obj.primary == 'on' ? '*primary' : ''));
		});
		$('#phonen').val(vales.join(', '));
	}
</script>
