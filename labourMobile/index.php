<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="generator" content="PSK">
	<title>Medicplus - Labour Management</title>

	<!-- Place favicon.ico in the `app/` directory -->
	<!-- Chrome for Android theme color -->
	<meta name="theme-color" content="#2E3AA1">

	<!-- Web Application Manifest -->
	<link rel="manifest" href="manifest.json">

	<!-- Tile color for Win8 -->
	<meta name="msapplication-TileColor" content="#3372DF">

	<!-- Add to homescreen for Chrome on Android -->
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="application-name" content="Labour Management">
	<link rel="icon" sizes="192x192" href="images/icon4.png">

	<!-- Add to homescreen for Safari on iOS -->
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-title" content="Labour Management">
	<link rel="apple-touch-icon" href="images/icon4.png">

	<!-- Tile icon for Win8 (144x144) -->
	<meta name="msapplication-TileImage" content="images/icon4.png">

	<!-- build:css styles/main.css -->
	<link rel="stylesheet" href="styles/main.css">
	<!-- endbuild-->

	<!-- build:js bower_components/webcomponentsjs/webcomponents-lite.min.js -->
	<script src="../bower_components/webcomponentsjs/webcomponents-lite.js"></script>
	<!-- endbuild -->

	<script src="scripts/indexed_db.js?t=<?= time() ?>"></script>
	<script src="../bower_components/jquery/dist/jquery.min.js"></script>
	<script src="../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="../bower_components/angular/angular.min.js"></script>
	<script src="../bower_components/object.observe/dist/object-observe-lite.min.js"></script>
	<script src="../bower_components/ng-polymer-elements/ng-polymer-elements.js"></script>
	<script src="../bower_components/moment/min/moment.min.js"></script>
	<script src="../bower_components/angular-indexedDB/angular-indexed-db.js"></script>
	<script src="scripts/main.js?t=<?= time() ?>"></script>
	<script src="/assets/lodash.js"></script>

	<!-- Because this project uses vulcanize this should be your only html import
			 in this file. All other imports should go in elements.html -->
	<link rel="import" href="elements/elements.html">

	<!-- For shared styles, shared-styles.html import in elements.html -->
	<style is="custom-style" include="shared-styles"></style>
</head>

