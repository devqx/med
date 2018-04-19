/**
 * Created by emnity on 1/29/16.
 */

var vars = {
	selected: 0
};
var dbName = 'labourMgt';
window.addEventListener('WebComponentsReady', function () {
	var app = document.querySelector('template#app');

	$.getJSON("/api/get_antenatal_vars.php", function (data) {
		app.selected = vars.selected;
		app.Gravida = _.map(data.gravida);
		app.Para = data.parity;
		app.Alive = data.general;
		app.Abortions = data.general;
		app.Pregnancies = data.pregnancies;
	});

	document.querySelector('#fundal_grip').addEventListener('paper-radio-group-changed', function (evt) {
		resetFetalPosition();
	});

	app.assessmentTakeDate = function (item) {
		if (item.length === 0) {
			return "n/a";
		}
		return app.formatDateTime(item[item.length - 1].dateTaken);
	};
	app.assessmentNextDate = function (item) {
		if (item.length === 0) {
			return "n/a";
		}
		return app.formatDateTime(item[item.length - 1].nextDate);
	};

	app.newMeasurements = function () {
		page.redirect('/record-measurements/' + app.params.labourID);
	};

	app.newVitals = function () {
		page.redirect('/record-vitals/' + app.params.labourID);
	};
	app.newRiskAssessment = function () {
		page.redirect('/risk-assessment/' + app.params.labourID);
	};
	app.newDelivery = function () {
		page.redirect('/record-delivery/' + app.params.labourID);
	};

	app.formatDateTime = function (dt) {
		if (_.includes([null, undefined, ''], dt)) {
			return '- -';
		}
		return moment(dt, 'YYYY-MM-DD HH:mm:ss').format('Do MMM, YYYY h:mm a');
	};
	app.subDate= function (date, interval) {
		var intervalParts = interval.split(" ");
		return moment(date, 'YYYY-MM-DD HH:mm:ss').subtract(parseInt(intervalParts[0]), intervalParts[1]).format('YYYY-MM-DD HH:mm:ss');
	};

	app.subDateFromNow = function (date) {
		return moment(date, 'YYYY-MM-DD HH:mm:ss').fromNow(true).weeks();
	};

	app.weeksFromNow = function (date) {
		// return moment(date, "YYYY-MM-DD").week()
		return moment().diff(moment(date), 'weeks')+ 'weeks';
	};

	app.formatDate = function (dt) {
		return moment(dt, 'YYYY-MM-DD').format('Do MMM, YYYY');
	};
	app.formatTime = function (dt) {
		return moment(dt, 'YYYY-MM-DD HH:mm:ss').format('h:mm a');
	};

	app.bPress = function (s) {
		if (jQuery.isPlainObject(s)) {
			return s.systolic.value + ' / ' + s.diastolic.value;
		}
	};
	var items_ = {};

	app.lastValue = function (obj, type) {
		items_[type] = [];
		_.forEach(obj, function (item) {
			if(!_.isNull(item[type])){
				items_[type].push(item[type]);
			}
		});

		if(_.isPlainObject(_.last(items_[type]))){
			if(type === 'bloodPressure'){
				return app.bPress(_.last(items_[type]));
			}
			return _.last(items_[type]).value;
		}
		return '- -';
	};

	app.checkData = function (s) {
		if (_.includes([null, undefined, ''], s)) {
			return '- -';
		}
		return s;
	};
	app.replaceString = function (s) {
		s = app.checkData(s);
		return (s) ? s.replace('_', ' ') : s;
	};
	app.joinMembranes = function (s) {
		if (s instanceof Object) {
			return moment(s.date, 'YYYY-MM-DD HH:mm:ss').format('Do MMM, YYYY') + ' ' + moment(s.time, 'HH:mm:ss').format('h:mm a');
		} else {
			s = app.checkData(s);
			return s;
		}
	};
	app.joinUrine = function (s) {
		if (s instanceof Object) {
			var contentStr = (_.isArray(s.content)) ? s.content.join(", ") : s.content;
			return 'Volume: ' + s.volume + 'mls, Content: ' + contentStr;
		} else {
			s = app.checkData(s);
			return s;
		}
	};
	app.joinOxy = function (s) {
		if (s instanceof Object) {
			var s_ = s.ml + 'ml of Oxytocin administered; ';
			s_ = s_ + s.unit + ' Units of Oxytocin administered; ';
			s_ = s_ + s.perMinute + ' Drops per minute of Oxytocin administered';
			return s_;
		} else {
			s = app.checkData(s);
			return s;
		}
	};
	app.joinEmergString = function (s) {
		if (s instanceof Array) {
			var e = [];
			for (var i = 0; i < s.length; i++) {
				e.push(ucFirstAllWords(s[i].replace('_', ' ').replace('_', ' ')));
			}
			return e.join(', ');
		} else {
			s = app.checkData(s);
			return s;
		}
	};
	app.joinDrugs = function (s) {
		if (_.isObject(s)) {
			return s.dose + " of " + s.name + ' given ' + s.frequency;
		}
		s = app.checkData(s);
		return s;
	};
	app.joinDeliveryType = function (s) {
		if (s) {
			var x = s.split('_');
			var e = [];
			for (var i = 0; i < x.length; i++) {
				e.push(x[i]);
			}
			return ucFirstAllWords(e.join(' '));
		} else {
			return s;
		}
	};

	//Close Delivery
	app.doCloseDelivery = function () {
		if (window.confirm("Are you sure to close the labour instance?")) {
			var transaction = db.transaction(['labourEnrollment'], 'readwrite');
			var objectStore = transaction.objectStore('labourEnrollment');
			var request = objectStore.get(parseInt(app.params.labourID));
			request.onsuccess = function () {
				var data = request.result;
				data.dateClosed = moment(new Date()).format('YYYY-MM-DD HH:mm:ss');
				data.labourClosed = true;
				console.log(data);
				var updateRequest = objectStore.put(data);
				updateRequest.onsuccess = function () {
					app.dataInfo = 'Patient delivery closed!';
					document.querySelector('#toast').open();
					console.log('updated');
					app.syncData();
					page.redirect('/patients');
				};
			};
		}
	};

	app.lookUpPatient = function () {
		var patient = null;
		var pid = document.querySelector('#emrId').value;
		app.$.backdrop.open();
		jQuery.getJSON("/api/get_antenatal_patients.php", {q: pid, sex: 'female', mode: 'single'})
			.done(function (data) {
				console.log(data);
				//should always get only one data
				if (data.length > 1) {
					// app.dataInfo = 'Search did not find a UNIQUE record';
					// document.querySelector('#toast').open();
					app.messageInfo = 'Multiple results returned';
					app.$.appDialog.open();
				} else if (data.length === 1) {
					patient = data[0];
					app.patientName = patient.fullname;
					app.patientDOB = patient.date_of_birth;
					app.patientBloodGroup = patient.bloodgroup;
					if (patient.antenatal) {
						app.patientHusband = patient.antenatal.babyFatherName;
						app.patientGravida = patient.antenatal.gravida - 1;
						app.patientPara = parseInt(patient.antenatal.para);
						app.patientAlive = patient.antenatal.alive;
						app.patientAbortions = patient.antenatal.abortions;
						app.patientLMP = patient.antenatal.lmpDate;
					}

				} else {
					// app.dataInfo = 'Search did not find ANY record';
					// document.querySelector('#toast').open();
					app.messageInfo = 'Search did not find ANY record';
					app.$.appDialog.open();

				}
				app.$.backdrop.close();

			}).error(function () {
			// app.dataInfo = 'A Server error occurred';
			// document.querySelector('#toast').open();

			app.messageInfo = 'A Server error occurred';
			app.$.appDialog.open();
			app.$.backdrop.close();
		});
	};

	document.getElementById('enroll-patient').addEventListener('iron-form-submit', doEnrollPatient);
	document.getElementById('record-measurements').addEventListener('iron-form-submit', doRecordMeasurements);
	document.getElementById('record-vitals').addEventListener('iron-form-submit', doRecordVitals);
	document.getElementById('record-delivery').addEventListener('iron-form-submit', doRecordDelivery);
	document.getElementById('risk-assessment').addEventListener('iron-form-submit', doRiskAssessment);

	angular.bootstrap(wrap(document), ['polymer-labour']);
});

