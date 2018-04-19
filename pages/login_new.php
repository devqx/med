<form class="loginForm" method="post" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finished})">
    <header><div class="span6" style="float:none !important; text-align: center; width:auto">
        <div class="logo">
            <h1><a href="/index.php">Medic<span class="color">plus</span></a></h1>

            <div class="hmeta">Medical Records Simplified</div>
        </div>
    </div></header>
    <!--<header><div class="span6" style="float:none !important; text-align: center; width:auto">
        <div class="logo">
            <h1><a href="/index.php"><img src="/img/logo_medicplus_high_definition.png" style="height: 50px"></a></h1>

            <div class="hmeta">Medical Records Simplified</div>
        </div>
    </div></header>-->
<!--    <h3 class="center">Login to <br><img src="/img/logo_medicplus_high_definition.png" style="height: 50px"></h3>-->
    <span id="output"></span>

    <label><input type="text" value="<?= isset($_COOKIE['medicplus_username'])?$_COOKIE['medicplus_username']:""?>" autofocus="true" name="userid" placeholder="Username" id="username" autocomplete="off"></label>

    <label><input type="password" value="<?= isset($_COOKIE['medicplus_passcode'])?$_COOKIE['medicplus_passcode']:""?>" name="passwd" placeholder="Password" id="password"></label>

    <?php if(isset($_SESSION['location'])){ ?>
        <input type="hidden" name="next" value="<?= $_SESSION['location']?>">
        <?php unset($_SESSION['location']);}?>

    <label class="checkbox">
        <input type="checkbox" name="remember"> Remember my login
    </label>
    <div class="clear"></div>
    <label class="btn-block"></label>
    <div class="btn-group-justified">
        <div class="btn-group"><button type="submit" class="btn">Login</button></div>
        <div class="btn-group"><button type="reset" class="btn-link pull-right">Reset <i class="icon-angle-right"></i></button></div>
    </div>

</form>
