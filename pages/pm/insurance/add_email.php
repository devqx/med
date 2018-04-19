<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/19/18
 * Time: 12:13 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Country.php';
$countries = (new Country())->all();
$types = getTypeOptions('type', 'contact');
?>
<section style="width:700px;">
	<form id="emailForm">
		<div class="row-fluid">
			<label class="span12">
				Email Address
				<input required type="email" name="email" placeholder="hello@example.com">
			</label>
		</div>
		<div id="displayPanel"></div>
		<p style="margin-bottom: 50px;"></p>
		<div class="btn-block">
			<button class="btn" type="button" onclick="addContact()">Add</button>
			<button class="btn-link" id="_delEmalLnk2" type="button">Reset</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Close</button>
		</div>
	
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		
		var vars = $('#multiple_email').val();
		var oldEmails = vars !== '' ? JSON.parse(vars) : [];
		var vals = [];
		var displayPanel = $('#displayPanel');
		displayPanel.empty();
		_.each(oldEmails, function (obj, idx) {
			vals.push(obj.email);
			displayPanel.append('<span class="action">('+ obj.email +' )</span>');
		});
		$('#email').val(vals.join(', '));

	});
	
	var addContact = function () {
			if($('#emailForm')[0].checkValidity() && $('#emailForm [name="email"]').val() !== ""){
			var vars = $('#multiple_email').val();
			var oldEmails = vars !== '' ? JSON.parse(vars) : [];
			var emails = $('#emailForm').serializeObject();
			oldEmails.push(emails);
			$('#multiple_email').val(JSON.stringify(oldEmails));
			$('#emailForm')[0].reset();
			
			
			var vals = [];
			var displayPanel = $('#displayPanel');
			displayPanel.empty();
			_.each(oldEmails, function (obj,) {
							vals.push(obj.email);
							displayPanel.append('<span class="action">('+ obj.email + ')</span>');
		});
			
			$('#email_').val(vals.join(', '));
		} else {
			Boxy.alert("Form is invalid");
		}

	};
</script>
