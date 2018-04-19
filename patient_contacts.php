<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/21/16
 * Time: 10:52 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Country.php';
$countries = (new Country())->all();
$types = getTypeOptions('type', 'contact');
?>
<script type="text/javascript">
	var countries = JSON.parse('<?=json_encode($countries, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>');
	$('input[name="country_id"]').select2({
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
	})
</script>
<section style="width:700px">
	<form id="contactForm">
		<div class="row-fluid">
			<label class="span3">Type <select required name="type"><?php foreach ($types as $type) { ?>
						<option value="<?= $type ?>"><?= ucwords($type) ?></option><?php } ?>
				</select> </label>
			<label class="span4">
				Country
				<input type="hidden" required name="country_id" placeholder="select country">
			</label>
			
			<label class="span3">
				Phone
				<input required type="text" name="phone" placeholder="8021234567" pattern="^[0-9]*$">
			</label>
			<label class="span2 no-label" title="Check if Primary Contact">
				<input type="checkbox" name="primary"> Primary
			</label>
			
		</div>
		
		<div id="displayPanel"></div>
		<p style="margin-bottom: 50px;"></p>
		<div class="btn-block">
			<button class="btn" type="button" onclick="addContact()">Add</button>
			<button class="btn-link" id="_delNumLnk2" type="button">Reset</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Close</button>
		</div>

	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('[name="primary"]').iCheck({checkboxClass: 'icheckbox_square-blue'}).on('ifChanged', function(event){
			$(event.currentTarget).trigger('change');
		});
		var vars = $('#multiple_phone').val();
		var oldPhones = vars !== '' ? JSON.parse(vars) : [];
		var vals = [];
		var displayPanel = $('#displayPanel');
		displayPanel.empty();
		_.each(oldPhones, function (obj, idx) {
			var country = _.find(countries, ['id', obj.country_id]);
			vals.push('('+country.dialing_code+')'+obj.phone + (obj.primary && obj.primary === "on" ? ' *Primary':'')+'');
			displayPanel.append('<span class="action">('+country.dialing_code+')'+obj.phone + (obj.primary && obj.primary === "on" ? ' *Primary':'')+'</span>');
		});
		$('#phonen').val(vals.join(', '));
		
	});
	var addContact = function () {
		var phoneNumber = $('#multiple_phone').val();
		var oldPhoneNumbers = phoneNumber !== '' ? JSON.parse(phoneNumber) : [];
		var thisPhoneNumber = $('#contactForm').serializeObject();
		
		//if primary has been selected before and is checked, don't add but alert
		var invalid = false;
		_.each(oldPhoneNumbers, function (obj, idx) {
			if(obj.primary === "on" && thisPhoneNumber.primary === "on"){
				invalid = true;
			}
		});
		
		if(invalid){
			Boxy.warn("You cannot add multiple numbers as primary");
		} else if($('#contactForm')[0].checkValidity() && $('#contactForm [name="country_id"]').val() !== "" && $('#contactForm [name="type"]').val() !== ""){
			var vars = $('#multiple_phone').val();
			var oldPhones = vars !== '' ? JSON.parse(vars) : [];
			var phones = $('#contactForm').serializeObject();
			oldPhones.push(phones);
			$('#multiple_phone').val(JSON.stringify(oldPhones));
			$('#contactForm')[0].reset();
			$('#contactForm [name="country_id"]').select2("val", "").val("");
			var init = $('#contactForm [name="type"] option:first-child').attr('value');
			$('#contactForm [name="type"]').select2("val", init).val(init);
			$('[name="primary"]').iCheck('update');
			var vals = [];
			var displayPanel = $('#displayPanel');
			displayPanel.empty();
			_.each(oldPhones, function (obj, idx) {
				var country = _.find(countries, ['id', obj.country_id]);
				vals.push('('+country.dialing_code+')'+obj.phone + (obj.primary && obj.primary === "on" ? ' *Primary':'')+'');
				displayPanel.append('<span class="action">('+country.dialing_code+')'+obj.phone + (obj.primary && obj.primary === "on" ? ' *Primary':'')+'</span>');
			});
			$('#phonen').val(vals.join(', '));
		} else {
			Boxy.alert("Form is invalid");
		}
		
	};
</script>