function ucFirstAllWords(str) {
	var pieces = str.split(' ');
	for (var i = 0; i < pieces.length; i++) {
		var j = pieces[i].charAt(0).toUpperCase();
		pieces[i] = j + pieces[i].substr(1);
	}
	return pieces.join(' ');
}

angular
	.module('polymer-labour', ['ng-polymer-elements', 'indexedDB'], function ($interpolateProvider) {
		$interpolateProvider.startSymbol('<%');
		$interpolateProvider.endSymbol('%>');
	})
	.config(function ($indexedDBProvider) {
		$indexedDBProvider.connection(dbName);
	})
	.controller('AssessmentCtrl', ['$scope', '$indexedDB', '$timeout', function ($scope, $indexedDB, $timeout) {
		$scope.assess = {};
		$scope.riskScore = function (_e) {
			$scope.riskScore_ = 0;
			if ($scope.height !== '' && $scope.height < 145) {
				$scope.riskScore_ = $scope.riskScore_ + 1;
			}
			if ($scope.weight !== '' && ($scope.weight < 45 || $scope.weight > 90)) {
				$scope.riskScore_ = $scope.riskScore_ + 3;
			}
			if (typeof _e !== 'undefined') {
				console.log(_e);
				$scope.riskScore_ = $scope.riskScore_ + parseInt(_e);
				console.log('score3: ' + $scope.riskScore_);
			}
			if ($scope.assess.pp == 'Still_birth' || $scope.assess.pp == 'Miscarriage' || $scope.assess.pp == 'Spontaneous_abortion') {
				$scope.riskScore_ = $scope.riskScore_ + 2;
			}
			if ($scope.assess.bw == 'Yes') {
				$scope.riskScore_ = $scope.riskScore_ + 1;
			}
			if ($scope.pc) {
				$scope.riskScore_ = $scope.riskScore_ + 2;
			}
			if ($scope.ec) {
				$scope.riskScore_ = $scope.riskScore_ + 2;
			}
			if ($scope.usu) {
				$scope.riskScore_ = $scope.riskScore_ + 2;
			}
			if ($scope.ps) {
				$scope.riskScore_ = $scope.riskScore_ + 2;
			}
			if ($scope.pr) {
				$scope.riskScore_ = $scope.riskScore_ + 2;
			}
			if ($scope.an) {
				$scope.riskScore_ = $scope.riskScore_ + 3;
			}
			if ($scope.fa) {
				$scope.riskScore_ = $scope.riskScore_ + 1;
			}
			if ($scope.hy) {
				$scope.riskScore_ = $scope.riskScore_ + 2;
			}
			if ($scope.mc) {
				$scope.riskScore_ = $scope.riskScore_ + 2;
			}
			if ($scope.ba) {
				$scope.riskScore_ = $scope.riskScore_ + 2;
			}
			if ($scope.ap) {
				$scope.riskScore_ = $scope.riskScore_ + 2;
			}
			if ($scope.ma) {
				$scope.riskScore_ = $scope.riskScore_ + 2;
			}
			if ($scope.prom) {
				$scope.riskScore_ = $scope.riskScore_ + 2;
			}
			if ($scope.fd) {
				$scope.riskScore_ = $scope.riskScore_ + 2;
			}
			if ($scope.pl) {
				$scope.riskScore_ = $scope.riskScore_ + 2;
			}
			if ($scope.usi) {
				$scope.riskScore_ = $scope.riskScore_ + 2;
			}
			if ($scope.dh) {
				$scope.riskScore_ = $scope.riskScore_ + 2;
			}
			return $scope.riskScore_;
		};

		$scope.oAssessmentData = {};
		$scope._getAssessment = function (id) {
			var lid = parseInt(id);
			$indexedDB.openStore('labourEnrollment', function (labourEnrollment) {
				labourEnrollment.findBy('idxLabourID', lid).then(function (data) {
					if (data.patientAge < 18 || data.patientAge > 35) {
						$scope.ageRisk = 1;
					}
					if (data.parity == 'Primi Para (P1)' || data.parity == 'Multi Para (P5)') {
						$scope.parityRisk = 1;
					}
					$scope.oAssessmentData = data;
				});
			});
		};

		$scope.resetAssMe = function () {
			$scope.assess = {
				pp: '',
				bw: ''
			};
			$timeout(function () {
				$scope.$apply();
			}, 1);
		};
		$scope.resetAssMe();
	}])
	.controller('RecordCtrl', ['$scope', '$timeout', function ($scope, $timeout) {
		$scope.user = {};

		$scope.resetMe = function () {
			$scope.user = {
				mr: '',
				fg: '',
				urinated: '',
				oxytocinated: '',
				od: ''
			};
			$timeout(function () {
				$scope.$apply();
			}, 1);
		};

		$scope.isInArray = function (arr, item) {
			return _.includes(arr, item)
		};
		$scope.resetMe();
	}])
	.controller('RecordVitalsCtrl', ['$scope', '$timeout', function ($scope, $timeout) {
		$scope.user = {};
		$scope.resetMe = function () {
			$scope.user = {
				mr: '',
				fg: '',
				fhr: '',
				urinated: '',
				oxytocinated: '',
				od: ''
			};
			$timeout(function () {
				$scope.$apply();
			}, 1);
		};
		$scope.resetMe();
	}])
	.controller('deliveryCtrl', ['$scope', '$timeout', function ($scope, $timeout) {
		$scope.udev = {};
		$scope.resetDelivery = function () {
			$scope.udev = {bleeding: ''};
			$timeout(function () {
				$scope.$apply();
			}, 1);
		};
		$scope.resetDelivery();
	}]);


