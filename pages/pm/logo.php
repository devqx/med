<?php
if ($_FILES['logofile']) {
	$path = $_SERVER['DOCUMENT_ROOT'] . "/img/logo/";
	$valid_file_types = array("image/jpeg", "image/png", "image/gif", "bmp");
	$name = $_FILES['logofile']['name'];
	$size = $_FILES['logofile']['size'];
	if (strlen($name)) {
		if (in_array($_FILES['logofile']['type'], $valid_file_types) && $size < (250 * 1024)) {
			$actual_image_name = "logo.jpg";
			$tmp = $_FILES['logofile']['tmp_name'];
			if (move_uploaded_file($tmp, $path . $actual_image_name)) {
				$image = "/img/logo/" . $actual_image_name;
				sleep(2);
				echo "success:" . $image;
			} else {
				echo "error:Upload failed for unknown reason! Make sure the /img/logo dir is writable";
			}
		} else {
			echo "error:Invalid file format... or large file size! (250kb max)";
		}
	} else {
		echo "error:Please select image..!";
	}
	exit;
} else if ($_FILES['idcard']) {
	$path = $_SERVER['DOCUMENT_ROOT'] . "/img/";
	$valid_file_types = array("image/jpeg", "image/png", "image/gif", "bmp");
	$name = $_FILES['idcard']['name'];
	$size = $_FILES['idcard']['size'];
	if (strlen($name)) {
		if (in_array($_FILES['idcard']['type'], $valid_file_types) && $size < (250 * 1024)) {
			$actual_image_name = "ID_Card.png";
			$tmp = $_FILES['idcard']['tmp_name'];
			if (move_uploaded_file($tmp, $path . $actual_image_name)) {
				$image = "/img/" . $actual_image_name;
				sleep(2);
				echo "success:" . $image.rand(0, 100009999);
			} else {
				echo "error:Upload failed for unknown reason! Make sure the /img dir is writable";
			}
		} else {
			echo "error:Invalid file format... or large file size! (250kb max)";
		}
	} else {
		echo "error:Please select image..!";
	}
	exit;
} ?>
<script type="text/javascript">
	function tog(me, area, what) {
		$(area).toggle(function () {
			if ($(me).html().indexOf("Show") !== -1) {
				$('#msg').html("");
				$(me).html("Hide Current " + what);
			} else {
				$(me).html("Show Current " + what);
			}
		});
	}

	function startUpload() {
		$('#logoMsg').html('<span class="alert-box notice"><img src="/img/loading.gif"> Uploading image... The upload progress is not available. Please wait</span>');
	}

	function finishedUpload(s) {
		var str = s.split(':');
		if (str[0] === 'error') {
			$('#logoMsg').html('<span class="alert-box error">' + str[1] + '</span>');
		} else if (str[0] === 'success') {
			$('#logoMsg').html('<span class="alert-box success">Image uploaded!</span>');
			setTimeout(function(){loadLogo();}, 600);
			
			//if(logo){
			//	$('#logo img').attr({'src': str[1]});
			//} else {
			//	$('#idcard_ img').attr({'src': str[1]});
			//}
		}
	}
</script>

<span id="logoMsg"></span>
<div class="well">Select an image file to use as logo; the logo will be used on all printable documents in this application.
	Leave blank to use the default installed by the application or the previously uploaded.
	<span class="fadedText">Dimension should be about 285 x 185 pixels</span>

	<div>
		<div id="logo" style="display: none">
			<img src="/img/logo/logo.jpg?t=<?= rand(0, 600) ?>">
			<div class="border"></div>
		</div>
		<?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/img/logo/logo.jpg")) { ?>
			<button class="action" type="button" onclick="tog(this, '#logo', 'Logo')">Show Current Logo</button>
		<?php } ?>

		<form action="<?= $_SERVER['REQUEST_URI'] ?>" enctype="multipart/form-data" method="post" onSubmit="return AIM.submit(this, {'onStart' : startUpload, 'onComplete' : finishedUpload})">
			<label><em class="fadedText">Click here to Select a new logo file</em>
				<input name="logofile" type="file" id="logofile" style="display: none"/></label>
			<div>
				<button name="submitLogo" class="btn" id="submitLogo" type="submit">Upload Hospital Logo</button>
			</div>
		</form>
	</div>

</div>
<div class="well">Select a backdrop for Patient EMR ID Card
	<div style="display: none" id="idcard_">
		<img style="height: 313px" src="/img/ID_Card.png?t=<?= rand(0, 600) ?>">
		<div class="border"></div>
	</div>
	
	<?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/img/ID_Card.png")) { ?>
		<button style="display: block;" class="action" type="button" onclick="tog(this, '#idcard_', 'ID Card Template')">Show Current ID Card Template</button>
	<?php } ?>
	<form action="<?= $_SERVER['REQUEST_URI'] ?>" enctype="multipart/form-data" method="post" onSubmit="return AIM.submit(this, {'onStart' : startUpload, 'onComplete' : finishedUpload})">
		<label><em class="fadedText">Click here to upload new </em><input type="file" name="idcard" style="display: none"> </label>
		<button name="submitIDCard" type="submit" class="btn">Upload ID Card</button>
	</form>
	
</div>