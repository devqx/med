<div class="register clear">
  <div class="row">
	<div class="span12 clear" style="padding-left: 45px;">
	  <div class="lrform" style="margin-top: 20px; border-radius: 0px !important;">
		 <h5 style="text-align:center">Login to your Account</h5>
	  <div class="form">
		  <form class="form-horizontal" method="post" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finished})">
<!--			  <div class="control-group">-->
<!--				<div class="controls">-->
<!--				</div>-->
<!--			  </div>-->
			  <div id="output"></div>
			  <!-- Username -->
			  <div class="control-group">
				<label class="control-label" for="username">Username</label>
				<div class="controls">
				  <input type="text" value="<?= isset($_COOKIE['medicplus_username'])?$_COOKIE['medicplus_username']:""?>" autofocus="true" name="userid" class="input-large" id="username" autocomplete="off">
				</div>
			  </div>
			  <!-- Password -->
			  <div class="control-group">
				<label class="control-label" for="password">Password</label>
				<div class="controls">
				  <input type="password" value="<?= isset($_COOKIE['medicplus_passcode'])?$_COOKIE['medicplus_passcode']:""?>" name="passwd" class="input-large" id="password">
				  <?php if(isset($_SESSION['location'])){ ?>
                      <input type="hidden" name="next" value="<?= $_SESSION['location']?>">
                      <?php unset($_SESSION['location']);}?>
				</div>
			  </div>
			  <div class="control-group">
				 <div class="controls">
					<label class="checkbox">
					<input type="checkbox" name="remember"> Remember my login
					</label>
				 </div>
			  </div>
			  <!-- Buttons -->
			  <div class="form-actions">
				 <!-- Buttons -->
				<button type="submit" class="btn">Login</button>
				<button type="reset" class="btn">Reset</button>
			  </div>
		  </form>
		</div>
	  </div>

	</div>
  </div>
</div>  