//Enroll Patient
function enrollPatient(event) {
	Polymer.dom(event).localTarget.parentElement.submit();
}

function doEnrollPatient(event) {
	var transaction = db.transaction(['labourEnrollment'], 'readwrite');
	var objectStore = transaction.objectStore('labourEnrollment');
	var patientData_ = event.detail;
	patientData_.dateEnrolled = moment(new Date()).format('YYYY-MM-DD HH:mm:ss');
	patientData_.dateClosed = null;
	patientData_.labourClosed = false;
	patientData_.trueLabourTime = null;
	patientData_.patientID = parseInt(event.detail.patientID) || Math.floor(Date.now() / 1000); //i'm not sure this will scale
	patientData_.patientBloodGroup = document.querySelector('#bloodGroup').selected;
	patientData_.patientEDD = moment(event.detail.patientLMP, 'YYYY-MM-DD').add(40, 'weeks').format('YYYY-MM-DD');
	var now = moment(new Date());
	patientData_.patientAge = now.diff(moment(event.detail.patientDOB), 'year');
	patientData_.assessment = [];
	var request = objectStore.add(patientData_);
	request.onsuccess = function (data) {
		console.log('success');
		//app.push('patientLabourDatum', patientData_);
		console.log(app.patientLabourDatum);
		app.patientLabourDatum.push(patientData_);
		_labourPatients.push(patientData_);
		app.dataInfo = 'Patient enrolled successfully!';
		document.querySelector('#toast').open();
		document.querySelector('#enroll-patient').reset();
		page.redirect('/patient-profile/' + parseInt(data.target.result));
	};
	request.onerror = function (error) {
		console.log(error);
		app.dataInfo = 'Patient has already been enrolled into the labour management';
		document.querySelector('#toast').open();
	};
}