<body>
<span id="browser-sync-binding"></span>
<template is="dom-bind" id="app">
	<paper-drawer-panel id="paperDrawerPanel" force-narrow>
		<!-- Drawer Scroll Header Panel -->
		<paper-scroll-header-panel drawer fixed>
			<!-- Drawer Toolbar -->
			<paper-toolbar id="drawerToolbar">
				<span class="menu-name"><a href="/">Medic<span class="color">plus</span></a></span>
			</paper-toolbar>
			<!-- Drawer Content -->
			<paper-menu attr-for-selected="data-route" selected="[[route]]">
				<a data-route="enroll-patient" href="{{baseUrl}}enroll-patient"><span>Enroll Patient</span></a>
				<a data-route="patients" href="{{baseUrl}}patients"><span>Labour Patients</span></a>
				<a data-route="search-labour" href="{{baseUrl}}search"><span>Search Labour Records</span></a>
			</paper-menu>
		</paper-scroll-header-panel>
		<!-- Main Area -->
		<paper-scroll-header-panel main id="headerPanelMain" condenses fixed keep-condensed-header mode="waterfall-tall" shadow="true">
			<!-- Main Toolbar -->
			<paper-toolbar id="mainToolbar" class="tall_">
				<paper-icon-button hidden$="{{detailView}}" id="paperToggle" icon="menu" paper-drawer-toggle></paper-icon-button>
				<paper-icon-button on-tap="goHome" hidden$="{{!detailView}}" icon="hardware:keyboard-backspace"></paper-icon-button>

				<span class="space"></span>
				<!-- Application name -->
				<div class="middle middle-container">
					<div class="app-name">Labour Management</div>
				</div>
				<paper-menu-button no-animations horizontal-align="right" vertical-offset="10" horizontal-offset="20">
					<paper-icon-button icon="icons:more-vert" class="dropdown-trigger"></paper-icon-button>
					<paper-menu class="dropdown-content">
						<paper-item on-tap="syncData">
							<iron-icon icon="icons:autorenew"></iron-icon>
							Sync Data to Server
						</paper-item>
						<paper-item on-tap="showHelp">
							<iron-icon icon="icons:help-outline"></iron-icon>
							Help
						</paper-item>
					</paper-menu>
				</paper-menu-button>
			</paper-toolbar>
			<!-- Main Content -->
			<div class="content">
				<iron-pages attr-for-selected="data-route" selected="{{route}}">
					<section data-route="enroll-patient">
						<paper-material elevation="1">
							<h2 class="page-title">Enroll Patient</h2>
							<form is="iron-form" id="enroll-patient" method="post" action="/">
								<paper-input name="patientID" id="emrId" label="Patient EMR ID" required auto-validate pattern="^[0-9]*$">
									<paper-icon-button icon="search" suffix on-tap="lookUpPatient"></paper-icon-button>
								</paper-input>
								<paper-input name="patientName" label="Patient Name" value="{{patientName}}" required auto-validate></paper-input>
								<paper-input name="babyFatherName" label="Husband's Name" value="{{patientHusband}}" required auto-validate></paper-input>
								<paper-input name="patientDOB" label="Date of birth" type="date" value="{{patientDOB}}" always-float-label required auto-validate></paper-input>
								<div class="horizontal-section input-block">
									<label class="block">Blood group</label>
									<paper-radio-group name="bloodGroup" id="bloodGroup" selected="{{patientBloodGroup}}">
										<paper-radio-button name="O+">O+</paper-radio-button>
										<paper-radio-button name="O-">O-</paper-radio-button>
										<paper-radio-button name="A+">A+</paper-radio-button>
										<paper-radio-button name="A-">A-</paper-radio-button>
										<paper-radio-button name="B+">B+</paper-radio-button>
										<paper-radio-button name="B-">B-</paper-radio-button>
										<paper-radio-button name="AB+">AB+</paper-radio-button>
										<paper-radio-button name="AB-">AB-</paper-radio-button>
									</paper-radio-group>
								</div>
								<paper-dropdown-menu no-animations label="Gravida" name="gravida" required auto-validate>
									<paper-menu class="dropdown-content" selected="{{patientGravida}}">
										<template is="dom-repeat" items="{{Gravida}}">
											<paper-item>{{item}}</paper-item>
										</template>
									</paper-menu>
								</paper-dropdown-menu>
								<paper-dropdown-menu no-animations label="Parity" name="parity" required auto-validate>
									<paper-menu class="dropdown-content" selected="{{patientPara}}">
										<template is="dom-repeat" items="{{Para}}">
											<paper-item>{{item}}</paper-item>
										</template>
									</paper-menu>
								</paper-dropdown-menu>
								<paper-dropdown-menu no-animations label="Alive" name="alive" required auto-validate>
									<paper-menu class="dropdown-content" selected="{{patientAlive}}">
										<template is="dom-repeat" items="{{Alive}}">
											<paper-item>{{item}}</paper-item>
										</template>
									</paper-menu>
								</paper-dropdown-menu>
								<paper-dropdown-menu no-animations label="Miscarriages" name="abortions" required auto-validate>
									<paper-menu class="dropdown-content" selected="{{patientAbortions}}">
										<template is="dom-repeat" items="{{Abortions}}">
											<paper-item>{{item}}</paper-item>
										</template>
									</paper-menu>
								</paper-dropdown-menu>
								<paper-dropdown-menu no-animations label="Present Pregnancy" name="presentPregnancy" required auto-validate>
									<paper-menu class="dropdown-content" selected="{{patientCurrentPregnancy}}">
										<template is="dom-repeat" items="{{Pregnancies}}">
											<paper-item>{{item}}</paper-item>
										</template>
									</paper-menu>
								</paper-dropdown-menu>
								<paper-input name="patientLMP" label="LMP" type="date" value="{{patientLMP}}" required always-float-label auto-validate></paper-input>
								<paper-button class="input-block" onclick="enrollPatient(event)">Save</paper-button>
								<a class="input-block" href$="{{baseUrl}}patients" onclick="clearThisFrm('enroll-patient')">
									<paper-button>Cancel</paper-button>
								</a>
							</form>
						</paper-material>
					</section>
					<section data-route="patients">
						<div class="cards">
							<template is="dom-repeat" items="{{patientLabourDatum}}">
								<paper-card style="" heading="{{item.patientName}}" data-id="{{item.labourID}}">
									<div class="card-content">
										<div>Date Enrolled: <span>{{formatDateTime(item.dateEnrolled)}}</span></div>
										<div>Gestational Age: <span>{{ weeksFromNow(item.patientLMP) }}</span></div>
										<div>Last Exam: <span>{{assessmentTakeDate(item.assessment)}}</span></div>
										<div>Next Exam: <span>{{assessmentNextDate(item.assessment)}}</span></div>
									</div>
									<div class="card-actions">
										<a href$="{{baseUrl}}patient-profile/{{item.labourID}}">
											<paper-button class="link">Visit Profile
												<iron-icon icon="icons:arrow-forward"></iron-icon>
											</paper-button>
										</a>
									</div>
								</paper-card>
							</template>
						</div>
					</section>
					<section data-route="patient-profile">
						<paper-material elevation="1">
							<!--<a href$="{{baseUrl}}patients">
								<paper-button class="link">
									<iron-icon icon="hardware:keyboard-backspace"></iron-icon> Patients List
								</paper-button>
							</a>-->
							<paper-card id="patientProfileData" heading="{{patientLabourData.lp.patientName}} ({{patientLabourData.lp.patientAge}} years old)" elevation="0">
								<div class="card-content">
									<div class="md-4">
										<table class="table table-responsive">
											<tr>
												<th>Admitted</th>
												<td>{{formatDateTime(patientLabourData.lp.dateEnrolled)}}</td>
											</tr>
											<tr>
												<th>LMP: </th>
												<td>{{formatDate(patientLabourData.lp.patientLMP)}}</td>
											</tr>
											<tr>
												<th>EDD: </th>
												<td>{{formatDate(patientLabourData.lp.patientEDD)}}</td>
											</tr>
											<tr>
												<th>Gestational Age: </th>
												<td>{{ weeksFromNow(patientLabourData.lp.patientLMP) }}</td>
											</tr>
											<tr>
												<th>Present Pregnancy</th>
												<td>{{patientLabourData.lp.presentPregnancy}}</td>
											</tr>
											<tr>
												<td colspan="2">
													<paper-button class="link" on-tap="doCloseDelivery" style="font-size: small;margin-left:0;padding-left: 0;">
														<iron-icon icon="icons:block" style="width: 16px;margin-right: 5px"></iron-icon>
														Close Management
													</paper-button>
												</td>
											</tr>
										</table>
									</div>
									<div class="md-4">
										<table class="table table-responsive">
											<tr>
												<th>Gravida</th>
												<td>{{patientLabourData.lp.gravida}}</td>
											</tr>
											<tr>
												<th>Parity</th>
												<td>{{patientLabourData.lp.parity}}</td>
											</tr>
											<tr>
												<th>Alive</th>
												<td>{{patientLabourData.lp.alive}}</td>
											</tr>
											<tr>
												<th>True/False Labour</th>
												<td>{{ checkData(patientMeasurementsData.labourSign) }} {{ formatDateTime(patientMeasurementsData.labourSignDate) }}</td>
											</tr>
											<tr>
												<th>Risk Factor</th>
												<td>{{patientAssessmentData.riskScore || 'N/A'}}</td>
											</tr>
										</table>
									</div>
								</div>
							</paper-card>
							<paper-tabs selected="{{selected}}" style="/*box-shadow: 0 8px 5px -5px rgba(20,20,20,0.3);*/border-bottom: 1px solid #d8d8d8;">
								<paper-tab>MEASUREMENTS</paper-tab>
								<paper-tab>PARTO-GRAPH</paper-tab>
								<paper-tab>RISK ASSESSMENT</paper-tab>
								<paper-tab>DELIVERY</paper-tab>
							</paper-tabs>
							<iron-pages id="profile-page" selected="{{selected}}">
								<div class="tab measurements">
									<fieldset>
										<legend>Most Recent Measurements/Recordings at {{ formatDateTime(patientMeasurementsData.dateTaken) }}</legend>
										<table class="table table-responsive table-striped">
											<tr>
												<th>Membranes Ruptured</th>
												<td>{{ joinMembranes(patientMeasurementsData.membranesRuptured) }}</td>
											</tr>
											<tr>
												<th>Presentation</th>
												<td>{{ checkData(patientMeasurementsData.fundalGrip) }}</td>
											</tr>
											<tr>
												<th>Descent</th>
												<td>{{replaceString(patientMeasurementsData.descent_1)}}</td>
											</tr>
											<tr>
												<th>Fetal Lie</th>
												<td>{{ checkData(patientMeasurementsData.fetalLie) }}</td>
											</tr>
											<tr>
												<th>Position of fetus</th>
												<td>{{ checkData(patientMeasurementsData.fetalPosition) }}</td>
											</tr>
											<tr>
												<th>Amniotic Fluid / Membranes</th>
												<td>{{replaceString(patientMeasurementsData.membranes_liquor)}}</td>
											</tr>
											<tr>
												<th>Fetal Skull Bone Moulding</th>
												<td>{{ checkData(patientMeasurementsData.moulding) }}</td>
											</tr>
											<tr>
												<th>Has the woman passed urine since the last measurement?</th>
												<td>{{ joinUrine(patientMeasurementsData.urine) }}</td>
											</tr>
											<tr>
												<th>Has oxytocin been administered during the current exam?</th>
												<td>{{ joinOxy(patientMeasurementsData.oxytocinated) }}</td>
											</tr>
											<tr>
												<th>Have other drugs or IV fluids been administered during the current exam?</th>
												<td>{{ joinDrugs(patientMeasurementsData.otherDrugs) }}</td>
											</tr>
											<tr>
												<th>Lab Tests Done</th>
												<td>{{ checkData(patientMeasurementsData.note) }}</td>
											</tr>
											<tr>
												<th>Emergency Signs</th>
												<td>{{ joinEmergString(patientMeasurementsData.emergencySigns) }}</td>
											</tr>
										</table>

									</fieldset>
									<fieldset>
										<legend>Most Recent Vitals</legend>
										<table class="table table-responsive table-striped">
											<tr>
												<th>Fetal Heart Rate</th>
												<td>{{ lastValue(vitalSigns, 'fetalHeartRate')}}</td>
											</tr>
											<tr>
												<th>Cervical Dilation (cms)</th>
												<td>{{ lastValue(vitalSigns, 'cervicalDilation') }}</td>
											</tr>
											<tr>
												<th>Station of the Fetal Head</th>
												<td>{{ lastValue(vitalSigns, 'fetalHeadStation')}}</td>
											</tr>
											<tr>
												<th>Number of Contractions</th>
												<td>{{lastValue(vitalSigns, 'contractionsRate') }}</td>
											</tr>
											<tr>
												<th>Duration of Contractions (seconds)</th>
												<td>{{lastValue(vitalSigns, 'contractionDuration') }}</td>
											</tr>
											<tr>
												<th>Blood Pressure</th>
												<td>{{ lastValue(vitalSigns, 'bloodPressure') }}</td>
											</tr>
											<tr>
												<th>Pulse</th>
												<td>{{lastValue(vitalSigns, 'pulse')}}</td>
											</tr>
											<tr>
												<th>Temperature (&deg;C)</th>
												<td>{{lastValue(vitalSigns, 'temperature') }}</td>
											</tr>
											<tr>
												<th>Respiration per Minute</th>
												<td>{{lastValue(vitalSigns, 'respiration') }}</td>
											</tr>
											<tr>
												<th>Sugar Level</th>
												<td>{{lastValue(vitalSigns, 'bloodGlucose') }}</td>
											</tr>
										</table>
									</fieldset>
									<paper-fab icon="add" title="Take new measurements" on-tap="newMeasurements"></paper-fab>
								</div>
								<div class="tab partograph">
									<highcharts-chart id="fhr" type="line" title="Fetal Heart Rate" data="{{formatChartData(vitalSigns, 'fetalHeartRate')}}" x-axis="{{xAxis}}" y-label="Fetal Heart Rate" x-label="Labour Time" tooltip-options="{{tooltipSettings}}" legend-options="{{ legendOptions }}" chart-options="{{ chartOptions }}"></highcharts-chart>
									<highcharts-chart id="cervicoGraph" type="line" title="CervicoGraph" data="{{ formatChartData(vitalSigns, cervicoGroup ) }}" x-axis="{{xAxis}}" y-label="Dilations" x-label="Labour Time" tooltip-options="{{tooltipSettings}}" legend-options="{{ legendOptions }}" chart-options="{{ chartOptions }}"></highcharts-chart>
									<highcharts-chart id="contractions" type="line" title="Contractions" data="{{ formatChartData(vitalSigns, contractionsGroup ) }}" x-axis="{{xAxis}}" y-label="Contractions" x-label="Labour Time" tooltip-options="{{tooltipSettings}}" legend-options="{{ legendOptions }}" chart-options="{{ chartOptions }}"></highcharts-chart>
									<highcharts-chart id="bpChart" type="line" title="Blood Pressure" data="{{ formatChartData(vitalSigns, 'bloodPressure' ) }}" x-axis="{{xAxis}}" y-label="Blood Pressure" x-label="Labour Time" tooltip-options="{{tooltipSettings}}" legend-options="{{ legendOptions }}" chart-options="{{ chartOptions }}"></highcharts-chart>
									<highcharts-chart id="pulseChart" type="line" title="Pulse" data="{{ formatChartData(vitalSigns, 'pulse' ) }}" x-axis="{{xAxis}}" y-label="Pulse" x-label="Labour Time" tooltip-options="{{tooltipSettings}}" legend-options="{{ legendOptions }}" chart-options="{{ chartOptions }}"></highcharts-chart>
									<highcharts-chart id="temperatureChart" type="line" title="Temperature" data="{{ formatChartData(vitalSigns, 'temperature' ) }}" x-axis="{{xAxis}}" y-label="Temperature" x-label="Labour Time" tooltip-options="{{tooltipSettings}}" legend-options="{{ legendOptions }}" chart-options="{{ chartOptions }}"></highcharts-chart>
									<highcharts-chart id="respirationChart" type="line" title="Respiration rate" data="{{ formatChartData(vitalSigns, 'respiration' ) }}" x-axis="{{xAxis}}" y-label="Respiration rate" x-label="Labour Time" tooltip-options="{{tooltipSettings}}" legend-options="{{ legendOptions }}" chart-options="{{ chartOptions }}"></highcharts-chart>
									<highcharts-chart id="glucoseChart" type="line" title="Glucose" data="{{ formatChartData(vitalSigns, 'bloodGlucose' ) }}" x-axis="{{xAxis}}" y-label="Glucose" x-label="Labour Time" tooltip-options="{{tooltipSettings}}" legend-options="{{ legendOptions }}" chart-options="{{ chartOptions }}"></highcharts-chart>

									<paper-fab icon="add" title="New Vital Readings" on-tap="newVitals"></paper-fab>
								</div>
								<div class="tab risk-assessment">
									<div>
										<table class="table table-responsive table-striped">
											<tr>
												<th>Date Taken</th>
												<th>Next Exam Time</th>
												<th>*</th>
											</tr>
											<template is="dom-repeat" items="{{patientAssessmentDatum}}">
												<tr>
													<td>{{formatDateTime(item.dateTaken)}}</td>
													<td>{{formatTime(item.nextDate)}}</td>
													<td><a>view</a></td>
												</tr>
											</template>
										</table>
									</div>
									<paper-fab icon="add" title="Take risk assessment" on-tap="newRiskAssessment"></paper-fab>
								</div>
								<div class="tab delivery">
									<div>
										<template is="dom-repeat" items="{{patientDeliveryDatum}}">
											<table class="table table-responsive table-striped table-margin">
												<tr>
													<th>Is baby alive?</th>
													<td>{{item.babyAlive}}</td>
												</tr>
												<tr>
													<th>Baby Date of Birth</th>
													<td>{{item.babyDOB}}</td>
												</tr>
												<tr>
													<th>Baby Time of Birth</th>
													<td>{{item.babyTOB}}</td>
												</tr>
												<tr>
													<th>Baby Sex</th>
													<td>{{item.babySex}}</td>
												</tr>
												<tr>
													<th>Baby Weight</th>
													<td>{{item.babyWeight}}</td>
												</tr>
												<tr>
													<th>Is the Mother Rh negative?</th>
													<td>{{item.motherRh}}</td>
												</tr>
												<tr>
													<th>Baby APGAR Score</th>
													<td>{{item.babyApgarScore}}</td>
												</tr>
												<tr>
													<th>Baby cried immediately after birth?</th>
													<td>{{item.babyCried}}</td>
												</tr>
												<tr>
													<th>Was Vitamin K administered?</th>
													<td>{{item.babyVitaminK}}</td>
												</tr>
												<tr>
													<th>Drugs administered to baby</th>
													<td>{{item.note}}</td>
												</tr>
												<tr>
													<th>Is mother alive?</th>
													<td>{{item.motherAlive}}</td>
												</tr>
												<tr>
													<th>Bleeding within normal units (<500ml)?</th>
													<td>{{item.bleeding}}</td>
												</tr>
												<tr>
													<th>Delivery Type</th>
													<td>{{joinDeliveryType(item.deliveryType)}}</td>
												</tr>
												<tr>
													<th>Was the placenta delivered completely?</th>
													<td>{{item.placentaDelivered}}</td>
												</tr>
												<tr>
													<th>Administered 10 units of Oxytocin?</th>
													<td>{{item.synto}}</td>
												</tr>
												<tr>
													<th>Were was the baby transferred?</th>
													<td>{{item.babyTransferedTo}}</td>
												</tr>
												<tr>
													<th>Pediatrician Name</th>
													<td>{{item.pediatricianName}}</td>
												</tr>
											</table>
										</template>
									</div>
									<paper-fab icon="add" title="Record delivery" on-tap="newDelivery"></paper-fab>
								</div>
							</iron-pages>
						</paper-material>
					</section>
					<section data-route="record-measurements" id="record-measurements-pg" ng-controller="RecordCtrl">
						<paper-material elevation="1">
							<a class="back-btn" onclick="clearThisFrm('record-measurements')" href$="{{baseUrl}}patient-profile/{{params.labourID}}">
								<paper-button class="link">
									<iron-icon icon="hardware:keyboard-backspace"></iron-icon>
									Back to Profile
								</paper-button>
							</a>
							<h2 class="page-title">Measurements</h2>
							<form is="iron-form" id="record-measurements" name="recordMeasurementsFrm" method="post" action="/">
								<div class="input-block">
									<label class="label-block">True Labour Signs: Begins irregularly but becomes regular and predictable.
										Felt in the lower back and sweeps around to the abdomen ina wave pattern. Continues no matter what
										the woman's level of activity. Increases in duration, frequency and intensity with the passage of
										time. Accompanied by 'show' (blood stained mucus discharge). Achieves cervical effacement and
										cervical dilation.</label>
									<label class="label-block">False Labour Signs: Begins irregularly and remain irregular. Often
										disappears with ambulation or sleep. Felt first abdominally and remains confined to the abdomen and
										groin. Does not increase in duration, frequency or intensity with the passage of time. Show absent.
										Does not achieve cervical effacement and cervical dilation.</label>
								</div>
								<div class="horizontal-section input-block">
									<label class="block">Select if patient is in true or false labour</label>
									<paper-radio-group name="labour_sign" id="labour_sign">
										<paper-radio-button name="True">True Labour</paper-radio-button>
										<paper-radio-button name="False">False Labour</paper-radio-button>
									</paper-radio-group>
								</div>
								<div class="horizontal-section input-block">
									<label class="block">Membranes ruptured?</label>
									<paper-radio-group name="membranes_ruptured" id="membranes_ruptured" ng-model="user.mr">
										<paper-radio-button name="Yes">Yes</paper-radio-button>
										<paper-radio-button name="No">No</paper-radio-button>
									</paper-radio-group>
								</div>
								<div ng-show="user.mr == 'Yes'">
									<paper-input always-float-label name="date_membrane_rupture" label="Date of membrane rupture" type="date"></paper-input>
									<paper-input always-float-label name="time_membrane_rupture" label="Time of membrane rupture" type="time"></paper-input>
								</div>

								<fieldset>
									<legend>Abdominal Palpation/Examination</legend>

									<div class="horizontal-section input-block">
										<label class="block">Presentation</label>
										<paper-radio-group name="fundal_grip" id="fundal_grip" ng-model="user.fg">
											<paper-radio-button name="Cephalic">Cephalic</paper-radio-button>
											<paper-radio-button name="Breech">Breech</paper-radio-button>
											<paper-radio-button name="Shoulder">Shoulder</paper-radio-button>
											<paper-radio-button name="Other">Any Other</paper-radio-button>
										</paper-radio-group>
									</div>
									<label class="input-block label-block">**If head is felt, please inform the doctor</label>
									<div class="horizontal-section input-block">
										<label class="block">Position of fetus</label>
										<paper-radio-group name="fetal_position" id="fetal_position">
											<paper-radio-button name="LOA (Left Occipital Anterior)" ng-show="isInArray(['Cephalic'], user.fg)">
												LOA (Left Occipital Anterior)
											</paper-radio-button>
											<paper-radio-button name="ROA (Right Occipital Anterior)" ng-show="isInArray(['Cephalic'], user.fg)">
												ROA (Right Occipital Anterior)
											</paper-radio-button>
											<paper-radio-button name="LOP (Left Occipital Posterior)" ng-show="isInArray(['Cephalic'], user.fg)">
												LOP (Left Occipital Posterior)
											</paper-radio-button>
											<paper-radio-button name="ROP (Right Occipital Posterior)" ng-show="isInArray(['Cephalic'], user.fg)">
												ROP (Right Occipital Posterior)
											</paper-radio-button>
											<paper-radio-button name="OA (Occipital Anterior)" ng-show="isInArray(['Cephalic'], user.fg)">OA
												(Occipital Anterior?)
											</paper-radio-button>
											<paper-radio-button name="OP (Occipital Posterior)" ng-show="isInArray(['Cephalic'], user.fg)">OP
												(Occipital Posterior?)
											</paper-radio-button>

											<paper-radio-button name="LSA (Left Sinciput Anterior)" ng-show="isInArray(['Breech', 'Shoulder'], user.fg)">
												LSA (Left Sinciput Anterior)
											</paper-radio-button>
											<paper-radio-button name="RSA (Right Sinciput Anterior)" ng-show="isInArray(['Breech', 'Shoulder'], user.fg)">
												RSA (Right Sinciput Anterior)
											</paper-radio-button>

											<paper-radio-button name="LSP (Left Sinciput Posterior)" ng-show="isInArray(['Breech', 'Shoulder'], user.fg)">
												LSP (Left Sinciput Posterior)
											</paper-radio-button>
											<paper-radio-button name="RSP (Right Sinciput Posterior)" ng-show="isInArray(['Breech', 'Shoulder'], user.fg)">
												RSP (Right Sinciput Posterior)
											</paper-radio-button>

											<paper-radio-button name="SA (Sinciput Anterior)" ng-show="isInArray(['Breech'], user.fg)">SA
												(Sinciput Anterior?)
											</paper-radio-button>
											<paper-radio-button name="SP (Sinciput Posterior)" ng-show="isInArray(['Breech'], user.fg)">SP
												(Sinciput Posterior?)
											</paper-radio-button>
										</paper-radio-group>
										<paper-input ng-show="user.fg == 'Other'" name="other_fundal_grip" label="If Presentation was 'any other' please explain here"></paper-input>

									</div>
									<div class="horizontal-section input-block">
										<label class="block">Fetal lie</label>
										<paper-radio-group name="fetal_lie" id="fetal_lie">
											<paper-radio-button name="Longitudinal">Longitudinal</paper-radio-button>
											<paper-radio-button name="Oblique">Oblique</paper-radio-button>
											<paper-radio-button name="Transverse">Transverse</paper-radio-button>
										</paper-radio-group>

									</div>

									<label class="input-block label-block">**If there is breech, shoulder or face presentation, inform the
										doctor</label>
									<div class="horizontal-section input-block">
										<label class="block">Descent</label>
										<paper-dropdown-menu no-animations name="descent_1" id="descent_1" auto-validate>
											<paper-menu class="dropdown-content">
												<paper-item>5: Completely above</paper-item>
												<paper-item>4: Sinciput high, Occiput easily felt</paper-item>
												<paper-item>3: Sinciput easily felt, Occiput felt</paper-item>
												<paper-item>2: Sinciput felt, Occiput just felt</paper-item>
												<paper-item>1: Sinciput felt, Occiput not felt</paper-item>
												<paper-item>0: none of head palpable</paper-item>
											</paper-menu>
										</paper-dropdown-menu>

										<!--<paper-radio-group name="descent_1" id="descent_1">
											<paper-radio-button name="Engaged">Engaged</paper-radio-button>
											<paper-radio-button name="Engaged">Engaged</paper-radio-button>
											<paper-radio-button name="Non_Engaged">Non-engaged</paper-radio-button>
										</paper-radio-group>-->
										<label class="input-block label-block">**If buttocks felt, inform the doctor</label>
									</div>
								</fieldset>

								<fieldset>
									<legend>Vaginal Examination</legend>
									<div class="horizontal-section input-block">
										<label class="block">
											<paper-input name="cervical_effacement" label="Cervical Effacement/Length" auto-validate pattern="^[1-9]\d*(\.\d+)?$"></paper-input>
										</label>
									</div>
									<div class="horizontal-section input-block">
										<label class="block">Cervical Position</label>
										<paper-dropdown-menu no-animations name="cervical_position" id="cervical_position">
											<paper-menu class="dropdown-content">
												<paper-item>Anterior</paper-item>
												<paper-item>Posterior</paper-item>
												<paper-item>Midline</paper-item>
											</paper-menu>
										</paper-dropdown-menu>

									</div>
									<div class="horizontal-section input-block">
										<label class="block">Membranes/Liquor
											<paper-radio-group name="membranes_liquor" id="membranes_liquor">
												<paper-radio-button name="Intact Membranes">Intact Membranes</paper-radio-button>
												<paper-radio-button name="Clear">Clear</paper-radio-button>
												<paper-radio-button name="Blood Stained">Blood Stained</paper-radio-button>
												<paper-radio-button name="Light Meconium Staining">Light Meconium Staining</paper-radio-button>
												<paper-radio-button name="Particulate Meconium Staining">Particulate Meconium Staining
												</paper-radio-button>
												<paper-radio-button name="Heavy Meconium Staining">Heavy Meconium Staining</paper-radio-button>
											</paper-radio-group>
										</label>
									</div>
									<div class="horizontal-section input-block">
										<label class="block">Moulding</label>
										<paper-dropdown-menu no-animations name="moulding" id="moulding">
											<paper-menu class="dropdown-content">
												<paper-item>0: no moulding</paper-item>
												<paper-item>+: sutures are apposed</paper-item>
												<paper-item>++: sutures overlapped but reducible</paper-item>
												<paper-item>+++: sutures overlapped and not reducible</paper-item>
											</paper-menu>
										</paper-dropdown-menu>

									</div>
									<div class="horizontal-section input-block">
										<label class="block">Caput</label>
										<paper-dropdown-menu no-animations name="caput" id="caput">
											<paper-menu class="dropdown-content">
												<paper-item>0: No Caput</paper-item>
												<paper-item>+: Small Caput</paper-item>
												<paper-item>++: Moderate</paper-item>
												<paper-item>+++: Large Caput</paper-item>
											</paper-menu>
										</paper-dropdown-menu>

									</div>
									<div class="horizontal-section input-block">
										<label class="block">Has the woman passed urine since the last measurement?</label>
										<paper-radio-group name="unrinated" id="urinated" ng-model="user.urinated">
											<paper-radio-button name="Yes">Yes</paper-radio-button>
											<paper-radio-button name="No">No</paper-radio-button>
										</paper-radio-group>
									</div>
									<div ng-show="user.urinated == 'Yes'">
										<paper-input name="urine_volume" label="Volume of urine passed (ml)?" pattern="^[1-9]\d*(\.\d+)?$" auto-validate></paper-input>
										<div class="horizontal-section input-block">
											<label class="block">Did the urine contain any of the following?</label>
											<div class="input-block">
												<paper-checkbox name="urine_content" value="Protein">Protein</paper-checkbox>
												<paper-checkbox name="urine_content" value="Albumin">Albumin</paper-checkbox>
												<paper-checkbox name="urine_content" value="Glucose">Glucose</paper-checkbox>
												<paper-checkbox name="urine_content" value="Blood">Blood</paper-checkbox>
											</div>
										</div>
									</div>
									<div class="horizontal-section input-block">
										<label class="block">Has oxytocin been administered during the current exam?</label>
										<paper-radio-group name="oxytocinated" id="oxytocinated" ng-model="user.oxytocinated">
											<paper-radio-button name="Yes">Yes</paper-radio-button>
											<paper-radio-button name="No">No</paper-radio-button>
										</paper-radio-group>
									</div>
									<div ng-show="user.oxytocinated == 'Yes'">
										<paper-input name="oxytocin_administered_ml" label="How many mls of Oxytocin were administered?" pattern="^[1-9]\d*(\.\d+)?$" auto-validate></paper-input>
										<paper-input name="oxytocin_administered_units" label="How many units of Oxytocin were administered?" pattern="^[1-9]\d*(\.\d+)?$" auto-validate></paper-input>
										<paper-input name="oxytocin_administered_per_minute" label="How many drops per minute were administered?" pattern="^[1-9]\d*(\.\d+)?$" auto-validate></paper-input>
									</div>
									<div class="horizontal-section input-block">
										<label class="block">Have other drugs or IV fluids been administered during the current
											exam?</label>
										<paper-radio-group name="other_drugs" id="other_drugs" ng-model="user.od">
											<paper-radio-button name="Yes">Yes</paper-radio-button>
											<paper-radio-button name="No">No</paper-radio-button>
										</paper-radio-group>
									</div>
									<paper-input name="other_drugs_name" ng-show="user.od == 'Yes'" label="What are the drugs or IV fluids been administered?" auto-validate></paper-input>
									<paper-input name="other_drugs_dose" ng-show="user.od == 'Yes'" label="Dose or quantity?"></paper-input>
									<paper-input name="other_drugs_frequency" ng-show="user.od == 'Yes'" label="Frequency of dosing"></paper-input>

									<paper-input name="note" label="What Lab tests were done?"></paper-input>
								</fieldset>

								<div class="horizontal-section input-block">
									<label class="block">Identify if the patient has any of the following emergency signs</label>
									<div class="input-block">
										<paper-checkbox ng-model="breathing" name="emergency_signs" value="difficulty_in_breathing">
											Difficulty in breathing
										</paper-checkbox>
										<paper-checkbox ng-model="shock" name="emergency_signs" value="shock">Shock</paper-checkbox>
										<paper-checkbox ng-model="vaginal" name="emergency_signs" value="vaginal_bleeding">Vaginal
											bleeding
										</paper-checkbox>
										<paper-checkbox ng-model="convulsions" name="emergency_signs" value="convulsions_or_unconsciousness">
											Convulsions or Unconsciousness
										</paper-checkbox>
										<paper-checkbox ng-model="prolapsed" name="emergency_signs" value="prolapsed_cord">Prolapsed cord
										</paper-checkbox>
										<paper-checkbox ng-model="fetal" name="emergency_signs" value="fetal_distress">Fetal distress
										</paper-checkbox>
									</div>
								</div>
								<div ng-show="breathing" class="breathing input-block">
									<label>The patient demonstrates the signs of <strong>difficulty in breathing</strong>. Please proceed
										with the following steps:</label>
									<ol>
										<li>Inform the doctor</li>
									</ol>
								</div>
								<div ng-show="shock" class="shock input-block">
									<label>The patient demonstrates the signs of <strong>shock</strong>. Please proceed with the following
										steps:</label>
									<ol>
										<li>Call for help</li>
										<li>Give oxygen @ 6-8 litres/minute by mask/cannula</li>
										<li>Do rapid initial assessment of the woman in the shock and assesses circulation (pulse, blood
											pressure, skin colour, temperature, mental state)
										</li>
										<li>Provide immediate management of shock</li>
										<li>Turn patient to her side to minimize risk of aspiration</li>
										<li>Keep the woman warm</li>
										<li>Elevate her legs to increase venous return</li>
										<li>Loosen tight clothing</li>
										<li>Start an IV infusion to replace ongoing fluid/blood loss (consider inserting 2 IV lines)</li>
										<li>Monitor vital signs (pulse, blood pressure, breathing) and skin temperature every 15minutes</li>
										<li>Collect blood for testing cross matching</li>
										<li>Assess airways patency by looking, listening and feeling the air through nostrils</li>
										<li>If the airway is not patent, perform 'Head tilt-Chin lift' Observe breathing. If the woman is
											not breathing
										</li>
										<li>Give 30 chest compression followed by 2 breaths @ 100 compression/minute</li>
										<li>Press sternum vertically to depress it by 4-5 cm</li>
										<li>Breaths should be delivered by bag and mask</li>
										<li>Each breath should be provided for one second and should raise the chest (avoid
											hyperinflation)
										</li>
										<li>If the woman is breathing</li>
										<li>Rapidly evaluate her vital signs (pulse, blood pressure, breathing)</li>
										<li>Prop on left side</li>
										<li>Give oxygen at 6-8 litres/minute</li>
										<li>Ensure airway is clear all the time</li>
										<li>Once stabilized manage accordingly</li>
										<li>Steps for catheterization:</li>
										<li>After routine hand wash put on sterile gloves</li>
										<li>Clean the vulva with wet cotton swabs soaked in centrimide solution</li>
										<li>Open the sterile pack of size 16, 18 Foley's catheter</li>
										<li>Separate the labia majora and insert the tip of Foley's catheter in the urinary meatus</li>
										<li>Push the catheter and connect the other end of the catheter to the urobag</li>
										<li>Check the flow of urine</li>
										<li>Inflate the bulb of the catheter with 10 ml normal saline</li>
										<li>Maintain and monitor the input/output chart</li>
									</ol>
								</div>
								<div ng-show="vaginal" class="vaginal input-block">
									<label>The patient displays signs for <strong>abnormal vaginal bleeding</strong>. Please proceed with
										the following steps:</label>
									<ol>
										<li>P/V should not be performed</li>
										<li>Inform the doctor, establish an intravenous line and start intravenous fluids (Ringer
											Lactate/Normal Saline)
										</li>
										<li>Arrange for blood for blood transfusion</li>
									</ol>
								</div>
								<div ng-show="convulsions" class="convulsions input-block">
									<label>The patient displays signs for <strong>convulsions</strong>. Please proceed with the following
										steps:</label>
									<ol>
										<li>Offer supportive care</li>
										<li>If the woman is unconscious, position her on her left lateral side to reduce the risk of
											aspiration (vomits and blood)
										</li>
										<li>Clean the mouth and nostrils by applying gentle suction and remove the secretions</li>
										<li>Remove any visible obstructions or foreign body from her mouth</li>
										<li>Keep a padded-mouth gag between the upper and lower jaw to prevent tongue bite</li>
										<li>Administer the first dose of Magnesium Sulphate injection
											<ol type="a">
												<li>Wash hands thoroughly with soap and dry</li>
												<li>Place woman into the left lateral position</li>
												<li>Maintain airways at all times</li>
												<li>Insert IV canula and give fluids (NS or RL) slowly (1L in 6-8h)</li>
												<li>Keep ready 10 ampoules of 50% Magnesium (1ampoule = 2ml = 1g)</li>
												<li>Prepare 2 syringes (10 ml syringe and 22 gauge needle) with 5 g (10 ml) of 50% Magnesium
													Sulphate solution. If Lignocaine is available, administer the Magnesium Sulphate solution with
													1ml 2% Lignocaine to each buttock to prevent pain associated with the injection
												</li>
												<li>Carefully clean the injection site with an alcohol wipe</li>
												<li>Give 5g (10ml) by DEEP IM injection in one buttock (upper outer quadrant) and give the same
													dose on the other buttock as well
												</li>
												<li>Cut the needle with hub cutter and dispose of the used syringe in a proper disposal box</li>
												<li>Record the drug administration and findings on the woman's record</li>
											</ol>
										</li>
									</ol>
								</div>
								<div ng-show="prolapsed" class="prolapsed input-block">
									<label>The patient demonstrates signs of <strong>prolapsed cord</strong>. Steps to follow:</label>
									<ol>
										<li>Assess the woman in labour often if the fetus is preterm or small for gestational age, if the
											fetal presenting part is not engaged, and if the membranes are ruptured
										</li>
										<li>Periodically evaluate FHR, especially right after rupture of membranes (spontaneous or surgical)
											and again in 5 to 10 minutes
										</li>
										<li>If prolapse cord is identified, notify the physician and prepare for emergency cesarean birth
										</li>
										<li>If the client is fully dilated, the most emergent delivery route may be vaginal. In this case,
											encourage the client to push and assist with the delivery as follows
										</li>
										<li>Lower the head of the bed and elevate the client's hips on a pillow, or place the client in the
											knee chest position to minimize pressure from the cord
										</li>
										<li>Assess cord pulsations constantly</li>
										<li>Gently wrap gauze soaked in sterile normal saline solution around the prolapsed cord</li>
									</ol>
								</div>
								<div ng-show="fetal" class="fetal input-block">
									<label>The patient demonstrates signs of <strong>fetal distress</strong>. Steps to follow:</label>
									<ol>
										<li>Check FHR every 15 minutes</li>
										<li>If the FHR remains below 120 or above 160 beats per minute after 30 minutes and the woman is in
											labour, then do the following:
											<ol type="a">
												<li>Inform the doctor</li>
												<li>Explain the situation to the family</li>
												<li>Start an IV line with Ringer Lactate</li>
												<li>Administer oxygen</li>
												<li>Keep the woman lying on her left side till further management</li>
											</ol>
										</li>
									</ol>
								</div>

								<div class="horizontal-section input-block">
									<label class="block">
										<paper-input name="examiner_name" label="Examiner's Name" auto-validate required></paper-input>
									</label>
								</div>

								<paper-button class="input-block" onclick="recordMeasurements(event)">Save</paper-button>
								<a class="input-block" onclick="clearFrm(event)" href$="{{baseUrl}}patient-profile/{{params.labourID}}">
									<paper-button>Cancel</paper-button>
								</a>
							</form>
						</paper-material>
					</section>
					<section data-route="record-vitals" id="record-vitals-pg" ng-controller="RecordVitalsCtrl">
						<paper-material elevation="1">
							<a class="back-btn" onclick="clearThisFrm('record-vitals')" href$="{{baseUrl}}patient-profile/{{params.labourID}}">
								<paper-button class="link">
									<iron-icon icon="hardware:keyboard-backspace"></iron-icon>
									Back to Profile
								</paper-button>
							</a>
							<h2 class="page-title">Record Vitals</h2>
							<!--<div class="input-block">
								<label class="label-block">True Labour Started At: </label>
							</div>-->
							<form is="iron-form" id="record-vitals" name="recordVitalsFrm" method="post" action="/">
								<paper-input name="fetal_heart_rate" ng-model="user.fhr" label="What is the current fetal heart rate?" pattern="^[0-9]*$" auto-validate></paper-input>
								<div class="fetal input-block" ng-show="user.fhr !== '' && (user.fhr > 160 || user.fhr < 110)">
									<label class="block"><strong>Warning! a fetal heart rate of more than 160 or less than 110 indicates
										fetal distress</strong></label>
									<label class="block">Suggested action:</label>
									<ol>
										<li>Inform the doctor</li>
										<li>Start CTG monitoring</li>
										<li>Administer oxygen</li>
										<li>Maternal position - left lateral position</li>
										<li>Intravenous fluid administration</li>
										<li>Oxytocin should be discontinued</li>
									</ol>
								</div>
								<paper-input name="cervical_dilation" label="What is the current cervical dilation (cms)?" pattern="^[1-9]\d*(\.\d+)?$" auto-validate></paper-input>
								<div class="horizontal-section input-block">
									<label class="block">Station of the fetal head (Descent?)</label>
									<paper-radio-group name="fetal_head_station" id="fetal_head_station">
										<paper-radio-button name="-3">-3</paper-radio-button>
										<paper-radio-button name="-2">-2</paper-radio-button>
										<paper-radio-button name="-1">-1</paper-radio-button>
										<paper-radio-button name="0">0</paper-radio-button>
										<paper-radio-button name="1">1</paper-radio-button>
										<paper-radio-button name="2">2</paper-radio-button>
										<paper-radio-button name="3">3</paper-radio-button>
									</paper-radio-group>
								</div>

								<paper-input name="contractions_rate" label="What is the current number of contractions per 10 minutes?" pattern="^[0-9]*$" auto-validate></paper-input>
								<paper-input name="contraction_duration" label="What is the current duration of a contraction (seconds)?" pattern="^[0-9]*$" auto-validate></paper-input>
								<paper-input-container always-float-label auto-validate attr-for-value="value">
									<label>Blood Pressure</label>
									<bp-input class="paper-input-input" name="blood_pressure" id="blood_pressure"></bp-input>
									<paper-input-error>Value invalid!</paper-input-error>
								</paper-input-container>
								<paper-input name="pulse" label="What is the current pulse?" pattern="^[0-9]*$" auto-validate></paper-input>
								<paper-input name="temperature" label="What is the current temperature (&deg;C)?" pattern="^[1-9]\d*(\.\d+)?$" auto-validate></paper-input>
								<paper-input name="respiration" label="What is the current respiration rate per minute?" pattern="^[0-9]*$" auto-validate></paper-input>
								<paper-input name="blood_glucose" label="What is the blood sugar level?" pattern="^[1-9]\d*(\.\d+)?$" auto-validate></paper-input>

								<!--<div class="horizontal-section input-block">
									<label class="block">What is the colour of the amniotic fluid?</label>
									<paper-radio-group name="amniotic_fluid_color" id="amniotic_fluid_color">
										<paper-radio-button name="Clear">Clear</paper-radio-button>
										<paper-radio-button name="Absent">Absent</paper-radio-button>
										<paper-radio-button name="Meconium_Stained">Meconium Stained</paper-radio-button>
										<paper-radio-button name="Blood_Stained">Blood Stained</paper-radio-button>
									</paper-radio-group>
								</div>-->
								<!--<div class="horizontal-section input-block">
									<label class="block">What is the current moulding of fetal skull bones?</label>
									<paper-radio-group name="skull_moulding" id="skull_moulding">
										<paper-radio-button name="0">0 - Bones are separated</paper-radio-button>
										<paper-radio-button name="1">1 - Bones just touching</paper-radio-button>
										<paper-radio-button name="2">2 - Bones overlapping</paper-radio-button>
										<paper-radio-button name="3">3 - Bones severely overlapping</paper-radio-button>
									</paper-radio-group>
								</div>-->


								<paper-button class="input-block" onclick="recordVitals(event)">Save</paper-button>
								<a class="input-block" onclick="clearFrm(event)" href$="{{baseUrl}}patient-profile/{{params.labourID}}">
									<paper-button>Cancel</paper-button>
								</a>
							</form>
						</paper-material>
					</section>
					<section data-route="risk-assessment" id="risk-assessment-pg" ng-controller="AssessmentCtrl">
						<paper-material elevation="1">
							<a class="back-btn" onclick="clearThisFrm('risk-assessment')" href$="{{baseUrl}}patient-profile/{{params.labourID}}">
								<paper-button class="link">
									<iron-icon icon="hardware:keyboard-backspace"></iron-icon>
									Back To Profile
								</paper-button>
							</a>
							<h2 class="page-title">Risk Assessment</h2>
							<form is="iron-form" id="risk-assessment" name="riskAssessmentFrm" method="post" action="/">
								<div class="risk-info">
									<label class="label-block">Risk Score: <%riskScore() + ageRisk + parityRisk%></label>
									<label class="label-block" ng-show="(riskScore() + ageRisk + parityRisk) < 4">The patient has a risk
										score < 4; and is NOT considered high risk</label>
									<label class="label-block" ng-show="(riskScore() + ageRisk + parityRisk) >= 4">This patient is
										<strong>**high risk**</strong></label>
									<input is="iron-input" type="hidden" value="<%riskScore() + ageRisk + parityRisk%>" name="risk_score">
								</div>
								<div ng-show="oAssessmentData.patientAge < 18 || oAssessmentData.patientAge > 35">
									<label class="label-block">Age: <%oAssessmentData.patientAge%> (Age is less than 18 or greater than
										35, +1)</label>
								</div>
								<div ng-show="oAssessmentData.parity == 'Primi Para (P1)' || oAssessmentData.parity == 'Multi Para (P5)'">
									<label class="label-block">Parity: <%oAssessmentData.parity%> (Primi or Multi Para, +1)</label>
								</div>
								<paper-input label="What is her height (cm)?" name="height" ng-model="height" auto-validate pattern="\d+\.?\d*"></paper-input>
								<div ng-show="height != '' && height < 145">
									<label class="label-block">(Height is less than 145cm: +1)</label>
								</div>
								<paper-input label="What is her weight (kg)?" name="weight" ng-model="weight" auto-validate pattern="\d+\.?\d*"></paper-input>
								<div ng-show="weight != '' && (weight < 45 || weight > 90)">
									<label class="label-block">(Weight is less than 45kg or more than 90kg: +3)</label>
								</div>
								<div class="if_previous_pregnancy">
									<div class="horizontal-section input-block">
										<label class="block">What is the outcome of her previous pregnancy?</label>
										<paper-radio-group name="previous_pregnancy" id="previous_pregnancy" ng-model="assess.pp">
											<paper-radio-button name="Normal_delivery">Normal delivery</paper-radio-button>
											<paper-radio-button name="Assisted_delivery">Assisted delivery</paper-radio-button>
											<paper-radio-button name="Cesarean">Cesarean</paper-radio-button>
											<paper-radio-button name="Still_birth">Still birth</paper-radio-button>
											<paper-radio-button name="Miscarriage">Miscarriage</paper-radio-button>
											<paper-radio-button name="Spontaneous_abortion">Spontaneous abortion</paper-radio-button>
										</paper-radio-group>
									</div>
									<div ng-show="assess.pp == 'Still_birth' || assess.pp == 'Miscarriage' || assess.pp == 'Spontaneous_abortion'">
										<label class="label-block">(Patient has high obstetrical history: +2)</label>
									</div>
									<div class="horizontal-section input-block">
										<label class="block">History of low birth weight?</label>
										<paper-radio-group name="birth_weight" id="birth_weight" ng-model="assess.bw">
											<paper-radio-button name="Yes">Yes</paper-radio-button>
											<paper-radio-button name="No">No</paper-radio-button>
										</paper-radio-group>
									</div>
									<div ng-show="assess.bw == 'Yes'">
										<label class="label-block">(History of low birth weight: +1)</label>
									</div>
									<div class="horizontal-section input-block">
										<label class="block">Has the patient experienced any of the following in previous
											pregnancies?</label>
										<div class="input-block">
											<paper-checkbox name="past_experience" ng-model="pc" value="pre_clampsia">Pre-eclampsia
											</paper-checkbox>
											<paper-checkbox name="past_experience" ng-model="ec" value="eclampsia">Eclampsia</paper-checkbox>
											<paper-checkbox name="past_experience" ng-model="usu" value="uterine_surgery">Uterine Surgery
											</paper-checkbox>
											<paper-checkbox name="past_experience" ng-model="ps" value="puerperal_sepsis">APH or PPH
												(Puerperal Sepsis)
											</paper-checkbox>
											<paper-checkbox name="past_experience" ng-model="pr" value="placenta_removal">Manual removal of
												placenta
											</paper-checkbox>
											<paper-checkbox name="past_experience" ng-model="an" value="anaemia">Anaemia (less than 6g%)
											</paper-checkbox>
											<paper-checkbox name="past_experience" ng-model="fa" value="febrile_ailment">Febrile ailment in
												pregnancy
											</paper-checkbox>
											<paper-checkbox name="past_experience" ng-model="hy" value="hypertension">Pregnancy associated
												with hypertension
											</paper-checkbox>
											<paper-checkbox name="past_experience" ng-model="mc" value="medical_condition">Medical condition
												with pregnancy (TB, diabetes, thyroid disorder, asthma)
											</paper-checkbox>
											<paper-checkbox name="past_experience" ng-model="ba" value="bleeding_abortion">Bleeding P/V (APH
												Abortion)
											</paper-checkbox>
											<paper-checkbox name="past_experience" ng-model="ap" value="abnormal_presentation">Abnormal
												presentation (apart from cephalic)
											</paper-checkbox>
											<paper-checkbox name="past_experience" ng-model="ma" value="maturity_age">Maturity < 37 weeks or
												Maturity > 45 weeks
											</paper-checkbox>
											<paper-checkbox name="past_experience" ng-model="prom" value="prom">PROM (Premature Rupture Of
												Membranes)
											</paper-checkbox>
											<paper-checkbox name="past_experience" ng-model="fd" value="fetal_distress">Fetal distress
											</paper-checkbox>
											<paper-checkbox name="past_experience" ng-model="pl" value="prolonged_labour">Prolonged labour >
												24 hours
											</paper-checkbox>
											<paper-checkbox name="past_experience" ng-model="usi" value="uterine_size">Uterine size < period
												of gestation
											</paper-checkbox>
											<paper-checkbox name="past_experience" ng-model="dh" value="dai_handling"> Traditional birth
												attendant / Outside interference
											</paper-checkbox>
										</div>
									</div>
								</div>
								<div class="horizontal-section input-block">
									<label class="block">Would you like to do?</label>
									<paper-radio-group name="monitoring" id="monitoring" ng-model="monitoring">
										<paper-radio-button name="Yes">Flag this patient for increased monitoring, Continue exam
										</paper-radio-button>
										<paper-radio-button name="No">Stop Exam</paper-radio-button>
									</paper-radio-group>
								</div>
								<paper-dropdown-menu no-animations ng-show="monitoring == 'Yes'" label="Take this assessment again in the next" name="next_exam">
									<paper-menu class="dropdown-content">
										<paper-item>Continuous</paper-item>
										<paper-item>5 minutes</paper-item>
										<paper-item>10 minutes</paper-item>
										<paper-item>20 minutes</paper-item>
										<paper-item>30 minutes</paper-item>
										<paper-item>60 minutes</paper-item>
										<paper-item>120 minutes</paper-item>
									</paper-menu>
								</paper-dropdown-menu>
								<paper-input label="Note" name="note"></paper-input>
								<paper-button class="input-block" onclick="riskAssessment(event)">Save</paper-button>
								<a class="input-block" onclick="clearFrm(event)" href$="{{baseUrl}}patient-profile/{{params.labourID}}">
									<paper-button>Cancel</paper-button>
								</a>
							</form>
						</paper-material>
					</section>
					<section data-route="record-delivery" id="record-delivery-pg" ng-controller="deliveryCtrl">
						<paper-material elevation="1">
							<a class="back-btn" onclick="clearThisFrm('record-delivery')" href$="{{baseUrl}}patient-profile/{{params.labourID}}">
								<paper-button class="link">
									<iron-icon icon="hardware:keyboard-backspace"></iron-icon>
									Back to Profile
								</paper-button>
							</a>
							<h2 class="page-title">Record Delivery</h2>
							<form is="iron-form" id="record-delivery" name="recordDeliveryFrm" method="post" action="/">
								<div class="horizontal-section input-block">
									<label class="block">Delivery type</label>
									<paper-radio-group name="delivery_type" id="delivery_type" required>
										<paper-radio-button name="Full_term_normal_vaginal_delivery">Full-term normal vaginal delivery
										</paper-radio-button>
										<paper-radio-button name="normal_vaginal_delivery_with_episiotomy">Normal vaginal delivery with
											episiotomy
										</paper-radio-button>
										<paper-radio-button name="vaginal_delivery_in_malpresentation">Vaginal delivery in malpresentation
										</paper-radio-button>
										<paper-radio-button name="assisted_delivery">Assisted delivery (forceps or vent use)
										</paper-radio-button>
										<paper-radio-button name="cesarean">Cesarean</paper-radio-button>
									</paper-radio-group>
								</div>
								<div class="horizontal-section input-block">
									<label class="block">Is the mother alive?</label>
									<paper-radio-group name="mother_" id="mother_alive" required>
										<paper-radio-button name="Yes">Yes</paper-radio-button>
										<paper-radio-button name="No">No</paper-radio-button>
									</paper-radio-group>
								</div>
								<div class="horizontal-section input-block">
									<label class="block">Is the baby alive?</label>
									<paper-radio-group name="baby_alive" id="baby_alive" required>
										<paper-radio-button name="Yes">Yes</paper-radio-button>
										<paper-radio-button name="No">No</paper-radio-button>
									</paper-radio-group>
								</div>
								<div class="horizontal-section input-block">
									<label class="block">Administered 10 units of Oxytocin?</label>
									<paper-radio-group name="synto" id="synto">
										<paper-radio-button name="Yes">Yes</paper-radio-button>
										<paper-radio-button name="No">No</paper-radio-button>
									</paper-radio-group>
								</div>
								<div class="horizontal-section input-block">
									<label class="block">Was the placenta delivered completely?</label>
									<paper-radio-group name="placenta_delivered" id="placenta_delivered" required>
										<paper-radio-button name="Yes">Yes</paper-radio-button>
										<paper-radio-button name="No">No</paper-radio-button>
									</paper-radio-group>
								</div>
								<div class="horizontal-section input-block">
									<label class="block">Bleeding within normal units (<500ml)?</label>
									<paper-radio-group name="bleed" id="bleed" ng-model="udev.bleeding" required>
										<paper-radio-button name="Yes">Yes</paper-radio-button>
										<paper-radio-button name="No">No</paper-radio-button>
									</paper-radio-group>
								</div>
								<paper-input name="bleed_volume" ng-show="udev.bleeding == 'No'" label="How much did the mother bleed?"></paper-input>
								<paper-input name="baby_dob" label="Date of birth" type="date" always-float-label required auto-validate></paper-input>
								<paper-input name="baby_tob" label="Time of birth" type="time" always-float-label required auto-validate></paper-input>
								<div class="horizontal-section input-block">
									<label class="block">Baby cried immediately after birth?</label>
									<paper-radio-group name="baby_cried" id="baby_cried" required>
										<paper-radio-button name="Yes">Yes</paper-radio-button>
										<paper-radio-button name="No">No</paper-radio-button>
									</paper-radio-group>
								</div>
								<div class="horizontal-section input-block">
									<label class="block">Sex of the baby</label>
									<paper-radio-group name="baby_sex" id="baby_sex">
										<paper-radio-button name="Female">Female</paper-radio-button>
										<paper-radio-button name="Male">Male</paper-radio-button>
										<paper-radio-button name="Other">Other</paper-radio-button>
									</paper-radio-group>
								</div>
								<paper-input name="baby_apgar_score" label="APGAR score"></paper-input>
								<paper-input name="baby_weight" label="Weight (kg)" pattern="\d+\.?\d*" auto-validate required></paper-input>
								<div class="horizontal-section input-block">
									<label class="block">Was Vitamin K administered?</label>
									<paper-radio-group name="baby_vitamin_k" id="baby_vitamin_k" required>
										<paper-radio-button name="Yes">Yes</paper-radio-button>
										<paper-radio-button name="No">No</paper-radio-button>
									</paper-radio-group>
								</div>
								<div class="horizontal-section input-block">
									<label class="block">Is the mother Rh negative?</label>
									<paper-radio-group name="mother_rhesus" id="mother_rhesus">
										<paper-radio-button name="Yes">Yes</paper-radio-button>
										<paper-radio-button name="No">No</paper-radio-button>
									</paper-radio-group>
								</div>
								<paper-input name="note" label="If drugs were administered to the baby then mention"></paper-input>
								<div class="horizontal-section input-block">
									<label class="block">Were was the baby transferred?</label>
									<paper-radio-group name="transfer" id="transfer">
										<paper-radio-button name="Out">Transfer out</paper-radio-button>
										<paper-radio-button name="NICU">NICU</paper-radio-button>
									</paper-radio-group>
								</div>
								<paper-input name="pediatrician_name" label="Pediatrician's name" required auto-validate></paper-input>
								<paper-input name="delivery_comment" label="Comment" required auto-validate></paper-input>
								<paper-button class="input-block" onclick="recordDelivery(event)">Save</paper-button>
								<a class="input-block" href$="{{baseUrl}}patient-profile/{{params.labourID}}">
									<paper-button>Cancel</paper-button>
								</a>
							</form>
						</paper-material>
					</section>
					<section data-route="search-labour">
						<paper-material elevation="1">
							<form is="iron-form" method="post" action="/">
								<paper-input name="patientID" id="emrId" label="Search by Patient EMR ID"></paper-input>
								<paper-button icon="search">FIND</paper-button>
							</form>

						</paper-material>

					</section>
				</iron-pages>
			</div>
		</paper-scroll-header-panel>
	</paper-drawer-panel>
	<paper-toast id="toast" text="{{dataInfo}}" duration="2000">
		<span class="toast-hide-button" role="button" tabindex="0" style="color: #4e82c2;margin: 10px" onclick="app.$.toast.hide()">Close</span>
	</paper-toast>

	<paper-dialog id="dialog" modal>
		<h2>Help</h2>
		<p>Help Data is not available at the moment.</p>
		<div class="buttons">
			<paper-button class="link" dialog-dismiss>Ok, Got it</paper-button>
		</div>
	</paper-dialog>
	<paper-dialog id="appDialog" modal>
		<h2>Message</h2>
		<p>{{messageInfo}}</p>
		<div class="buttons">
			<paper-button class="link" dialog-dismiss>Close</paper-button>
		</div>
	</paper-dialog>

	<paper-dialog id="backdrop" modal with-backdrop style="display: flex;flex-direction: column;align-items: center;justify-content: center;width:75px;height:75px;border-radius:50px">
		<paper-spinner active></paper-spinner>
	</paper-dialog>
	<!-- Uncomment next block to enable Service Worker support (1/2) -->
	<!--<paper-toast id="caching-complete" duration="6000" text="Caching complete! This app will work offline.">
	</paper-toast>

	<platinum-sw-register auto-register clients-claim skip-waiting base-uri="../bower_components/platinum-sw/bootstrap" on-service-worker-installed="displayInstalledToast">
		<platinum-sw-cache default-cache-strategy="fastest" cache-config-file="cache-config.json">
		</platinum-sw-cache>
	</platinum-sw-register>-->
</template>
<!-- build:js scripts/app.js -->
<script src="scripts/app.js?t=<?= time() ?>"></script>
<!-- endbuild-->
</body>
</html>
