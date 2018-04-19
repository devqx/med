<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Vaccine.php';
//include_once $_SERVER['DOCUMENT_ROOT'] . '/api/getVaccine.php';
$mgr = new Manager();
$vacLabelsMuti = $mgr->vaccineStageLabels;
$vacLabels = array();
$vac = new Vaccine();
if (isset($_POST['vacname'])) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VaccineBooster.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VaccineLevel.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VaccineDAO.php';
	
	$vacBooster = new VaccineBooster();
	$counter = $_POST['counter'];
	$vacLevels = array();
	
	if (isset($_POST['isBooster'])) {
		$vac->setHasBooster(true);
		$interval = $_POST['interval'];
		$scale = $_POST['scale'];
		$recurrence = $_POST['recurrence'];
		
		if ($interval == "" || $interval == 0) {
			echo "error: Booster interval not given";
			exit();
		}
		if ($recurrence == "") {
			echo "error: Booster recurrence time is required";
			exit();
		}
		$vacBooster->setInterval($interval);
		$vacBooster->setIntervalScale($scale);
		$vacBooster->setStartAge($recurrence);
		$vac->setBooster($vacBooster);
	} else {
		$vac->setHasBooster(false);
	}
	$name = $_POST['vacname'];
	$price = parseNumber($_POST['vacprice']);
	$description = $_POST['vacdes'];
	if ($name == "") {
		echo "error: Name of the vaccine cannot be empty";
		exit();
	}
	if ($description == "") {
		echo "error: Vaccine description not given";
		exit();
	}
	if (is_blank($price)) {
		echo "error: Vaccine price not given";
		exit();
	}
	if ($counter >= 1) {
		for ($i = 0; $i < $counter; $i++) {
			$vacLevel = new VaccineLevel();
			$start = explode("|", $vacLabelsMuti[$_POST["startAge_" . ($i + 1)]]);
			$end = explode("|", $vacLabelsMuti[$_POST["endAge_" . ($i + 1)]]);
			$st = array_search($_POST["startAge_" . ($i + 1)], array_keys($vacLabelsMuti));
			$en = array_search($_POST["endAge_" . ($i + 1)], array_keys($vacLabelsMuti));
			$vacLevel->setLevel($_POST["level_" . ($i + 1)]);
			$vacLevel->setStartIndex($st);
			$vacLevel->setEndIndex($en);
			$vacLevel->setStartAge($start[0]);
			$vacLevel->setEndAge($end[0]);
			$vacLevel->setAgeScaleStart($start[1]);
			$vacLevel->setAgeScaleStop($end[1]);
			//        if($en-$st==1){
			//            $vacLevel->setDuration(1);
			//        }else{
			$vacLevel->setDuration($en - $st + 1);
			//        }
			$vacLevels[] = $vacLevel;
		}
		//echo "error: Add at least one or more levels"; exit();
	}
	
	$vac->setName($name);
	$vac->setDescription($description);
	$vac->setLevels($vacLevels);
	
	try {
		$vac_ = (new VaccineDAO())->addVaccine($vac, $price);
	} catch (Exception $e) {
		$vac_ = null;
	}
	
	
	if ($vac_ !== null) {
		exit("success:Vaccine added successfully");
	} else {
		exit("error:Error adding Vaccine");
	}
	
}

foreach ($vacLabelsMuti as $x => $x_value) {
	//Convert the associative array to simple array using only the keys
	$vacLabels[array_search($x, array_keys($vacLabelsMuti))] = $x;
}

?>