var _labourPatients = [];
//all patients
var db;
var oReq;
function __getPatientDatum(callback) {
	var patients = [];
	jQuery.getJSON('/api/get_labour_enrollments.php', function (data) {
		for (var i = 0; i < data.length; i++) {
			var patient = {};
			patient.patientName = data[i].patient.fullname;
			patient.babyFatherName = data[i].babyFatherName;
			patient.patientDOB = data[i].patient.dateOfBirth;
			patient.gravida = app.Gravida[data[i].gravida];
			patient.parity = app.Para[data[i].para];
			patient.alive = app.Alive[data[i].alive];
			patient.presentPregnancy = app.Pregnancies[parseInt(data[i].currentPregnancy) - 1];
			patient.abortions = app.Abortions[data[i].abortions];
			patient.patientLMP = data[i].lmpDate;
			patient.dateEnrolled = moment(data[i].enrolledOn).format('YYYY-MM-DD HH:mm:ss');
			patient.dateClosed = null;
			patient.labourClosed = !Boolean(data[i].active);
			patient.patientID = parseInt(data[i].patient.patientId);
			patient.patientBloodGroup = data[i].patient.bloodGroup;
			patient.patientEDD = moment(data[i].lmpDate, 'YYYY-MM-DD').add(40, 'weeks').format('YYYY-MM-DD');
			patient.trueLabourTime = data[i].patient.trueLabourTime || null;
			var now = moment(new Date());
			patient.patientAge = now.diff(moment(data[i].patient.dateOfBirth), 'year');
			patient.assessment = [];
			patients.push(patient);
		}
	}).done(function () {
		console.log("done...1...");
		oReq = window.indexedDB.open(dbName);
		oReq.onsuccess = function (evt) {
			console.log("opened database...");
			db = evt.target.result;
			var transaction = db.transaction(['labourEnrollment'], 'readwrite');
			var i = 0;
			var itemStore = transaction.objectStore("labourEnrollment");
			putNext();
			function putNext() {
				if (i < patients.length) {
					itemStore.add(patients[i]).onsuccess = putNext;
					++i;
				} else { // complete
					console.log('populate complete');
					//callback();
				}
			}
		};
		oReq.onerror = function (evt) {
			console.error(evt);
		}
	}).done(function () {
		oReq = window.indexedDB.open(dbName);
		oReq.onsuccess = function (evt) {
			db = evt.target.result;
			var transaction = db.transaction(['labourEnrollment'], 'readwrite');
			_labourPatients = [];
			transaction.objectStore('labourEnrollment').index('patientID').openCursor().onsuccess = function (e) {
				var cursor = e.target.result;
				if (cursor && cursor.value.labourClosed === false) {
					cursor.value.assessment = __getPatientAssessments(cursor.value.labourID, true);
					cursor.update(cursor.value);
					_labourPatients.push(cursor.value);
					cursor.continue();
				}
			};
			transaction.oncomplete = function () {
				console.info("transaction complete");
				// console.info(_labourPatients);
				app.patientLabourDatum = _labourPatients;
			};
		};
		oReq.onerror = function (event) {
			console.error(event);
		}
	});
}

