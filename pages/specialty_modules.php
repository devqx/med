<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
if (!isset($_SESSION)) {
    @session_start();
}
$this_user = null;
if (isset($_SESSION['staffID'])) {
    $this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
}
?>
<div class="content">
    <div class="container">
        <div class="features-four">
            <div class="row">

                <?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/physiotherapy") && $this_user && ($this_user->hasRole($protect->physiotherapy))) {//use role management to also apply who sees which app?>
                    <div class="span4">
                        <div class="f-block b-blue">
                            <a href="/physiotherapy/"><i class="therapy-therapy"></i></a>
                            <a href="/physiotherapy/"><h4>Physiotherapy</h4></a>

                            <p>Manage Patients Physiotherapy sessions</p>
                        </div>
                    </div><?php } ?>
	            
                <?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/ivf") && $this_user && ($this_user->hasRole($protect->ivf_nurse) || $this_user->hasRole($protect->ivf_doctor) )) { ?>
                    <div class="span4">
                        <div class="f-block b-blue">
                            <a href="/ivf/"><i class="sperm-people"></i></a>
                            <a href="/ivf/"><h4>IVF Clinic</h4></a>

                            <p>IVF Clinical Records</p>
                        </div>
                    </div>
                    <?php
                } ?>

                <?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/ophthalmology") && $this_user && ($this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->ophthalmology))) { ?>
                    <div class="span4">
                        <div class="f-block b-blue">
                            <a href="/ophthalmology/"><i class="icon-eye-open"></i></a>
                            <a href="/ophthalmology/"><h4>Optometry</h4></a>
                            <p>Optometry Requests/Results</p>

                        </div>
                    </div><?php } ?>
                <?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/dentistry") && $this_user && ($this_user->hasRole($protect->dentistry) )) { ?>
                    <div class="span4">
                        <div class="f-block b-blue">
                            <a href="/dentistry/"><i class="flaticon-dental1"></i></a>
                            <a href="/dentistry/"><h4>Dentistry</h4></a>
                            <p>Dentistry Requests/Results</p>
                        </div>
                    </div><?php } ?>


                <?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/arvMobile") && $this_user && ($this_user->hasRole($protect->doctor_role))) { ?>
                    <div class="span4">
                        <div class="f-block b-blue">
                            <a href="/arvMobile/"><i class="ribbon-ribbon"></i></a>
                            <a href="/arvMobile/"><h4>ARV Clinic</h4></a>
                            <p>ST Infections Management</p>
                        </div>
                    </div>
                <?php } ?>

            </div>
        </div>
    </div>
</div>