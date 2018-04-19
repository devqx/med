<?php
include_once "protect.php";

ini_set('display_errors', 'Off');
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$useragent = $_SERVER['HTTP_USER_AGENT'];
$mobile = (preg_match('/android|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(ad|hone|od)|iris|kindle|lge |maemo|meego.+mobile|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4)));
$version = date('Ymd');
?>
	<!DOCTYPE html>
	<html lang="en" moznomarginboxes mozdisallowselectionprint>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<title>MedicPlus <?= (isset($title) ? ' - ' . $title : '') ?> </title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta property="og:image" content="/img/icon3.png"/>
		<meta name="description" content="">
		<meta name="keywords" content="">
		<meta name="author" content="">
		<meta name="format-detection" content="telephone=no">
		<!-- Favicon -->
		<link rel="apple-touch-icon" sizes="120x120" href="/favicon/apple-touch-icon.png?v=<?= $version ?>">
		<link rel="icon" type="image/png" href="/favicon/favicon-32x32.png?v=<?= $version ?>" sizes="32x32">
		<link rel="icon" type="image/png" href="/favicon/favicon-16x16.png?v=<?= $version ?>" sizes="16x16">
		<link rel="manifest" href="/favicon/manifest.json?v=<?= $version ?>">
		<link rel="mask-icon" href="/favicon/safari-pinned-tab.svg?v=<?= $version ?>" color="#135a70">
		<link rel="shortcut icon" href="/favicon/favicon.ico?v=<?= $version ?>">
		<meta name="apple-mobile-web-app-title" content="MedicPlus App">
		<meta name="application-name" content="MedicPlus App">
		<meta name="theme-color" content="#135a70">
		<!-- JS -->
		<script src="/js/jquery-2.1.1.min.js?v=<?= $version ?>"></script>
		<!--<script src="/js/jquery-3.2.0.min.js?v=<?= $version ?>"></script>-->

		<script src="/js/jquery-migrate-1.2.1.min.js?v=<?= $version ?>"></script>
		<script src="/js/webtoolkit.aim.js?v=<?= $version ?>" type="text/javascript"></script>
		<script src="/js/jquery.PrintArea.js?v=<?= $version ?>" type="text/javascript"></script>
		<script src="/js/jquery.jqprint-0.3.js?v=<?= $version ?>"></script>
		<script src="/js/jquery.tablescroll.js?v=<?= $version ?>" type="text/javascript"></script>
		<script src="/js/jquery-scrolltofixed-min.js?v=<?= $version ?>"></script>
		<script src="/assets/boxy/js/jquery.boxy.js?v=<?= $version ?>"></script>
		<!--a modified boxy just for the documents viewing-->
		<script src="/assets/doc_viewer/js/jquery.viewer.js?v=<?= $version ?>"></script>
		<script src="/js/bootstrap.js?v=<?= $version ?>"></script>
		<!--<script src="/assets/bootstrap3-editable/js/bootstrap.min.js"></script>-->
		<link href="/assets/bootstrap3-editable/css/bootstrap-editable.css?v=<?= $version ?>" rel="stylesheet">
		<script src="/assets/bootstrap3-editable/js/bootstrap-editable.js?v=<?= $version ?>"></script>
		<link href="/assets/x-editable/inputs-ext/custom/custom.css?v=<?= $version ?>" rel="stylesheet">
		<script src="/assets/x-editable/inputs-ext/custom/custom.js?v=<?= $version ?>"></script>
		<script type="text/javascript" src="/js/jquery.shorten.1.0.js?v=<?= $version ?>"></script>
		<script src="/assets/jquery-ui/js/jquery-ui.custom.js?v=<?= $version ?>" type="text/javascript"></script>
		<script src="/assets/jquery-notify/src/jquery.notify.js?v=<?= $version ?>" type="text/javascript"></script>
		<script src="/js/notify.min.js?v=<?= $version ?>" type="text/javascript"></script>
		<link rel="stylesheet" href="/assets/jquery-notify/ui.notify.css?v=<?= $version ?>">

		<script src="/assets/moment/moment.min.js?v=<?= $version ?>"></script>
		<script src="/assets/moment/moment-with-locales.min.js?v=<?= $version ?>"></script>

		<!-- Stylesheets -->
		<link rel="stylesheet" href="/style/font-awesome.css?v=<?= $version ?>">
		<script src="/assets/blockUI/jquery.blockUI.js?v=<?= $version ?>"></script>
		<!-- Stylesheets -->
		<link rel="stylesheet" href="/style/bootstrap.css?v=<?= $version ?>">
		<link rel="stylesheet" href="/style/style.css?v=<?= $version ?>">

		<!-- Colors - Orange, Purple, Light Blue (lblue), Red, Green and Blue -->
		<link rel="stylesheet" href="/style/blue.css?v=<?= $version ?>">

		<link href="/assets/font-awesome/css/font-awesome.min.css?v=<?= $version ?>" rel="stylesheet" type="text/css"/>
		<link href="/assets/dentist/flaticon.css?v=<?= $version ?>" rel="stylesheet" type="text/css"/>
		<link href="/assets/antenatal/flaticon.css?v=<?= $version ?>" rel="stylesheet" type="text/css"/>
		<link href="/assets/ribbon/flaticon.css?v=<?= $version ?>" rel="stylesheet" type="text/css"/>
		<link href="/assets/physiotherapy/flaticon.css?v=<?= $version ?>" rel="stylesheet" type="text/css"/>
		<link href="/assets/sperm/flaticon.css?v=<?= $version ?>" rel="stylesheet" type="text/css"/>
		<link href="/assets/exam/flaticon.css?v=<?= $version ?>" rel="stylesheet" type="text/css"/>
		<link href="/assets/allergen/flaticon.css?v=<?= $version ?>" rel="stylesheet" type="text/css"/>
		<!-- growl ui -->
		<link href="/assets/blockUI/growl.ui.css?v=<?= $version ?>" rel="stylesheet" type="text/css"/>

		<link rel="stylesheet" href="/style/bootstrap-responsive.css?v=<?= $version ?>">
		<link rel="stylesheet" href="/assets/dataTables/media/css/jquery.dataTables.min.css?v=<?= $version ?>">
		<script type="text/javascript"
		        src="/assets/dataTables/media/js/jquery.dataTables.min.js?v=<?= $version ?>"></script>
		<link rel="stylesheet" href="/style/def.css?v=<?= $version ?>">
		<link rel="stylesheet" href="/assets/boxy/css/boxy.css?v=<?= $version ?>">
		<link rel="stylesheet" href="/assets/doc_viewer/css/viewer.css?v=<?= $version ?>">
		<link rel="stylesheet" href="/style/jquery.tablescroll.css?v=<?= $version ?>">
		<!-- these files have to be loaded exactly twice for the drop downs to work
				 very wierd: first load -->
		<script src="/js/bootstrap.min.js?v=<?= $version ?>"></script>
		<script src="/assets/droptabs/jquery.droptabs.js?v=<?= $version ?>"></script>
		<!-- these files have to be loaded exactly twice for the drop downs to work
				 very wierd: second load -->
		<script src="/js/bootstrap.min.js?v=<?= $version ?>"></script>
		<script src="/assets/droptabs/jquery.droptabs.js?v=<?= $version ?>"></script>

		<script type="text/javascript" src="/assets/select2_2/select2.min.js?v=<?= $version ?>"></script>
		<link rel="stylesheet" href="/assets/select2_2/select2.css?v=<?= $version ?>">
		<link rel="stylesheet" href="/assets/datetimepicker-master/jquery.datetimepicker.css?v=<?= $version ?>">
		<script type="text/javascript" src="/assets/datetimepicker-master/jquery.datetimepicker.js?v=<?= $version ?>"></script>

		<script type="text/javascript" src="/assets/tooltipster/js/jquery.tooltipster.js?v=<?= $version ?>"></script>
		<link rel="stylesheet" href="/assets/tooltipster/css/tooltipster.css?v=<?= $version ?>">
		<link rel="stylesheet" href="/assets/tooltipster/css/themes/tooltipster-punk.css?v=<?= $version ?>">
		<link rel="stylesheet" href="/assets/tooltipster/css/themes/tooltipster-shadow.css?v=<?= $version ?>">

		<!--<script type="text/javascript" src="/assets/tooltipster-scrollableTip/tooltipster-scrollableTip.min.js"></script>-->

		<script src="/assets/ckeditor/ckeditor.js?v=<?= $version ?>"></script>
		<script src="/assets/ckeditor/adapters/jquery.js?v=<?= $version ?>"></script>
		<!--current wysiwyg editor-->
		<link rel="stylesheet" href="/assets/summernote/summernote.css?v=<?= $version ?>">
		<script src="/assets/summernote/summernote.min.js?v=<?= $version ?>"></script>

		<script src="/assets/jquery-number-master/jquery.number.js?v=<?= $version ?>"></script>

		<link href='/assets/fullcalendar/fullcalendar.css?v=<?= $version ?>' rel='stylesheet'/>
		<link href='/assets/fullcalendar/fullcalendar.print.css?v=<?= $version ?>' rel='stylesheet' media='print'/>
		<script src='/assets/jquery-ui/js/jquery-ui.custom.min.js?v=<?= $version ?>'></script>
		<script src='/assets/fullcalendar/fullcalendar.min.js?v=<?= $version ?>'></script>
		<script src='/assets/fullcalendar/gcal.js'></script>

		<link href="/notify.css?v=<?= $version ?>" rel="stylesheet">
		<script type="text/javascript">
			SUMMERNOTE_CONFIG = {
				height: 200,
				toolbar: [
					['style', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript']],
					['para', ['ul', 'ol', 'paragraph']],
					['fontname', ['fontname']],
					['fontsize', ['fontsize']],
					['table', ['table']],
					['color', ['color']],
					['insert', ['picture']],
					['view', ['fullscreen', 'codeview']]
				],
				airPopover: []
			};SUMMERNOTE_MINI_CONFIG = {
				height: 250,
				toolbar: [
					['style', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript']],
					['para', ['ul', 'ol', 'paragraph']],
					['fontsize', ['fontsize']],
					['insert', ['picture']],
					['view', ['fullscreen', 'codeview']]
				],
				airPopover: []
			};
		</script>
		<script type="text/javascript" src="/js/functions.js?v=<?= $version ?>"></script>

		<link rel="stylesheet" href="/style/formToWizard.css?v=<?= $version ?>"/>
		<script src="/js/jquery.formToWizard.js?v=<?= $version ?>"></script>
		<link rel="stylesheet" type="text/css" href="/assets/alert/alert.css?v=<?= $version ?>"/>
		<link rel="stylesheet" type="text/css" href="/font/proxima-nova.css?v=<?= $version ?>"/>

		<script src="/assets/vex-2.2.1/js/vex.combined.min.js?v=<?= $version ?>"></script>
		<script>vex.defaultOptions.className = 'vex-theme-default';</script>
		<link rel="stylesheet" type="text/css" href="/assets/vex-2.2.1/css/vex.css?v=<?= $version ?>">
		<link rel="stylesheet" type="text/css" href="/assets/vex-2.2.1/css/vex-theme-default.css?v=<?= $version ?>">
		<!--<script src="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>-->
		<!--<script src="/assets/droptabs/jquery.droptabs.js"></script>-->
		<link href="/assets/icheck-1.x/skins/all.css" rel="stylesheet">
		<link href="/assets/icheck-1.x/skins/flat/blue.css" rel="stylesheet">
		<script src="/assets/icheck-1.x/icheck.js"></script>
		<script src="/assets/jquery.querystring.js"></script>

		<link href="/style/patient.profile.css" rel="stylesheet">
		<link href="/style/patient.procedure.css" rel="stylesheet">
		<link href="/style/easy.tabs.custom.css" rel="stylesheet">
		<script src="/js/jquery.easytabs.min.js"></script>
		<script type="text/javascript" src="/js/JsBarcode.all.min.js"></script>
		<script type="text/javascript" src="/js/functions.js"></script>
		<script type="text/javascript" src="/js/jQuery-csv.js"></script>
		<script type="text/javascript" src="/js/csvparse.js"></script>
		<script type="text/javascript" src="/js/csvsup.js"></script>

		<?php
		if (isset($extra_style)) {?>
			<?php for ($i = 0; $i < count($extra_style); $i++) {?>
				<link rel="stylesheet" href="<?= $extra_style[$i]; ?>?v=<?= $version ?>">
				<?php }?>
		<?php } ?>
		<?php if (isset($extra_script)) {
			for ($i = 0; $i < count($extra_script); $i++) {?>
				<script type="text/javascript" src="<?php echo $extra_script[$i]; ?>?v=<?= $version ?>"></script>
				<?php
			}
		} ?>
		<!-- HTML5 Support for IE -->
		<!--[if lt IE 9]>
		<!--<script src="/js/html5shim.js"></script>-->
		<![endif]-->
		<!--<script src="/assets/jquery-idletimer/dist/idle-timer.js" type="text/javascript"></script>
		<script src="/assets/jquery-lockscreen/dist/lockscreen.output.js" type="text/javascript"></script>
		<script src="/assets/jquery-lockscreen/libs/jquery/jquery.cookie.js" type="text/javascript"></script>
		<link rel="stylesheet" href="/assets/jquery-lockscreen/dist/style.css" media="all">-->
		<script type="text/javascript">
			$.fn.datetimepicker.defaults['yearStart'] = 1900;
			$.fn.datetimepicker.defaults['closeOnDateSelect'] = true;
			$.fn.datetimepicker.defaults['step'] = 5; //minutes
			$.fn.datetimepicker.defaults['scrollInput'] = false;
			$.fn.datetimepicker.defaults['allowBlank'] = true;
			$.fn.dataTableExt.sErrMode = 'throw';
			$.fn.tooltipster('setDefaults', {
				// theme:'tooltipster-punk',
				contentAsHTML: true,
				multiple: false,
				debug: false,
				animation: 'fade',
				interactive: true,
				maxWidth: 450,
				//timer: 2000
			});

			$.notify2.defaults({ autoHide: true, autoHideDelay: 10000, style:"default" });
			$.notify2.addStyle('default', {
				html: "<div><span data-notify-text/></div>",
				classes: {
					base: {
						"white-space": "nowrap",
						"padding": "10px 100px",
						'background': '#1586d5',
						'box-shadow': '0 0 6px #ccc',
						'color': '#fff !important',
						'text-align': 'center',
						'border-radius': '2px !important',
						'margin':'10px'
					},
					error: {
						'background': '#d51905'
					},
					warn: {
						'background': '#f3c727',
					}
				}
			});

			$(document).ready(function () {
				$(".boxy").boxy();
				$('input').attr('autocomplete', 'off');
			});
			$(document).ajaxSend(function (event, jqxhr, settings) {
				var prefix = ["/counter.php", "/api/tooltip_data.php", "/api/medical_report_items.php", "/api/search_patients.php", "/api/get_diagnoses.php", "/api/get_procedures.php", "/api/get_drugs.php", "/api/get_labs.php", "/api/get_scans.php", "/api/get_drug_generics.php", "/api/search_queue_patients.php", "/api/search_appointments_patients.php","/api/get_patient_slim_insurance.php", "/api/get_insurance_item_cost.php"];

				if (settings === undefined || !_.includes(prefix, settings.url.split("?")[0]) ){
					// || (settings.url.slice(0, prefix.length) != prefix && settings.url.slice(0, prefix2.length) != prefix2 && settings.url.slice(0, prefix3.length) != prefix3)) {
					$.blockUI({
						message: '<div class="ball"></div>',
						css: {
							borderWidth: '0',
							backgroundColor: 'transparent'
						}
					});
				}
			}).ajaxStop(function () {
				$.unblockUI();
				$("*[title]").tooltipster();
				//$('#content_loader').hide();
				$(".profile[data-pid]").tooltipster({
					content: 'Loading...',
					updateAnimation: false,
					functionBefore: function (origin, continueTooltip) {
						continueTooltip();
						if (origin.data('ajax') !== 'cached') {
							$.getJSON('/api/tooltip_data.php?pid=' + origin.attr('data-pid'),
								function (feed) {
									var content = '';
									var legacy = (feed.legacyPatientId !== "" ? feed.legacyPatientId + '<br>' : '');
									var phonenumber = feed.phoneNumber !== "" ? '<div>'+feed.phoneNumber+'</div>' : '';
									if (!feed) {
										content = 'Error loading data';
										origin.tooltipster('content', content);
									} else {
										content = $('<table border=0><tr><td><img align="left" width="50" style="" src="' + feed.passportPath + '"></td><td><div class="content">' + legacy + '<strong>' + feed.fullname + '</strong><br><div>EMR #:&nbsp;<a href="/patient_profile.php?id='+feed.patientId+'" target="_blank">' + feed.patientId + '</a></div><div>' + feed.age + '/' + ucwords(feed.sex) + '</div>'+phonenumber + '<div>Coverage: ' + feed.scheme + '</div></div></td></tr></table>');
										origin
											.tooltipster('content', content)
											.data('ajax', 'cached');
									}
								});

							origin.data('ajax', 'cached');
						}
					}
				});

				$("a[data-item]").tooltipster({
					content: '<div class="ball small"></div>',
					updateAnimation: false,
					theme: 'tooltipster-shadow',
					position: 'right',
					minWidth: 720,
					maxWidth: null,
					functionBefore: function (origin, continueTooltip) {
						continueTooltip();
						if (origin.data('ajax') !== 'cached') {
							$.get('/api/medical_report_items.php', {
								item: origin.attr('data-item'),
								type: origin.attr('data-type'),
								//patientId: origin.attr('data-patientId'),
								id : origin.attr('data-id')
							},
								function (feed) {
									var content = '';

									if (!feed) {
										content = 'Error loading data';
										origin.tooltipster('content', content);
									} else {
										content = feed;
										origin.tooltipster('content', content).data('ajax', 'cached');
									}
								});

							origin.data('ajax', 'cached');
						}
					}
				});

				$('.price-input, td.amount').each(function(){
					$(this).number(true, 2);
				});
				$('input[type="number"]').each(function(){
					$(this).attr('type','text').number(true, 2);
				});

				$(':checkbox').iCheck({checkboxClass: 'icheckbox_square-blue'}).on('ifChanged', function (event) {
					$(event.currentTarget).trigger('change');
				}).on('ifClicked', function (event) {
					$(event.currentTarget).trigger('click');
				});

				$(':radio').iCheck({radioClass: 'iradio_square-blue'}).on('ifChanged', function (event) {
					$(event.currentTarget).trigger('change');
				}).on('ifClicked', function (event) {
					$(event.currentTarget).trigger('click');
				});
			}).ajaxError(function () {
				Boxy.alert("AJAX Error occurred");
			});

			$(function () {
				$(".pdf_viewer").live('click', function (e) {
					Viewer.load($(this).attr("href"));
					e.preventDefault();
					return false;
				});

				$('a[href^="/pdf.php"]').bind('click', function(){
					$.blockUI({
						message: '<div class="ball"></div><br><h4>Generating PDF. Please wait</h4>',
						css: {
							borderWidth: '0',
							backgroundColor: 'transparent',
						}
					});
				});

				$('.select2-container + .select2-offscreen').live('change', function(){
					var $this = $(this).prev('.select2-container');
					var data = ($(this).select2('data'));
					//console.log($this.tooltip('instance'));
					if(data && !_.isArray(data)){
						$this.attr('title', data.text || data.name).tooltipster();
					} else if($this.tooltip) {
						//$this.tooltipster('destroy');
					}
				});

				$("*[title]").tooltipster();

				$("#notify").notify({speed: 500, expires: false});

				uiArrangeBlocks();
				preventDoubleClicks();
				//don't call this function on page load immediately
				setTimeout(refreshMessageCounters, 1500);
			});

			//$(function () {
				//var timeout = 1200000;
				//$(document).bind("idle.idleTimer", function () {
					//if ($('.lock-screen').length == 0) {
					//	$('body').lockScreen({
					//		timeout: 2000,
					//		unlockMe: "Unlock!",
					//		name: "<?= @$_SESSION['username'] ?>",
					//		app: "Medicplus",
					//		avatar: "/img/profiles/male.jpg",
					//		logo: "/img/logo/logo.jpg",
					//		unlockBtnClass: "btn",
					//		unlock: function () {
					//		}
					//	});
					//}
				//});
				//$(document).bind("active.idleTimer", function () {
				//	//
				//});
				//$.idleTimer(timeout);
			//});

			window.onerror = function (error, url, line) {
				Boxy.alert("A JavaScript Error has occurred.<br>Details: <code>" + error + "</code><br>URL:<code>" + url + ":" + line + "</code>");
				setTimeout(function () {
					$.unblockUI();
				}, 800);
			};

			<?php
			if (isset($script_block)) {
				echo $script_block;
			}
			?>
		</script>
		<link rel="stylesheet" href="/style/button.css?v=<?= $version ?>">
		<link href="/style/insurance_items.css" rel="stylesheet" type="text/css">

		<link href="/assets/sweetalert/dist/sweetalert.css" rel="stylesheet" type="text/css">
		<script src="/assets/sweetalert/dist/sweetalert.min.js"></script>
		<script src="/assets/lodash.js"></script>
	</head>

	<body>

	<!-- Header Starts -->
	<header>
		<div class="container">
			<div class="row">
				<div class="span6" style="/*margin-left: 35px;*/">
					<div class="logo">
						<h1><a href="/">Medic<span class="color">plus</span></a></h1>

						<div class="hmeta">Medical Records Simplified</div>
					</div>
				</div>
				<div class="span6 no-print" style="float:right">
					<div class="form">
						<form method="get" id="searchform" action="" class="form-search">
							<label><input disabled placeholder="search..." type="text" value="" name="s" id="s"
							              class="input-medium"/></label>
							<button disabled type="submit" class="btn">Search</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</header>

	<!-- Navigation bar starts -->
	<div class="navbar no-print">
		<div class="container">
			<div class="navbar-inner">
				<div class="container content">
					<a class="btn btn-navbar no-print" data-toggle="collapse" data-target=".nav-collapse">
						<span><span class="icon-align-justify"></span>Menu</span>
					</a>

					<div class="nav-collapse collapse">
						<ul class="nav no-print">
							<li><a href="/"><i class="icon-home"></i> Home</a></li>
							<?php if (isset($_GET['admission'])/* && $_GET['admission'] == 1*/) { ?>
								<li><a href="/admissions">Admission Home</a></li><?php } ?>
							<?php if (isset($extra_link)) { ?>
								<li><a href="<?= $extra_link['link']; ?>"><?= $extra_link['title']; ?> Home</a>
								</li><?php } ?>
							<?php if (isset($title)) { ?>
								<li><a href="."><?= $title; ?> /Home</a></li><?php } ?>

							<!-- Refer Bootstrap navbar doc -->
							<li class="dropdown">
								<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
									<!--<span class="icon-envelope"></span>--> Inbox
									<span class="li_count _badge badge-important_" data-badge="" id="totalMessages"></span>
									<b class="caret"></b>
								</a>
								<ul class="dropdown-menu" id="couter-area">
									<li><a href="/messaging/"><i class="icon-envelope-alt"></i>Message Home</a></li>
									<!--  <li><a href="/messaging/menu_up.php?type=secured"><span class="li_count _badge badge-important_" data-name="mail">0</span>Secured Mail</a></li>-->
									<!--  <li><a href="/messaging/menu_up.php?type=notifications"><span class="li_count _badge badge-important_" data-name="notification">0</span>Notifications</a></li>-->
									<li><a href="/messaging/menu_up.php?type=queue">
											<span class="li_count _badge badge-important_" data-name="queue" data-badge="0"></span>Queue</a></li>
									<li><a href="/messaging/menu_up.php?type=aqueue">
											<span class="li_count _badge badge-important_" data-name="aqueue" data-badge="0"></span>Approved Queue</a>
									</li>
									<li><a href="/messaging/menu_up.php?type=appointmentlist">
											<span class="li_count _badge badge-important_" data-name="appointment" data-badge="0"></span>Appointment List</a>
									</li>
									<li><a href="/messaging/menu_up.php?type=referral">
											<span class="li_count _badge badge-important_" data-name="referral" data-badge="0"></span>Referrals</a>
									</li>
									<li>
										<a href="/messaging/menu_up.php?type=signature">
											<span class="li_count _badge badge-important_" data-name="signature" data-badge="0">Signature List</span>
										</a>
									</li>
								</ul>
							</li>
							<?php if (false !== strpos($_SERVER['REQUEST_URI'], 'immunization')) { ?>
								<li class="dropdown">
									<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
										<span class="icon-envelope"></span>
										Immunization Reports
										<b class="caret"></b>
									</a>
									<ul class="dropdown-menu">
										<li><a href="/immunization/summary.php?type=v"><i class="icon-beaker"></i>
												Vaccines due</a></li>
										<li><a href="/immunization/summary.php?type=p"><i class="icon-user"></i>
												Patients due</a></li>
									</ul>
								</li><?php } ?>
							<li class="last dropdown">
								<a href="javascript:" class="dropdown-toggle" data-toggle="dropdown">
									<span class="icon-cog"></span>
									Settings
									<b class="caret"></b>
								</a>
								<ul class="dropdown-menu" style="margin-left: -48px;"><?php if (isset($_SESSION['staffID'])) { ?>
										<li><a href="/staff_profile.php?id=<?= $_SESSION['staffID'] ?>"><i
													class="icon-user"></i> My Profile</a></li>

										<?php
										if (!class_exists('StaffManager')) {
											require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.staff.php';
										}
										$staff = new StaffManager();
										if ($staff->isSubscribedToroom($_SESSION['staffID'])) {
											echo '<li><a href="javascript:;" id="swub_link_out"><i class="icon-remove"></i> Unsubscribe from Room</a></li>';
										} else {
											if (strtolower($staff->getStaffInfo($_SESSION['staffID'])['profession']) == "doctor") {
												echo '<li><a href="javascript:;" id="swub_link"><i class="icon-plus"></i> Subscribe to Room</a></li>';
											}
										}
										?><?php } ?>
									<li><a href="javascript:" id="status"><i class="icon-info-sign"></i> Room Status</a></li>
									<li><a href="/pm/"><i class="icon-cogs"></i> Configurations</a></li>
									<li><a href="/pm/reporting"><i class="icon-bar-chart"></i> Reports</a></li>
									<li><a href="/logout.php"><i class="icon-lock"></i> Log out</a></li>

								</ul>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Navigation bar ends -->

	<div class="content">
		<div class="container">
			<?php
			if (isset($page)) {
				include $page;
			} else {
				echo '<div class="warning-bar">Sorry, a fatal error has occurred. Please report this to the administrator</div>';
			} ?>

			<div class="border"></div>

		</div>
	</div>

	<!-- Social -->

	<!-- Footer -->
	<footer>
		<div class="container">
			<div class="row">
				<!--            <div class="span12">-->
				<div class="copy">
					<h6>Medic<span class="color">plus</span></h6>

					<p>Copyright &copy; 2013-2017 - Version: <strong><?= get_version() ?></strong> <!--| <a href="">FAQ</a> | <a
							href="">Contact
							Us</a> | <a class="boxy" data-title="Report A Bug" href="/boxy.bug_report.php">Report a Bug</a>-->
						<span class="pull-right fadedText">Logged in as: <strong><?= @$_SESSION['username']?></strong></span>
					</p>

				</div>
				<!--            </div>-->

			</div>
			<div class="clearfix"></div>

			<div id="content_loader" style="display: none;">loading <img src="/img/loading_bar.gif?v=<?= $version ?>" alt="loading"></div>
			<script type="text/javascript">
				$(document).ready(function () {
					$("#swub_link").click(function () {
						Boxy.load('/login_subscribe_to_room.php', {title: 'Select Examination Room'});
					});
					$("#swub_link_out").click(function () {
						Boxy.ask('Are you sure you want to leave the consultation room?', ['Sure', 'Not yet'], function (a) {
							if (a === "Sure") {
								location.href = '/login_unsubscribe.php';
							}
						});
					});
					$("#status").click(function () {
						Boxy.load('/roomsearch.php', {title: 'Existing Examination/Consultation Rooms'});
					});
				});

				function refreshMessageCounters() {
					demo();
					$.ajax({
						url: '/counter.php',
						type: 'get',
						dataType: 'json',
						success: function (s) {
							if( s === null ){
								if(localStorage.shown !== 'true'){
									Boxy.warn("Your session has expired. Please login again.", function () {
										localStorage.removeItem('shown');
										location.reload();
									});
									localStorage.setItem('shown', 'true');
								}
							}else{
								$("span[data-name='queue']").attr("data-badge", s.queue);
								$("span[data-name='aqueue']").attr("data-badge", s.aqueue);
								$("span[data-name='appointment']").attr("data-badge", s.appointment);
								$("span[data-name='referral']").attr("data-badge", s.referral);
								$("#totalMessages").attr("data-badge", s.mail + s.notification + s.queue + s.aqueue + s.appointment + s.referral);
							}

						}
					});
				}

				function uiArrangeBlocks() {
					var countBars = $(".row .span4").length;
					if (countBars % 3 === 2) {
						$(".row .span4:last").width('65%');
					} else if (countBars % 3 === 1) {
						$(".row .span4:last").width('99.5%');
					}
				}

				function demo() {
					var version = '<?= get_version()?>';
					if (version.toLowerCase().indexOf('demo') !== -1) {
						$('body').css('background-color', 'rgba(202, 39, 39, 0.71)');
					}
				}

				setInterval(refreshMessageCounters, 300000);//5 minutes interval
				function preventDoubleClicks() {
					$('button, .btn, input[type="submit"], input[type="button"], input[type="reset"]').live('click', function (e) {
						var $this = $(this);
						setTimeout(function () {
							$this.attr({'disabled': 'disabled'});
							setTimeout(function () {
								$this.removeAttr('disabled');
							}, 500);
						}, 5);
						// e.preventDefault();
					});
				}
			</script>
		</div>
	</footer>
	<div id="notify" style="display:none; z-index:999999">
		<!--
		Later on, you can choose which template to use by referring to the
		ID assigned to each template.  Alternatively, you could refer
		to each template by index, so in this example, "basic-tempate" is
		index 0 and "advanced-template" is index 1.
		-->
		<div id="sticky">
			<a class="ui-notify-cross ui-notify-close icon-remove" href="#"></a>
			<h1>#{title}</h1>
			<p>#{text}</p>
		</div>
	</div>
	<?php include "greetings.php"; ?>
	</body>
	</html>