//patient profile
function __getPatientData(lid) {
	var _patientLabourData = {};
	var db;
	var oReq = window.indexedDB.open(dbName);
	app.detailView = true;

	oReq.onsuccess = function (evt) {
		db = evt.target.result;
		var transaction = db.transaction(['labourEnrollment'], 'readwrite');
		var _getLabourPatient = transaction.objectStore('labourEnrollment');
		var request = _getLabourPatient.get(parseInt(lid));
		request.onsuccess = function () {
			_patientLabourData.lp = request.result;
		};
		setTimeout(function () {
			app.patientLabourData = _patientLabourData;
			// return _patientLabourData;
		}, 200);

	};
}

//Record Measurements
function recordMeasurements(event) {
	Polymer.dom(event).localTarget.parentElement.submit();
}

function doRecordMeasurements(event) {
	var patientMeasurementsData_ = {};
	patientMeasurementsData_.labourID = parseInt(app.params.labourID);
	patientMeasurementsData_.labourSign = document.querySelector('#labour_sign').selected;

	var trueLabourTime = moment(new Date()).format('YYYY-MM-DD HH:mm:ss');

	if (patientMeasurementsData_.labourSign == 'True') {
		// prevent this value from being over-written if it already has a value?
		if (app.patientLabourData.lp.trueLabourTime === null) {
			patientMeasurementsData_.labourSignDate = trueLabourTime;
			//start committing changes to db
			oReq = window.indexedDB.open(dbName);
			oReq.onsuccess = function (evt) {
				db = evt.target.result;
				var transaction = db.transaction(['labourEnrollment'], 'readwrite');
				//_labourPatients = [];
				transaction.objectStore('labourEnrollment').index('patientID').openCursor().onsuccess = function (e) {
					var cursor = e.target.result;
					if (cursor && (cursor.value.trueLabourTime === null || cursor.value.trueLabourTime === undefined )) {
						cursor.value.trueLabourTime = trueLabourTime;
						cursor.update(cursor.value);
						//_labourPatients.push(cursor.value);
						cursor.continue();
					}
				};
			};
			//end committing changes to db
		} else {
			patientMeasurementsData_.labourSignDate = app.patientLabourData.lp.trueLabourTime;
		}

	} else {
		patientMeasurementsData_.labourSignDate = null;
	}
	var mr_ = document.querySelector('#membranes_ruptured').selected;
	if (mr_ == 'Yes') {
		patientMeasurementsData_.membranesRuptured = {
			'date': event.detail.date_membrane_rupture,
			'time': event.detail.time_membrane_rupture
		};
	} else {
		patientMeasurementsData_.membranesRuptured = (mr_ == 'No') ? 'No' : null;
	}

	var fg_ = document.querySelector('#fundal_grip').selected;
	if (fg_ == 'None') {
		patientMeasurementsData_.fundalGrip = event.detail.other_fundal_grip;
	} else {
		patientMeasurementsData_.fundalGrip = fg_ || null;
	}

	patientMeasurementsData_.fetalLie = document.querySelector('#fetal_lie').selected || null;
	patientMeasurementsData_.fetalPosition = document.querySelector('#fetal_position').selected || null;
	patientMeasurementsData_.descent_1 = document.querySelector('#descent_1').value || null;
	patientMeasurementsData_.cervical_effacement = event.detail.cervical_effacement || null;
	patientMeasurementsData_.cervical_position = document.querySelector('#cervical_position').value || null;
	patientMeasurementsData_.membranes_liquor = document.querySelector('#membranes_liquor').selected || null;
	patientMeasurementsData_.moulding = document.querySelector('#moulding').value || null;
	patientMeasurementsData_.caput = document.querySelector('#caput').value || null;

	var u_ = document.querySelector('#urinated').selected;
	if (u_ == 'Yes') {
		patientMeasurementsData_.urine = {
			'volume': event.detail.urine_volume,
			'content': event.detail.urine_content
		};
	} else {
		patientMeasurementsData_.urine = (u_ == 'No') ? 'No' : null;
	}

	var oxy_ = document.querySelector('#oxytocinated').selected;
	if (oxy_ == 'Yes') {
		patientMeasurementsData_.oxytocinated = {
			'ml': event.detail.oxytocin_administered_ml,
			'unit': event.detail.oxytocin_administered_units,
			'perMinute': event.detail.oxytocin_administered_per_minute
		};
	} else {
		patientMeasurementsData_.oxytocinated = (oxy_ == 'No') ? 'No' : null;
	}
	var od_ = document.querySelector('#other_drugs').selected;
	if (od_ == 'Yes') {
		patientMeasurementsData_.otherDrugs = {
			name: event.detail.other_drugs_name,
			dose: event.detail.other_drugs_dose,
			frequency: event.detail.other_drugs_frequency
		};
	} else {
		patientMeasurementsData_.otherDrugs = (od_ == 'No') ? 'No' : null;
	}
	patientMeasurementsData_.emergencySigns = event.detail.emergency_signs;
	patientMeasurementsData_.examiner_name = event.detail.examiner_name;
	patientMeasurementsData_.note = event.detail.note;
	patientMeasurementsData_.dateTaken = moment(new Date()).format('YYYY-MM-DD HH:mm:ss');

	var transaction = db.transaction(['labourMeasurement'], 'readwrite');
	var objectStore = transaction.objectStore('labourMeasurement');
	var request = objectStore.add(patientMeasurementsData_);
	request.onsuccess = function () {
		console.log('success');
		app.dataInfo = 'Patient measurements taken!';
		document.querySelector('#toast').open();
		document.querySelector('#record-measurements').reset();
		page.redirect('/patient-profile/' + app.params.labourID);
	};
	request.onerror = function (error) {
		console.log(error);
		app.dataInfo = 'Error saving measurements';
		document.querySelector('#toast').open();
	};
}