<div>
	<h5><a id="escv" href="javascript:void(0)">Business Unit/Service Center</a></h5>

	<div id="existingCenters" style="display: none">
		<span><a id="nscv" href="javascript:void(0)">New Center</a> </span>
		<ul class="list-blocks">
		</ul>
	</div>
	
	<h5><a id="ev" href="javascript:void(0)">Vaccines</a></h5>

	<div id="existingVaccines" style="display: none">
		<ul class="list-blocks">
		</ul>
	</div>

	<h5><a id="ebv" href="javascript:void(0)">Booster Vaccines</a></h5>
	<div id="existingBoosters" style="display: none;">
		<ul class="list-blocks"></ul>
	</div>

	<h5><a id="aebv" href="javascript:void(0)">Add New Booster Vaccine</a></h5>

	<h5>New Vaccine Details</h5>
	<span id="output"></span>

	<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post" id="addVaccine" onsubmit="return AIM.submit(this, {'onStart':start, 'onComplete':done})">
		<div class="row-fluid">
			<label class="span3">Vaccine Name
				<input type="text" name="vacname" id="vacname"/></label>
			<label class="span6">Vaccine Description
				<input type="text" name="vacdes" id="vacdes"/></label>
			<label class="span3">Vaccine Price
				<input type="number" name="vacprice" id="vacprice" value="0"/></label>
		</div>


		<label><input type="checkbox" name="isBooster" value="YES" id="isBooster">
			Has Booster?</label>
		<div class="row-fluid">
			<label class="span2" data="booster" style="display: none">
				<span style="display: block">Booster Interval:</span>
				<input type="number" name="interval" id="interval" placeholder="example: 2"></label>
			<label class="span5 no-label" style="display: none" data="booster">
				<select name="scale" id="scale">
					<option value="DAY" selected="selected">Days</option>
					<option value="WEEK">Weeks</option>
					<option value="MONTH">Months</option>
					<option value="YEAR">Years</option>
				</select> </label>
			<label class="span5" data="booster" style="display: none">Booster
				Recurrence <i>(Zero =
					infinity(&infin;))</i>
				<input type="number" value="0" name="recurrence" id="recurrence"></label>
		</div>

		<div class="row-fluid">
			<label class="span2">Level:
				<input type="text" readonly name="level" id="level" value="1" style=""></label>
			<label class="span5">Start Age:
				<select name="startAge" id="startAge">
				</select></label>
			<label class="span5">End Age:
				<select name="endAge" id="endAge">
					<option value="" selected>select end age</option>
				</select> </label>
		</div>


		<a href="javascript:void(0)" title="Add Level" id="add"><i class="icon-plus-sign"></i>Add
			Level</a>
		<table id="levels" class="data table">
			<thead>
			<tr align="left">
				<th colspan="4" align="center"><strong>Vaccine Administration
						Levels</strong></th>
			</tr>
			<tr>
				<th align="center"><strong>Level</strong></th>
				<th align="center"><strong>Start Age</strong></th>
				<th align="center"><strong>End Age</strong></th>
				<th width="7%" align="center">Action</th>
			</tr>
			</thead>
		</table>
		<!--        <p>&nbsp;</p>-->

		<div id="vacmgt" class="btn-block">
			<button name="btn1" id="btn1" type="submit" class="btn btn-default">Save
			</button>
			<button name="btn1" id="btn2" type="button" class="btn-link" onclick="Boxy.get(this).hide()">
				Close
			</button>
			<input type="hidden" id="counter" name="counter" value="0">
		</div>
		<div></div>
	</form>
	<!--    </fieldset>-->

