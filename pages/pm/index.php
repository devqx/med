<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
error_log("Has role management". json_encode($protect->mgt));
if (!isset($_SESSION)) {
	session_start();
}
@session_start();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
?>


<!-- Begin Content -->
<script type="text/javascript">
	function resizeBoxy(boxy) {
		setTimeout(function () {
			boxy.resize(1000);
			boxy.center();
		}, 50);
	}

	function hideAll(count) {
		loadContent(count);
	}
	function loadContent(count) {
		switch (count) {
			case(1): {
				Boxy.load("/pages/pm/boxy_getusers.php", {
					title: "Create User/Hospital", afterShow: function () {
						resizeBoxy(this);
						$('#existingUsers').load("/pages/pm/getUserList.php");
					}
				});
				break;
			}
			case(2): {
				Boxy.load("/pages/pm/boxy_adminmgt.php", {
					title: "Set Administrative Details", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}
			case(3): {
				Boxy.load("/pages/pm/bedspaces/index.php", {
					title: "Admissions Management", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}
			case(4): {
				Boxy.load("/pages/pm/labmgt.php", {
					title: "Laboratory Management", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}
			case(5): {
				Boxy.load("/pages/pm/insurance_mgt.php", {
					title: "Bill Management", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}
			case(6): {
				Boxy.load("/pages/pm/vaccine/index.php", {
					title: "Vaccine Management", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}
			case(7): {
				Boxy.load("/pages/pm/pharmacy/index.php", {
					title: "Pharmacy Management", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}
			case(8): {
				window.location = "/pm/reporting/index.php";
				break;
			}
			case(9): {
				Boxy.load("/pm/imaging/index.php", {
					title: "Imagery Management", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}
			case(10): {
				Boxy.load("/pm/procedure/index.php", {
					title: "Procedure Management", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}
			case(11): {
				Boxy.load("/pm/items/index.php", {
					title: "Consumables Management", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}
			case(12): {
				window.location = "/backups";
				break;
			}
			case(13): {
				Boxy.load("/pages/pm/ophthmgt.php", {
					title: "Ophthalmology Management", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}
			case(18): {
				Boxy.load("/pages/pm/physiomgt.php", {
					title: "Physiotherapy Management", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}
			case(15): {
				Boxy.load("/pm/dentistry/index.php", {
					title: "Dentistry Management", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}
			case(14): {
				Boxy.load("/pages/pm/medical_history.php", {
					title: "Medical History Configuration", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}
			case(16): {
				Boxy.load("/pages/pm/antenatal_packages.php", {
					title: "Antenatal Packages Configuration", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}
			case(17): {
				Boxy.load("/pages/pm/voucher.php", {
					title: "Voucher Configuration", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}
			case(19): {
				Boxy.load("/pages/pm/medical_exam.php", {
					title: "Medical Reports", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}	case(20): {
				Boxy.load("/ivf/pm/index.php", {
					title: "IVF Clinic Management", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			} case(21): {
				Boxy.load("/pages/pm/packages.php", {
					title: "Packages and Promos Management", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			} case(22): {
				Boxy.load("/pages/pm/clinicalTasksCombos.php", {
					title: "Clinical Tasks Combos Management", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			} case(23): {
				Boxy.load("/pages/pm/sforms/index.php", {
					title: "SForms", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			} case(24): {
				Boxy.load("/pages/pm/calendar/index.php", {
					title: "SForms", afterShow: function () {
						resizeBoxy(this);
					}
				});
				break;
			}
			default: {
				Boxy.alert("Error!");
			}
		}
	}
</script>
<div class="content">
	<div class="container">
		<div class="features-four">
			<div class="row">
				<?php if ($this_user->hasRole($protect->mgt)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(1)"><i class="icon-user"></i></a>
							<a href="javascript:hideAll(1)"><h4>Users Management &raquo;</h4></a>
							<p>Create new user account/Edit existing account</p></div>
					</div>
				<?php } ?>
				<?php if ($this_user->hasRole($protect->mgt)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(2)"><i class="icon-user-md"></i></a>
							<a href="javascript:hideAll(2)"><h4>Administrative Management &raquo;</h4></a>
							<p>Create and manage staff types, logo, consultation rooms, ...</p>
							<div id="adminmgt" class="c"></div>
						</div>
					</div>
				<?php } ?>
				<?php if ($this_user->hasRole($protect->mgt)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(24)"><i class="icon-calendar"></i></a>
							<a href="javascript:hideAll(24)"><h4>Calendar Management &raquo;</h4></a>
							<p>Create and manage Calendar resources</p>
							<div class="c"></div>
						</div>
					</div>
				<?php } ?>
				<?php if ($this_user->hasRole($protect->mgt) && $this_user->hasRole($protect->nurse)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(3)"><i class="icon-hospital"></i></a>
							<a href="javascript:hideAll(3)"><h4>Admissions Management &raquo;</h4></a>
							<p>Manage Bed spaces in my hospital</p>
							<div id="bedmgt" class="c"></div>
						</div>
					</div>
				<?php } ?>
				<?php if ($this_user->hasRole($protect->mgt) && $this_user->hasRole($protect->lab_super)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(4)"><i class="icon-beaker"></i></a>
							<a href="javascript:hideAll(4);"><h4>Laboratory management &raquo;</h4></a>
							<p>Laboratory diagnoses and tests costs</p>
							<div id="labmgt" class="c"></div>
						</div>
					</div>
				<?php } ?>
				
				<?php if (is_dir($_SERVER['DOCUMENT_ROOT'] . "/ophthalmology") && $this_user->hasRole($protect->mgt) && $this_user->hasRole($protect->ophthalmology)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(13)"><i class="icon-eye-open"></i></a>
							<a href="javascript:hideAll(13);"><h4>Ophthalmology management &raquo;</h4></a>
							<p>Ophthalmology Services and Costs</p>
							<div id="ophthmgt" class="c"></div>
						</div>
					</div>
				<?php } ?>
				
				<?php if (is_dir($_SERVER['DOCUMENT_ROOT'] . "/physiotherapy") && $this_user->hasRole($protect->mgt) && $this_user->hasRole($protect->physiotherapy)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(18)"><i class="therapy-therapy"></i></a>
							<a href="javascript:hideAll(18);"><h4>Physiotherapy management &raquo;</h4></a>
							<p>Physiotherapy Services and Costs</p>
							<div id="physiomgt" class="c"></div>
						</div>
					</div>
				<?php } ?>
				<?php if (is_dir($_SERVER['DOCUMENT_ROOT'] . "/dentistry") && $this_user->hasRole($protect->mgt) && $this_user->hasRole($protect->dentistry)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(15)"><i class="flaticon-dental1"></i></a>
							<a href="javascript:hideAll(15);"><h4>Dentistry management &raquo;</h4></a>
							<p>Dentistry Services and Costs</p>
							<div id="dentmgt" class="c"></div>
						</div>
					</div>
				<?php } ?>
				
				<?php if ($this_user->hasRole($protect->mgt) && $this_user->hasRole($protect->doctor_role)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(19)"><i class="exam-test"></i></a>
							<a href="javascript:hideAll(19);"><h4>Medical Report &raquo;</h4></a>
							<p>Medical Report/Examinations management </p>
							<div id="mr_mgt" class="c"></div>
						</div>
					</div>
				<?php } ?>
				<?php if ($this_user->hasRole($protect->mgt) && $this_user->hasRole($protect->accounts) && $this_user->hasRole($protect->hmo_officer)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(5)"><i class="icon-shopping-cart"></i></a>
							<a href="javascript:hideAll(5)"><h4>Billing Manager &raquo;</h4></a>
							<p>Manage your billing system; configure insurance scheme items</p>
							<div id="insmgt" class="c"></div>
						</div>
					</div>
				<?php } ?>
				<?php if (is_dir($_SERVER['DOCUMENT_ROOT'] . "/immunization") && $this_user->hasRole($protect->mgt) && $this_user->hasRole($protect->nurse)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(6)"><i class="icon-tint"></i></a>
							<a href="javascript:hideAll(6)"><h4>Vaccine Management &raquo;</h4></a>
							<p>Manage vaccines in my hospital</p>
							<div id="vacmgt" class="c"></div>
						</div>
					</div>
				<?php } ?>
				<?php if ($this_user->hasRole($protect->mgt) && $this_user->hasRole($protect->pharmacy)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(7)"><i class="icon-tint"></i></a>
							<a href="javascript:hideAll(7)"><h4>Pharmacy Management &raquo;</h4></a>
							<p>Manage your drug store and inventory</p>
							<div id="vacmgt" class="c"></div>
						</div>
					</div>
				<?php } ?>
				<?php if ($this_user->hasRole($protect->radiology) && $this_user->hasRole($protect->mgt)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(9)"><i class="icon-picture"></i></a>
							<a href="javascript:hideAll(9)"><h4>Imaging/Scans &raquo;</h4></a>
							<p>Configure imagery/scans </p>
							<div id="imgmgt" class="c"></div>
						</div>
					</div>
				<?php } ?>
				<?php if ($this_user->hasRole($protect->doctor_role) /*&& $this_user->hasRole($protect->mgt)*/) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(14)"><i class="icon-time"></i></a>
							<a href="javascript:hideAll(14)"><h4>Medical History Data &raquo;</h4></a>
							<p>Manage Medical History Data Elements </p><!--<div id="imgmgt" class="c"></div>-->
						</div>
					</div>
				<?php } ?>
				<?php if ($this_user->hasRole($protect->doctor_role) /*&& $this_user->hasRole($protect->mgt)*/) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(22)"><i class="icon-th-list"></i></a>
							<a href="javascript:hideAll(22)"><h4>Clinical Task Combo &raquo;</h4></a>
							<p>Manage clinical tasks groups </p><!--<div id="imgmgt" class="c"></div>-->
						</div>
					</div>
				<?php } ?>
				<?php if ($this_user->hasRole($protect->doctor_role) /*&& $this_user->hasRole($protect->mgt)*/) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(23)"><i class="icon-copy"></i></a>
							<a href="javascript:hideAll(23)"><h4>SForms &raquo;</h4></a>
							<p>Manage SForms </p><!--<div id="imgmgt" class="c"></div>-->
						</div>
					</div>
				<?php } ?>
				<?php if (is_dir($_SERVER['DOCUMENT_ROOT'] . "/antenatal") && $this_user->hasRole($protect->doctor_role) /*&& $this_user->hasRole($protect->mgt)*/) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(16)"><i class="antenatal-mother"></i></a>
							<a href="javascript:hideAll(16)"><h4>Antenatal Packages &raquo;</h4></a>
							<p>Manage Antenatal Packages </p>
						</div>
					</div>
				<?php } ?>
				<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(21)"><i class="icon-trophy"></i></a>
							<a href="javascript:hideAll(21)"><h4>Packages and Promos &raquo;</h4></a>
							<p>Manage Packages and Promotional Offers</p>
						</div>
					</div>
				<?php if ($this_user->hasRole($protect->mgt) && $this_user->hasRole($protect->procedures)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(10)"><i class="icon-medkit"></i></a>
							<a href="javascript:hideAll(10)"><h4>Procedures &raquo;</h4></a>
							<p>Configure procedure services </p>
							<div id="procmgt" class="c"></div>
						</div>
					</div>
				<?php } ?>
				
				<?php if (is_dir($_SERVER['DOCUMENT_ROOT'] . "/consumableItems")  && $this_user->hasRole($protect->mgt) && $this_user->hasRole($protect->consumables)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(11)"><i class="icon-lemon"></i></a>
							<a href="javascript:hideAll(11)"><h4>Consumables &raquo;</h4></a>
							<p>Configuration for non-drug items</p>
						</div>
					</div>
				<?php } ?>
				<?php if (is_dir($_SERVER['DOCUMENT_ROOT'] . "/ivf") && $this_user->hasRole($protect->mgt)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(20)"><i class="sperm-people"></i></a>
							<a href="javascript:hideAll(20)"><h4>IVF Clinic Management &raquo;</h4></a>
							<p>Configurations for IVF Labs</p>
							<!--<div id="repmgt" class="c"></div>-->
						</div>
					</div>
				<?php } ?>
				
				<?php if ($this_user->hasRole($protect->mgt)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(12)"><i class="icon-cloud"></i></a>
							<a href="javascript:hideAll(12)"><h4>Backup &raquo;</h4></a>
							<p>Backup files, database</p>
							<!--<div id="repmgt" class="c"></div>-->
						</div>
					</div>
				<?php } ?>
				
				<?php if ($this_user->hasRole($protect->mgt) && $this_user->hasRole($protect->voucher)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(17)"><i class="icon-gift"></i></a>
							<a href="javascript:hideAll(17)"><h4>Voucher &raquo;</h4></a>
							<p>Voucher management</p><!--<div id="repmgt" class="c"></div>-->
						</div>
					</div>
				<?php } ?>
				
				<?php if ($this_user->hasRole($protect->mgt)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="javascript:hideAll(8)"><i class="icon-bar-chart"></i></a>
							<a href="javascript:hideAll(8)"><h4>Reports &raquo;</h4></a>
							<p>MDG, Antenatal Visits, Vaccine enrollments reports </p>
							<!--<div id="repmgt" class="c"></div>-->
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