function resetFetalPosition() {
	document.querySelector('#fetal_position').selected = null;
}
//Record Measurements
function recordVitals(event) {
	Polymer.dom(event).localTarget.parentElement.submit();
}

function doRecordVitals(event) {
	//if labour has started
	var patientVitals_ = {};
	patientVitals_.labourID = parseInt(app.params.labourID);

	if (_.includes([null, undefined], app.patientLabourData.lp.trueLabourTime)) {
		app.messageInfo = 'Sorry, We cannot save readings because True Labour has not started. ';
		app.$.appDialog.open();
		return null;
	}

	patientVitals_.fetalHeartRate = app.addChartData(app.$.fhr, event.detail.fetal_heart_rate);
	patientVitals_.cervicalDilation = app.addChartData(app.$.cervicoGraph, event.detail.cervical_dilation);
	patientVitals_.fetalHeadStation = app.addChartData(app.$.cervicoGraph, document.querySelector('#fetal_head_station').selected || null, undefined, "Descent");

	patientVitals_.contractionsRate = app.addChartData(app.$.contractions, event.detail.contractions_rate);
	patientVitals_.contractionDuration = app.addChartData(app.$.contractions, event.detail.contraction_duration);

	patientVitals_.bloodPressure = app.addChartData(app.$.bpChart, document.querySelector('#blood_pressure').value, 'bp'); //prevent 0 from plotting?
	patientVitals_.pulse = app.addChartData(app.$.pulseChart, event.detail.pulse);
	patientVitals_.temperature = app.addChartData(app.$.temperatureChart, event.detail.temperature);
	patientVitals_.respiration = app.addChartData(app.$.respirationChart, event.detail.respiration);
	patientVitals_.bloodGlucose = app.addChartData(app.$.glucoseChart, event.detail.blood_glucose);

	var transaction = db.transaction(['labourVitals'], 'readwrite');
	var objectStore = transaction.objectStore('labourVitals');
	var request = objectStore.add(patientVitals_);
	request.onsuccess = function () {
		console.log('success');
		app.dataInfo = 'Vital signs saved!';
		document.querySelector('#toast').open();
		document.querySelector('#record-vitals').reset();
		page.redirect('/patient-profile/' + app.params.labourID);
	};
	request.onerror = function (error) {
		console.log(error);
		app.dataInfo = 'Error saving measurements';
		document.querySelector('#toast').open();
	};
}

