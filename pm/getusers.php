<div align="left"><a href="javascript:void(0)" class="subs" onclick="$('#createhospital').hide('fast'); $('#createstaff').slideToggle('fast');">Create User</a>&nbsp;| &nbsp;<a href="javascript:void(0)" class="subs" onclick="$('#createstaff').hide('fast'); $('#createhospital').slideToggle('fast');">Create Hospital</a></div>
<div id="createstaff" style="display:none;"><span class="error" style="display:block;"></span>
	<form action="index.php" id="createuser" method="post" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finish})">
		<table width="100%" border="0" align="left" cellpadding="2" cellspacing="1">
			<tr>
				<td width="20%"><label for="staff_id">First name</label><span class="required-text">*</span></td>
				<td width="80%"><input name="fname" type="text" id="fname"/></td>
			</tr>
			<tr>
				<td><label for="lname">Last name</label><span class="required-text">*</span></td>
				<td><input name="lname" type="text" id="lname"/></td>
			</tr>
			<tr>
				<td><label for="hospital">Hospital</label></td>
				<td><select name="hospital" size="1">
						<option value="">Select your Hospital / Clinic</option>
						<?php require $_SERVER['DOCUMENT_ROOT']. '/Connections/dbconnection.php'; mysql_select_db($database_dbconnection, $dbconnection); $sql = "SELECT * FROM clinic ORDER BY FullName"; $chk=mysql_query($sql); while($row_data = mysql_fetch_array($chk)){ $clinicid[] = $row_data['clinicID']; $clinic[] = $row_data['FullName']; } for ($i=0; $i<count($clinicid); $i++){ echo '<option value="'.$clinicid[$i].'">'.$clinic[$i].'</option>'; } ?>
					</select></td>
			</tr>
			<tr>
				<td><label for="profession">Profession</label><span class="required-text">*</span></td>
				<td><select name="profession"><option value="">-- profession --</option>
				<option value="doctor">Medical Doctor</option>
				<option value="nurse">Nurse</option>
				<option value="technician">Medical Lab Scientist</option>
				<option value="technician">Pharmacist</option>
				<option value="technician">Statistician</option>
				<option value="others">Others</option></select></td>
			</tr>
			<tr>
				<td><label for="specialty">Specialization</label><span class="required-text">*</span></td>
				<td><select name="specialty" size="1">
						<option value="">-- specialization --</option>
						<?php
						require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
						$specs = (new StaffSpecializationDAO())->getSpecializations();
						foreach ($specs as $data) { ?>
							<option value="<?= $data->getId() ?>"<?= (($staffObj->getSpecialization()->getId() == $data->getId()) ? ' selected="selected"' : '') ?>><?= $data->getName() ?></option>
						<?php } ?>
					</select></td>
			</tr>
			<tr>
				<td><label for="phone">Phone number</label><span class="required-text">*</span></td>
				<td><input name="phone" type="text" id="phone"/></td>
			</tr>
			<tr>
				<td><label for="email">Email address</label><span class="required-text">*</span></td>
				<td><input name="email" type="email" id="email"/></td>
			</tr>
			<tr>
				<td><label for="username">Username</label><span class="required-text">*</span></td>
				<td><input name="username" type="text" id="username"/></td>
			</tr>
			<tr>
				<td><label for="password">Password</label><span class="required-text">*</span></td>
				<td><input name="password" type="password" id="password"/></td>
			</tr>
			<tr>
				<td><label for="rpassword">Repeat password</label><span class="required-text">*</span></td>
				<td><input name="rpassword" type="password" id="rpassword"/></td>
			</tr>
			<tr>
				<td colspan="2"><div align="right">
						<button name="newuser" type="submit" style="width:20%">Create User &raquo;</button>
					</div></td>
			</tr>
		</table>
	</form>
</div>
<div id="createhospital" style="display: none; margin-top: 12px;"><span class="output" style="display:block;"></span>
	<form action="index.php" method="post" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done})">
<!-- 		<label for="clinicid">Clinic id</label> -->
<!-- 		<input name="clinicid" type="text" id="clinicid" value="" /> -->
		<label for="clinic">Hospital/Clinic name</label>
		<input name="clinic" type="text" id="clinic" value="" />
		<label for="code">Code</label>
		<input name="code" type="text" id="code" value="" />
		<label for="lga">LGA</label>
		<select name="lga">
		<?php require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.main.php';
		$lgastr = MainConfig::listLGAs();
		$lgas = explode("^", $lgastr);
		for($i=0;$i<count($lgas);$i++){
			$lll=explode("#", $lgas[$i]);
			echo '<option value="'.$lll[0].'">'.$lll[1].'</option>';
		}
		?>
		</select> 
		<label>Address</label>
		<input name="clinic_address" id="clinic_address" type="text" value="" />
		<label>Location: Longitude</label>
		<input name="long" id="long" type="text" value="" />
		<label>Location: Latitude</label>
		<input name="lat" id="long" type="text" value="" />
		<label>Class</label>
		<select name="class">
			<option value="">Select Class</option>
			<option value="Hosp">Hospital</option>
			<option value="PHC">Primary Health Care Center</option>
		</select>
		<div align="right">
			<button name="newclinic" type="submit">Create hospital &raquo;</button>
		</div>
	</form>
</div>
<?php require $_SERVER['DOCUMENT_ROOT'].'/classes/class.staff.php'; $staff = new StaffManager; echo $staff->getUsersList(); ?>
