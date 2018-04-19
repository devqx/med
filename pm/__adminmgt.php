<?php require_once($_SERVER['DOCUMENT_ROOT'].'/classes/class.confstaff.php'); $patient = new StaffConfig(); ?><div id="adminmgtmain">
	<div><fieldset><legend>Logo</legend>
		<span id="msg"></span>
		<form action="index.php" enctype="multipart/form-data" method="post" onSubmit="return AIM.submit(this, {'onStart' : startUpload, 'onComplete' : finishedUpload})">
			<label for="logofile">Select a file to use as your logo</label>
			<input name="logofile" type="file" id="logofile" />
			<div align="right">
				<?php if(file_exists("../images/logo/logo.jpg")){?>
				<a href="javascript:void(0)" onclick="new Boxy('<img src=\'/images/logo/logo.jpg\'>',{title:'Current Logo'});">View Current Logo</a> &nbsp;
				<?php }?><button name="submitLogo" id="submitLogo" type="submit" style="width:20%">Continue &raquo;</button>
			</div>
		</form></fieldset>
	</div>
	
	<div class="confstaff"><a href="javascript:void(0)" onClick="$('#confstaff').slideToggle('fast');">Configure Operator Types</a></div>
	<div id="confstaff">
		<fieldset><legend></legend>
		<span class="error1" style="display:block;"></span>
		<form action="index.php" method="post" onSubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finished})">
			<label for="stafftype">Staff Category</label>
			<input name="stafftype" type="text" id="stafftype" value="" />
			<div align="right">
				<button name="addstafftype" id="addstafftype" type="submit" style="width:20%">Add &raquo;</button>
			</div>
		</form>
		</fieldset><br>
	</div>
	<?php $retVal = $patient->getStaffRecord(); if ($retVal != "0"){ ?><div id="updatefee">
		<fieldset><legend>Staff &amp; Charges</legend>
		<span class="error2" style="display:block;"></span>
		<form action="index.php" method="post" onSubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : end})">
			<?php echo $retVal; ?>
			<div align="right">
				<button name="updatebtn" type="submit" style="width:20%">Update &raquo;</button>
			</div>
		</form>
		</fieldset><br>
	</div><?php }?>
	<div>
		<div><a href="javascript:void(0)" onclick="hideAll();Boxy.load('boxy.add.exam-rooms.php',{title:'Configure Examination Rooms'})">Configure Exam Rooms</a></div>
	</div>
</div>