//patient measurements
function __getPatientMeasurements(lid) {
	var _patientLabourMeasurements = [];
	var db;
	var oReq = window.indexedDB.open(dbName);
	oReq.onsuccess = function (evt) {
		db = evt.target.result;
		var transaction = db.transaction(['labourMeasurement'], 'readwrite');
		var _getLabourPatientM = transaction.objectStore('labourMeasurement');
		var index = _getLabourPatientM.index('labourID');
		var cursorRequest = index.openCursor(window.IDBKeyRange.only(parseInt(lid)));
		cursorRequest.onsuccess = function (e) {
			var cursor = e.target.result;
			if (cursor) {
				_patientLabourMeasurements.push(cursor.value);
				cursor.continue();
			}
		};
		setTimeout(function () {
			app.measurements = _patientLabourMeasurements;
			if (_patientLabourMeasurements.length > 0) {
				app.patientMeasurementsData = _patientLabourMeasurements[_patientLabourMeasurements.length - 1];
			}
			app.patientLMDatum = _patientLabourMeasurements;
		}, 200);
	};
}

//patient vitals
function __getPatientVitals(lid) {
	var _patientLabourVitals = [];
	var db;
	var oReq = window.indexedDB.open(dbName);
	oReq.onsuccess = function (evt) {
		db = evt.target.result;
		var transaction = db.transaction(['labourVitals'], 'readwrite');
		var _getLabourPatientM = transaction.objectStore('labourVitals');
		var index = _getLabourPatientM.index('labourID');
		var cursorRequest = index.openCursor(window.IDBKeyRange.only(parseInt(lid)));
		cursorRequest.onsuccess = function (e) {
			var cursor = e.target.result;
			if (cursor) {
				_patientLabourVitals.push(cursor.value);
				cursor.continue();
			}
		};
		setTimeout(function () {
			app.vitalSigns = _patientLabourVitals;
			if (_patientLabourVitals.length > 0) {
				app.patientVitalsData = _patientLabourVitals[_patientLabourVitals.length - 1];
			}
		}, 200);
	};
}

//Record Delivery
function recordDelivery(event) {
	Polymer.dom(event).localTarget.parentElement.submit();
}
function doRecordDelivery(event) {
	var patientDeliveryData_ = {};
	patientDeliveryData_.labourID = parseInt(app.params.labourID);
	patientDeliveryData_.deliveryType = document.querySelector('#delivery_type').selected;
	patientDeliveryData_.motherAlive = document.querySelector('#mother_alive').selected;
	patientDeliveryData_.babyAlive = document.querySelector('#baby_alive').selected;
	patientDeliveryData_.synto = document.querySelector('#synto').selected;
	patientDeliveryData_.placentaDelivered = document.querySelector('#placenta_delivered').selected;
	var bleed = document.querySelector('#bleed').selected;
	if (bleed == 'Yes') {
		patientDeliveryData_.bleeding = bleed;
	} else {
		patientDeliveryData_.bleeding = 'No, Volume: ' + event.detail.bleed_volume;
	}
	patientDeliveryData_.babyDOB = event.detail.baby_dob;
	patientDeliveryData_.babyTOB = event.detail.baby_tob;
	patientDeliveryData_.babyCried = document.querySelector('#baby_cried').selected;
	patientDeliveryData_.babySex = document.querySelector('#baby_sex').selected;
	patientDeliveryData_.babyApgarScore = event.detail.baby_apgar_score;
	patientDeliveryData_.babyWeight = event.detail.baby_weight;
	patientDeliveryData_.babyVitaminK = document.querySelector('#baby_vitamin_k').selected;
	patientDeliveryData_.babyRh = document.querySelector('#baby_rhesus').selected;
	patientDeliveryData_.note = event.detail.note;
	patientDeliveryData_.babyTransferedTo = document.querySelector('#transfer').selected;
	patientDeliveryData_.pediatricianName = event.detail.pediatrician_name;
	patientDeliveryData_.Comment = event.detail.delivery_comment;
	patientDeliveryData_.dateTaken = moment(new Date()).format('YYYY-MM-DD HH:mm:ss');

	var transaction = db.transaction(['labourDelivery'], 'readwrite');
	var objectStore = transaction.objectStore('labourDelivery');
	var request = objectStore.add(patientDeliveryData_);
	request.onsuccess = function () {
		console.log('success');
		app.dataInfo = 'Patient delivery recorded!';
		document.querySelector('#toast').open();
		document.querySelector('#record-delivery').reset();
		page.redirect('/patient-profile/' + app.params.labourID);
	};
	request.onerror = function (error) {
		console.log(error);
		app.dataInfo = 'Error saving delivery';
		document.querySelector('#toast').open();
	};
}

