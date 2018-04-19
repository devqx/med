<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$version = date('Ymd');
?>
<!DOCTYPE html>
<html lang="en" moznomarginboxes mozdisallowselectionprint>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<title>MedicPlus - <?php //echo $title; ?> </title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta property="og:image" content="/img/icon3.png?v=<?= $version ?>"/>
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="">


	<!-- Stylesheets -->
	<link rel="stylesheet" href="/style/bootstrap.css?v=<?= $version ?>" media="all">
	<link rel="stylesheet" href="/style/font-awesome.css?v=<?= $version ?>">
	<link rel="stylesheet" href="/style/style.css?v=<?= $version ?>" media="all">

	<!-- Colors - Orange, Purple, Light Blue (lblue), Red, Green and Blue -->
	<link rel="stylesheet" href="/style/blue.css?v=<?= $version ?>">

	<link rel="stylesheet" href="/style/bootstrap-responsive.css?v=<?= $version ?>">

	<link rel="stylesheet" href="/assets/boxy/css/boxy.css?v=<?= $version ?>">
	<link rel="stylesheet" href="/style/def.css?v=<?= $version ?>" media="all">
	<link rel="stylesheet" href="/style/login.css?v=<?= $version ?>" media="all">
	<link rel="stylesheet" href="/assets/vex-2.2.1/css/vex-theme-default.css?v=<?= $version ?>" media="all">

	<!-- JS -->
	<script src="/js/jquery.js?v=<?= $version ?>"></script>
	<script src="/js/webtoolkit.aim.js?v=<?= $version ?>" type="text/javascript"></script>
	<script src="/js/bootstrap.js?v=<?= $version ?>"></script>
	<script type="text/javascript" src="/assets/select2_2/select2.min.js?v=<?= $version ?>"></script>
	<link rel="stylesheet" href="/assets/select2_2/select2.css?v=<?= $version ?>">
	<script src="/assets/boxy/js/jquery.boxy.js?v=<?= $version ?>"></script>
	<script src="/assets/jquery-number-master/jquery.number.js?v=<?= $version ?>"></script>
	<script type="text/javascript">
		$(document).ready(function () {
			$(".boxy").boxy();
		});
		<?php echo $script_block;?>
	</script>
	<!-- HTML5 Support for IE -->
	<!--[if lt IE 9]>
	<script src="/js/html5shim.js?v=<?= $version?>"></script>
	<![endif]-->

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
	
	<link rel="stylesheet" href="/style/button.css?v=<?= $version ?>">
</head>

<body>

<!-- Header Starts -->
<header>
	<div class="container">
		<div class="row">
			<div class="span6" style="float:none !important; text-align: center; width:auto">
				<div class="logo">
					<h1><a href="/index.php">Medic<span class="color">plus</span></a></h1>

					<div class="hmeta">Medical Records Simplified</div>
				</div>
			</div>

			<!--<div class="span6">
					<div class="form">
							<form method="get" id="searchform" action="#" class="form-search">
									<input type="text" value="" name="s" id="s" class="input-medium"/>
									<button type="submit" class="btn">Search</button>
							</form>
					</div>
			</div>-->

		</div>
	</div>
</header>

<!-- Navigation bar starts -->

<!-- Navigation bar ends -->

<div class="content">
	<div class="container">

		<?php include $page ?>


		<div class="border"></div>

		<!-- Product & links starts -->

		<!-- Product & links ends -->

	</div>
</div>

<!-- Social -->

<!-- Footer -->
<footer>
	<div class="container">
		<div class="row">

			<!--      <div class="span12">-->
			<div class="copy">
				<!--            <h6>Medic<span class="color">plus</span></h6>-->
				<p>Version: <strong><?= get_version() ?></strong> <!--| <a href="">FAQ</a> | <a href="">Contact
                        Us</a> | <a class="boxy" data-title="Report A Bug" href="/boxy.bug_report.php">Report a Bug</a>-->
				</p>
			</div>
			<!--      </div>-->

		</div>
		<div class="clearfix"></div>
		<div id="content_loader" style="display: none;">loading <img src="/img/loading_bar.gif" alt="loading"></div>
	</div>
</footer>
<img src="/img/loading.gif" style="display: none">
</body>
</html>
