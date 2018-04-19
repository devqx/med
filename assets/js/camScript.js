$(document).ready(function () {

	var camera = $('#camera'),
		photos = $('#photos'),
		screen = $('#screen');

	var template = '<a href="/img/profiles/{src}" rel="cam" style="background-image:url(img/profiles/{src})"></a>';
	var imgSrc = '/img/profiles/{src}';
	//var template = '<a href="uploads/original/{src}" rel="cam" '
	//+'style="background-image:url(uploads/thumbs/{src})"></a>';

	/*----------------------------------
	 Setting up the web camera
	 ----------------------------------*/


	webcam.set_swf_url('/assets/webcam/webcam.swf');
	webcam.set_api_url('/upload.php');	// The upload script
	webcam.set_quality(80);				// JPEG Photo Quality
	webcam.set_shutter_sound(false, '/assets/webcam/shutter.mp3');

	// Generating the embed code and adding it to the page:	
	screen.html(
		webcam.get_html(screen.width(), screen.height())
	);


	/*----------------------------------
	 Binding event listeners
	 ----------------------------------*/


	var shootEnabled = false;

	$('#shootButton').click(function () {
		if (!shootEnabled) {
			return false;
		}

		webcam.freeze();
		togglePane();
		return false;
	});

	$('#cancelButton').click(function () {
		webcam.reset();
		togglePane();
		return false;
	});

	$('#uploadButton').click(function () {
		webcam.upload();
		webcam.reset();
		togglePane();
		picURL = '/img/profiles/' + $('input:hidden[name="id"]').val() + '_profile.jpg?t=' + Math.random();
		setTimeout(function(){$("#passport,img.passport").attr('src', picURL);},50);
		camera.hide('slow');
		return false;
	});

	camera.find('.settings').click(function () {
		if (!shootEnabled) {
			return false;
		}

		webcam.configure('camera');
	});

	// Showing and hiding the camera panel:

	var shown = false;
	$('.camTop').click(function () {

		$('.tooltip').fadeOut('fast');
		if (shown) {
			camera.animate({
				bottom: -372
			});
		} else {
			camera.animate({
				bottom: -5
			});
			//},{easing:'easeOutExpo',duration:'slow'});
		}
		shown = !shown;
		camera.hide('slow');
	});

	$('.tooltip').mouseenter(function () {
		$(this).fadeOut('fast');
	});


	/*---------------------- 
	 Callbacks
	 ----------------------*/


	webcam.set_hook('onLoad', function () {
		// When the flash loads, enable
		// the Shoot and settings buttons:
		shootEnabled = true;
	});

	webcam.set_hook('onComplete', function (msg) {

		// This response is returned by upload.php
		// and it holds the name of the image in a
		// JSON object format:

		msg = $.parseJSON(msg);

		if (msg.error) {
			alert(msg.message);
		}
		else {
			// Adding it to the page;
			//photos.prepend(templateReplace(template,{src:msg.filename}));
			//initFancyBox();
		}
	});

	webcam.set_hook('onError', function (e) {
		screen.html(e);
	});


	/*----------------------
	 Helper functions
	 ------------------------*/


	// This function initializes the
	// fancybox lightbox script.

//	function initFancyBox(filename){
//		photos.find('a:visible').fancybox({
//			'transitionIn'	: 'elastic',
//			'transitionOut'	: 'elastic',
//			'overlayColor'	: '#111'
//		});
//	}


	// This function toggles the two
	// .buttonPane divs into visibility:

	function togglePane() {
		var visible = $('#camera .buttonPane:visible:first');
		var hidden = $('#camera .buttonPane:hidden:first');

		visible.fadeOut('fast', function () {
			hidden.removeClass('hidden').show();
		});
	}


	// Helper function for replacing "{KEYWORD}" with
	// the respectful values of an object:

	function templateReplace(template, data) {
		return template.replace(/{([^}]+)}/g, function (match, group) {
			return data[group.toLowerCase()];
		});
	}
});
