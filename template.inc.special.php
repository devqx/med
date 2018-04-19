
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <title>MedicPlus - <?php //echo $title; ?> </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">


    <!-- Stylesheets -->
    <link rel="stylesheet" href="/style/bootstrap.css" >
    <link rel="stylesheet" href="/style/font-awesome.css">

    <link rel="stylesheet" href="/style/style.css">

    <!-- Colors - Orange, Purple, Light Blue (lblue), Red, Green and Blue -->
    <link rel="stylesheet" href="/style/blue.css">

    <link rel="stylesheet" href="/style/bootstrap-responsive.css">
    <link rel="stylesheet" href="/style/boxy.css">
    <link rel="stylesheet" href="/style/def.css">
    <link rel="stylesheet" href="/style/tv.css">
    <link rel="stylesheet" href="/style/unslider.css">
    <link rel="stylesheet" href="/style/button.css">
    <!-- JS -->
    <script src="/js/jquery.js"></script>
    <script src="/js/webtoolkit.aim.js" type="text/javascript"></script>
    <script src="/js/bootstrap.js"></script>

    <script src="/js/jquery.boxy.js"></script>
    <script src="/js/unslider.min.js"></script>
    <script type="text/javascript">
        <?php echo $script_block;?>
    </script>
    <!-- HTML5 Support for IE -->
    <!--[if lt IE 9]>
    <script src="/js/html5shim.js"></script>
    <![endif]-->

    <!-- Favicon -->
    <link rel="shortcut icon" href="/favicon.ico">
</head>

<body>
<header>
    <div class="container">
        <div class="row">
            <div class="span6">
                <div class="logo">
                    <h1><a href="/">Medic<span class="color">plus</span></a></h1>

                    <div class="hmeta">Medical Records Simplified</div>
                </div>
            </div>
            <div class="span6 no-print" style="float:right">

            </div>
        </div>
    </div>
</header>
<!-- Header Starts -->

<!-- Navigation bar starts -->

<!-- Navigation bar ends -->


<div class="content">
    <div class="container">
        <?php include $page?>
<!--
        <div class="border"></div>
-->

    </div>
</div>

<!-- Social -->

<!-- Footer -->

</body>
</html>