//Patient Delivery
function __getPatientDeliveries(lid) {
	var _patientDelivery = [];
	var db;
	var oReq = window.indexedDB.open(dbName);
	oReq.onsuccess = function (evt) {
		db = evt.target.result;
		var transaction = db.transaction(['labourDelivery'], 'readwrite');
		var _getLabourPatientD = transaction.objectStore('labourDelivery');
		var index = _getLabourPatientD.index('labourID');
		var cursorRequest = index.openCursor(window.IDBKeyRange.only(parseInt(lid)));
		cursorRequest.onsuccess = function (e) {
			var cursor = e.target.result;
			if (cursor) {
				_patientDelivery.push(cursor.value);
				cursor.continue();
			}
		};
		setTimeout(function () {
			app.patientDeliveryDatum = _patientDelivery;
			console.log(_patientDelivery);
		}, 200);
	};
}

//Record Assessments
function riskAssessment(event) {
	Polymer.dom(event).localTarget.parentElement.submit();
}
function doRiskAssessment(event) {
	var patientAssessmentData_ = {};
	patientAssessmentData_.labourID = parseInt(app.params.labourID);
	patientAssessmentData_.riskScore = event.detail.risk_score;
	patientAssessmentData_.height = event.detail.height;
	patientAssessmentData_.weight = event.detail.weight;
	patientAssessmentData_.previousPregnancy = document.querySelector('#previous_pregnancy').selected;
	patientAssessmentData_.birthWeightHistory = document.querySelector('#birth_weight').selected;
	patientAssessmentData_.pastExperience = event.detail.past_experience;
	patientAssessmentData_.note = event.detail.note;
	if (document.querySelector('#monitoring').selected == 'Yes') {
		var ne = event.detail.next_exam;
		if(ne == 'Continuous'){
			patientAssessmentData_.nextDate = moment(new Date()).add(1, 'minutes').format('YYYY-MM-DD HH:mm:ss');
		}else {
			patientAssessmentData_.nextExam = ne.split(' ')[0];
			patientAssessmentData_.nextDate = moment(new Date()).add(parseInt(ne.split(' ')[0]), 'minutes').format('YYYY-MM-DD HH:mm:ss');
		}
	} else {
		patientAssessmentData_.nextExam = null;
		patientAssessmentData_.nextDate = null;
	}
	patientAssessmentData_.dateTaken = moment(new Date()).format('YYYY-MM-DD HH:mm:ss');

	var transaction = db.transaction(['labourAssessment'], 'readwrite');
	var objectStore = transaction.objectStore('labourAssessment');
	var request = objectStore.add(patientAssessmentData_);
	request.onsuccess = function () {
		console.log('success');
		app.dataInfo = 'Patient assessment saved!';
		document.querySelector('#toast').open();
		document.querySelector('#risk-assessment').reset();
		page.redirect('/patient-profile/' + app.params.labourID);
	};
	request.onerror = function (error) {
		console.log(error);
		app.dataInfo = 'Error saving assessment';
		document.querySelector('#toast').open();
	};
}

//Patient Assessments
function __getPatientAssessments(lid) {
	var _patientAssessment = [];
	var db;
	var oReq = window.indexedDB.open(dbName);
	oReq.onsuccess = function (evt) {
		db = evt.target.result;
		var transaction = db.transaction(['labourAssessment'], 'readwrite');
		var _getLabourPatientA = transaction.objectStore('labourAssessment');
		var index = _getLabourPatientA.index('labourID');
		var cursorRequest = index.openCursor(window.IDBKeyRange.only(parseInt(lid)));
		cursorRequest.onsuccess = function (e) {
			var cursor = e.target.result;

			if (cursor) {
				_patientAssessment.push(cursor.value);
				cursor.continue();
			}
			if (_patientAssessment.length > 0) {
				var _pAss = _patientAssessment[_patientAssessment.length - 1];
				app.patientAssessmentData = _pAss;
			}
			app.patientAssessmentDatum = _patientAssessment;
		};
	};
	return _patientAssessment;
}

function clearFrm(event) {
	Polymer.dom(event).localTarget.parentElement.parentElement.reset();
	// Polymer.dom(event).localTarget.parentElement.reset();
}

function clearThisFrm(form) {
	document.getElementById(form).reset();
}