</div>
<script type="text/javascript">
	$(document).ready(function () {
		$("#escv").click(function () {
			 refreshVSList();
			$("#existingCenters").slideToggle("fast");
		});$("#ev").click(function () {
			$("#existingVaccines").slideToggle("fast");
		});
		$("#ebv").click(function () {
			$("#existingBoosters").slideToggle("fast");
		});
		$("#aebv").live('click', function (e) {
			if (e.handled !== true) {
				Boxy.load("/pages/pm/vaccine/add.boostervaccine.php", {
					title: "Add New Booster Vaccine", afterHide: function () {
						refreshVBList();
					}
				});
				e.handled = true;
			}
		});
		
$("#nscv").live('click', function (e) {
			if (e.handled !== true) {
				Boxy.load("/pages/pm/vaccine/add_service_center.php", {
					title: "Add New Business Unit/Service Center", afterHide: function () {
						refreshVSList();
					}
				});
				e.handled = true;
			}
		});

		labels = <?=json_encode($vacLabels)?>;

		var html = '<option value="" selected="selected">select start age</option>';
		for (var i = 0; i < labels.length; i++) {
			html += '<option value="' + labels[i] + '">' + labels[i] + '</option>';
		}
		setTimeout(function () {
			$('#startAge').html(html).select2("val", "");
		}, 100);

		$("#isBooster").change(function (e) {
			if ($("#isBooster").is(':checked')) {
				$("label[data='booster']").slideDown("fast")
			} else {
				$("label[data='booster']").slideUp("fast");
			}
		});
		$("#existingCenters").live('click', function (s) {
				refreshVSList();
			
		});

		$("#existingVaccines .list-blocks .tag a").live('click', function (e) {
			if (e.handled !== true) {
				Boxy.load('/pages/pm/vaccine/edit.vaccine.php?id=' + $(this).data("id"), {
					afterHide: function () {
						refreshVList();
					}
				});
				e.handled = true;
			}
		});

		$("#existingBoosters .list-blocks .tag a").live('click', function (e) {
			if (e.handled !== true) {
				Boxy.load('/pages/pm/vaccine/edit.boostervaccine.php?id=' + $(this).data("id"), {
					title: "Edit Booster Vaccine", afterHide: function () {
						refreshVBList();
					}
				});
				e.handled = true;
			}
		});
	});
	$('select[name="startAge"]').live('change', function () {
		var html = '<option value="" selected>select end age</option>';
		var index = 0;
		for (var j = 0; j < labels.length; j++) {
			if ($(this).val() === labels[j]) {
				index = j;
				break;
			}
		}
		for (var i = index; i < labels.length; i++) {
			html += '<option value="' + labels[i] + '">' + labels[i] + '</option>';
		}
		$('#' + $(this).attr('id').replace("startAge", "endAge")).html(html);
	});
	$('#add').live('click', function (e) {
		if (!isOk()) {
			e.preventDefault();
			return;
		}
		addLevel($("#levels tr").length - 1);
		var html = '<option value="" selected>select start age</option>';
		var index = 0;
		for (var i = 0; i < labels.length; i++) {
			if ($("#endAge").val() === labels[i]) {
				index = i;
				break;
			}
		}
		$("#level").val(1 + parseInt($("#level").val()));
		$("#startAge").val("");
		$("#endAge").val("");

//        for (var i = index; i < labels.length ; i++) {
		for (var i = index; i < labels.length - 1; i++) {
			html += '<option value="' + labels[i + 1] + '">' + labels[i + 1] + '</option>';
//            html += '<option value="' + labels[i] + '">' + labels[i] + '</option>';
		}
		$('#startAge').html(html);
		$('#endAge').html('<option value="" selected>select end age</option>');

		$("input[name='counter']").val($("#levels tr").length - 2);
	});
	$('a[data*="d"]').live({
		click: function (e) {
			var x = parseInt($(this).attr("data").replace("d", "")) - 1;
			$('#tr' + x + ' td').last().html('<a href="javascript:void(0)" data="d' + x + '">Delete</a>');
			$("#tr" + (x + 1)).remove();
			$("input[name='counter']").val($("#levels tr").length - 2);
			$("#level").val(parseInt($("#level").val()) - 1);
			var html = '<option value="" selected="selected">select start age</option>';
			var index = 0;
			for (var i = 0; i < labels.length; i++) {
				if ($("#endAge_" + x).val() === labels[i]) {
					index = i;
					break;
				}
			}
			for (var i = index; i < labels.length - 1; i++) {
				html += '<option value="' + labels[i + 1] + '">' + labels[i + 1] + '</option>';
			}
			$('#startAge').html(html);
			$('#endAge').html('<option value="" selected="selected">select end age</option>');
			e.preventDefault();
		}
	});

	function validate() {
		var status = true;
		if ($("#vacname").val() === "") {
			$("#vacname").addClass("errorBorder");
			status = false;
		} else {
			status = true;
			$("#vacname").removeClass("errorBorder");
		}
		if ($("#vacdes").val() === "") {
			$("#vacdes").addClass("errorBorder");
			status = false;
		} else {
			status = true;
			$("#vacdes").removeClass("errorBorder");
		}
		/*if ($("#level").val() == 1) {
		 $("#startAge").addClass("errorBorder");
		 $("#endAge").addClass("errorBorder");
		 status = false;
		 } else {
		 status = true;
		 $("#startAge").removeClass("errorBorder");
		 $("#endAge").removeClass("errorBorder");
		 }
		 if (parseInt($("input[name='counter']").val()) < 1) {
		 $('#output').fadeIn().html('<span style="color:#C00">Add at least one level</span>');
		 status = false;
		 }*/
		return status;
	}
	function isOk() {
		var status;
		if ($("#startAge").val() === "") {
			status = false;
			$("#startAge").addClass("errorBorder");
		} else {
			status = true;
			$("#startAge").removeClass("errorBorder");
		}

		if ($("#endAge").val() === "") {
			status = false;
			$("#endAge").addClass("errorBorder");
		} else {
			status = true;
			$("#endAge").removeClass("errorBorder")
		}
		return status;

	}
	function addLevel(x) {
		$("a[data*='d']").remove();
		$("#levels").append('<tr id="tr' + x + '">'
			+ '<td ><strong>' + $("#level").val() + '</strong><input type="hidden" id="level_' + x + '" name="level_' + x + '" value="' + $("#level").val() + '"></td>'
			+ '<td ><strong>' + $("#startAge").val() + '</strong><input type="hidden" id="startAge_' + x + '" name="startAge_' + x + '" value="' + $("#startAge").val() + '"></td>'
			+ '<td ><strong>' + $("#endAge").val() + '</strong><input type="hidden" id="endAge_' + x + '" name="endAge_' + x + '" value="' + $("#endAge").val() + '"></td>'
			+ '<td width="10%" data="action"><i class="icon-remove-sign"></i><a href="javascript:void(0)" data="d' + x + '">Delete</a></td>'
			+ '</tr>');
		setTimeout(function () {
			$("#endAge, #s2id_endAge").removeClass("errorBorder");
			$("#endAge").select2("val", "");
			$("#startAge, #s2id_startAge").removeClass("errorBorder");
			$("#startAge").select2("val", "");
		}, 50);
	}


	function start() {
		$('#output').html('<img src="/img/loading.gif"> Please wait');
		//var valid = ;
		if (!validate()) {
			$(".boxy-content").animate({scrollTop: 0}, "slow");
			return false;
		}
		return true;
	}
	function done(s) {
		$(".boxy-content").animate({scrollTop: 0}, "slow");
		if (s.toLowerCase().indexOf("error") === -1) {
			//every thing is ok, proceed
		}
		ret = s.split(":");
		if (ret[0] === "success") {
			$('#output').fadeIn().html('<span style="color:#00c">' + ret[1] + '...</span>');
			$("#addVaccine")[0].reset();
			$('.boxy-content select').each(function () {
				$(this).removeClass("errorBorder");
				$(this).select2("data", null);
			});
			$('#vacname').val("");
			$('#vacdes').val("");
			$('#vacprice').val("0");
			$('#level').val("1");
			$('#counter').val("0");
			html = '<option value="" selected>select start age</option>';
			for (var i = 0; i < labels.length; i++) {
				html += '<option value="' + labels[i] + '">' + labels[i] + '</option>';
			}

			$('select[id="startAge"]').html(html);
			$('select[id="endAge"]').html('<option value="" selected>select end age</option>');
			$('#levels tr[id*="tr"]').remove();

			refreshVList();
		} else {
			$('#output').fadeIn().html('<span style="color:#c00">' + ret[1] + '</span>');
		}
		setTimeout(function () {
			$("#output").animate({'opacity': '0'}, "slow", function () {
				$(this).html('&nbsp;').css({'opacity': 1}).fadeOut();
			});
		}, 5000);
	}

	function refreshVSList() {
		var container = $("#existingCenters .list-blocks");
		$.ajax({
			url: "/api/get_service_centers.php?type=Vaccine",
			type: "POST",
			dataType: "json",
			beforeSend: function () {
				container.append('<li class="no-border"><img src="/img/loading.gif"> refreshing service center list</li>');
			},
			success: function (data) {
				var html = "";
				for (var i = 0; i < data.length; i++) {
					html = html + '<li class="tag">' + data[i].name + "</li>";
				}
				container.html(html);
			}
		});
	}
	
	function refreshVList() {
		var container = $("#existingVaccines .list-blocks");
		$.ajax({
			url: "/api/getVaccine.php",
			type: "POST",
			dataType: "json",
			beforeSend: function () {
				container.append('<li class="no-border"><img src="/img/loading.gif"> refreshing vaccines list</li>');
			},
			success: function (data) {
				var html = "";
				for (var i = 0; i < data.length; i++) {
					html = html + '<li class="tag">' + data[i].description + "(" + data[i].name + ") <a href='javascript:;' data-id='" + data[i].id + "'><i class='icon-edit'></i></a></li>";
				}
				container.html(html);
			}
		});
	}
	refreshVList();

	function refreshVBList() {
		var container = $("#existingBoosters .list-blocks");
		$.ajax({
			url: "/api/getBoosterVaccine.php",
			type: "POST",
			dataType: "json",
			beforeSend: function () {
				container.append('<li class="no-border"><img src="/img/loading.gif"> refreshing vaccines list</li>');
			},
			success: function (data) {
				var html = "";
				for (var i = 0; i < data.length; i++) {
					html = html + '<li class="tag">' + data[i].vaccine.description + "(" + data[i].vaccine.name + ")</li>";
				}
				container.html(html);
			}
		});
	}
	refreshVBList();
</script>
