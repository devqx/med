<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/15/16
 * Time: 10:14 AM
 */
?>
<form method="post" onsubmit="start(event)">
	<h2>IVF Patient LookUp</h2>
	<div class="input-append">
		<input style="width: 90%" type="text" name="searchfield" id="searchfield" placeholder="Patient EMR ID/IVF #">
		<button type="submit" class="btn remainder">Search &raquo;</button>
	</div>
</form>

<form method="post" id="patient_to_be_enrolled_form" onsubmit="start__1(event)">
	<input type="hidden" name="type" value="ivf">
	<h2>Enroll Patient into IVF Program</h2>
	<!--<label>-->
	<div class="input-append">
		<input style="width: 90%" type="text" name="searchfield1" id="searchfield1" placeholder="Patient EMR ID to enroll">
		<button type="submit" class="btn remainder">Search &raquo;</button>
	</div>
	<!--</label>-->

	<div id="container1"></div>
</form>
<script type="text/javascript">
	start = function (e) {
		if ($('#searchfield').val().length >= 3) {
			$(document).trigger('ajaxSend');
			$('#container1 > div').remove();
			Boxy.load("/boxy.patients-searchresults.php?id=" + $('#searchfield').val() + "&type=ivf", {
				title: "Search for IVF Patients",
				afterShow: function () {
					$(document).trigger('ajaxStop');
				}
			});
		} else {
			Boxy.warn('Enter a valid search query');
		}
		e.preventDefault();
	};

	start__1 = function (e) {
		if($('#searchfield1').val().length >= 3){
			$('#container1').load("ajax.find_patients_to_enroll.php?id="+$('#searchfield1').val());
		}
		e.preventDefault();
		return false;
	}
</script>