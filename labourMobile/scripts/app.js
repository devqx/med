/*
 Copyright (c) 2015 The Polymer Project Authors. All rights reserved.
 This code may only be used under the BSD style license found at http://polymer.github.io/LICENSE.txt
 The complete set of authors may be found at http://polymer.github.io/AUTHORS.txt
 The complete set of contributors may be found at http://polymer.github.io/CONTRIBUTORS.txt
 Code distributed by Google as part of the polymer project is also
 subject to an additional IP rights grant found at http://polymer.github.io/PATENTS.txt
 */

(function (document) {
	'use strict';

	// Grab a reference to our auto-binding template
	// and give it some initial binding values
	// Learn more about auto-binding templates at http://goo.gl/Dx1u2g
	var app = document.querySelector('#app');

	// Sets app default base URL
	app.baseUrl = '/labourMobile/';
	app.patientLabourDatum = [];
	app.detailView = false;
	var dbName = 'labourMgt';

	app.goHome = function () {
		page.redirect('/patients');
		app.detailView = false;
	};


	app.displayInstalledToast = function () {
		// Check to make sure caching is actually enabled—it won't be in the dev environment.
		if (!Polymer.dom(document).querySelector('platinum-sw-cache').disabled) {
			Polymer.dom(document).querySelector('#caching-complete').show();
		}
	};

	// Listen for template bound event to know when bindings
	// have resolved and content has been stamped to the page
	app.addEventListener('dom-change', function () {
		console.log('Our app is ready to rock!');
	});

	// See https://github.com/Polymer/polymer/issues/1381
	window.addEventListener('WebComponentsReady', function () {
		// imports are loaded and elements have been registered
		Highcharts.SVGRenderer.prototype.symbols.cross = function (x, y, w, h) {
			return ['M', x, y, 'L', x + w, y + h, 'M', x + w, y, 'L', x, y + h, 'z'];
		};
		if (Highcharts.VMLRenderer) {
			Highcharts.VMLRenderer.prototype.symbols.cross = Highcharts.SVGRenderer.prototype.symbols.cross;
		}
	});

	// Main area's paper-scroll-header-panel custom condensing transformation of
	// the appName in the middle-container and the bottom title in the bottom-container.
	// The appName is moved to top and shrunk on condensing. The bottom sub title
	// is shrunk to nothing on condensing.
	window.addEventListener('paper-header-transform', function (e) {
		var appName = Polymer.dom(document).querySelector('#mainToolbar .app-name');
		var middleContainer = Polymer.dom(document).querySelector('#mainToolbar .middle-container');
		// var bottomContainer = Polymer.dom(document).querySelector('#mainToolbar .bottom-container');
		var detail = e.detail;
		var heightDiff = detail.height - detail.condensedHeight;
		var yRatio = Math.min(1, detail.y / heightDiff);
		// appName max size when condensed. The smaller the number the smaller the condensed size.
		var maxMiddleScale = 0.50;
		var auxHeight = heightDiff - detail.y;
		var auxScale = heightDiff / (1 - maxMiddleScale);
		var scaleMiddle = Math.max(maxMiddleScale, auxHeight / auxScale + maxMiddleScale);
		var scaleBottom = 1 - yRatio;

		// Move/translate middleContainer
		Polymer.Base.transform('translate3d(0,' + yRatio * 100 + '%,0)', middleContainer);

		// Scale bottomContainer and bottom sub title to nothing and back
		// Polymer.Base.transform('scale(' + scaleBottom + ') translateZ(0)', bottomContainer);

		// Scale middleContainer appName
		Polymer.Base.transform('scale(' + scaleMiddle + ') translateZ(0)', appName);
	});

	// Scroll page to top and expand header
	app.scrollPageToTop = function () {
		app.$.headerPanelMain.scrollToTop(true);
	};

	app.closeDrawer = function () {
		app.$.paperDrawerPanel.closeDrawer();
	};
	app.cervicoGroup = [
		{
			key: 'cervicalDilation', label: 'Cervical Dilation',
			marker: {
				symbol: 'cross',
				lineWidth: 2,
				lineColor: null,
				enabled: true,
				radius: 7,
				// symbol:'X',
				// symbol: "url(data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTkuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDIyMC4xNzYgMjIwLjE3NiIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMjIwLjE3NiAyMjAuMTc2OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgd2lkdGg9IjE2cHgiIGhlaWdodD0iMTZweCI+CjxnPgoJPGc+CgkJPGc+CgkJCTxwYXRoIGQ9Ik0xMzEuNTc3LDExMC4wODRsODQuMTc2LTg0LjE0NmM1Ljg5Ny01LjkyOCw1Ljg5Ny0xNS41NjUsMC0yMS40OTIgICAgIGMtNS45MjgtNS45MjgtMTUuNTk1LTUuOTI4LTIxLjQ5MiwwbC04NC4xNzYsODQuMTQ2TDI1LjkzOCw0LjQ0NmMtNS45MjgtNS45MjgtMTUuNTY1LTUuOTI4LTIxLjQ5Miwwcy01LjkyOCwxNS41NjUsMCwyMS40OTIgICAgIGw4NC4xNDYsODQuMTQ2TDQuNDQ2LDE5NC4yNmMtNS45MjgsNS44OTctNS45MjgsMTUuNTY1LDAsMjEuNDkyYzUuOTI4LDUuODk3LDE1LjU2NSw1Ljg5NywyMS40OTIsMGw4NC4xNDYtODQuMTc2bDg0LjE3Niw4NC4xNzYgICAgIGM1Ljg5Nyw1Ljg5NywxNS41NjUsNS44OTcsMjEuNDkyLDBjNS44OTctNS45MjgsNS44OTctMTUuNTk1LDAtMjEuNDkyTDEzMS41NzcsMTEwLjA4NHoiIGZpbGw9IiMwMDAwMDAiLz4KCQk8L2c+Cgk8L2c+Cgk8Zz4KCTwvZz4KCTxnPgoJPC9nPgoJPGc+Cgk8L2c+Cgk8Zz4KCTwvZz4KCTxnPgoJPC9nPgoJPGc+Cgk8L2c+Cgk8Zz4KCTwvZz4KCTxnPgoJPC9nPgoJPGc+Cgk8L2c+Cgk8Zz4KCTwvZz4KCTxnPgoJPC9nPgoJPGc+Cgk8L2c+Cgk8Zz4KCTwvZz4KCTxnPgoJPC9nPgoJPGc+Cgk8L2c+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg==)",
			}
		},
		{
			key: 'fetalHeadStation', label: 'Fetal Head Station',
			marker: {
				symbol: 'circle',
				enabled: true,
				lineColor: null,
				lineWidth: 2,
				fillColor: '#fff',
				radius: 7,
				// symbol: "url(data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTYuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjE2cHgiIGhlaWdodD0iMTZweCIgdmlld0JveD0iMCAwIDQzOC41MzMgNDM4LjUzMyIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDM4LjUzMyA0MzguNTMzOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxnPgoJPHBhdGggZD0iTTQwOS4xMzMsMTA5LjIwM2MtMTkuNjA4LTMzLjU5Mi00Ni4yMDUtNjAuMTg5LTc5Ljc5OC03OS43OTZDMjk1LjczNiw5LjgwMSwyNTkuMDU4LDAsMjE5LjI3MywwICAgYy0zOS43ODEsMC03Ni40Nyw5LjgwMS0xMTAuMDYzLDI5LjQwN2MtMzMuNTk1LDE5LjYwNC02MC4xOTIsNDYuMjAxLTc5LjgsNzkuNzk2QzkuODAxLDE0Mi44LDAsMTc5LjQ4OSwwLDIxOS4yNjcgICBjMCwzOS43OCw5LjgwNCw3Ni40NjMsMjkuNDA3LDExMC4wNjJjMTkuNjA3LDMzLjU5Miw0Ni4yMDQsNjAuMTg5LDc5Ljc5OSw3OS43OThjMzMuNTk3LDE5LjYwNSw3MC4yODMsMjkuNDA3LDExMC4wNjMsMjkuNDA3ICAgczc2LjQ3LTkuODAyLDExMC4wNjUtMjkuNDA3YzMzLjU5My0xOS42MDIsNjAuMTg5LTQ2LjIwNiw3OS43OTUtNzkuNzk4YzE5LjYwMy0zMy41OTYsMjkuNDAzLTcwLjI4NCwyOS40MDMtMTEwLjA2MiAgIEM0MzguNTMzLDE3OS40ODUsNDI4LjczMiwxNDIuNzk1LDQwOS4xMzMsMTA5LjIwM3ogTTM1My43NDIsMjk3LjIwOGMtMTMuODk0LDIzLjc5MS0zMi43MzYsNDIuNjMzLTU2LjUyNyw1Ni41MzQgICBjLTIzLjc5MSwxMy44OTQtNDkuNzcxLDIwLjgzNC03Ny45NDUsMjAuODM0Yy0yOC4xNjcsMC01NC4xNDktNi45NC03Ny45NDMtMjAuODM0Yy0yMy43OTEtMTMuOTAxLTQyLjYzMy0zMi43NDMtNTYuNTI3LTU2LjUzNCAgIGMtMTMuODk3LTIzLjc5MS0yMC44NDMtNDkuNzcyLTIwLjg0My03Ny45NDFjMC0yOC4xNzEsNi45NDktNTQuMTUyLDIwLjg0My03Ny45NDNjMTMuODkxLTIzLjc5MSwzMi43MzgtNDIuNjM3LDU2LjUyNy01Ni41MyAgIGMyMy43OTEtMTMuODk1LDQ5Ljc3Mi0yMC44NCw3Ny45NDMtMjAuODRjMjguMTczLDAsNTQuMTU0LDYuOTQ1LDc3Ljk0NSwyMC44NGMyMy43OTEsMTMuODk0LDQyLjYzNCwzMi43MzksNTYuNTI3LDU2LjUzICAgYzEzLjg5NSwyMy43OTEsMjAuODM4LDQ5Ljc3MiwyMC44MzgsNzcuOTQzQzM3NC41OCwyNDcuNDM2LDM2Ny42MzcsMjczLjQxNywzNTMuNzQyLDI5Ny4yMDh6IiBmaWxsPSIjMDAwMDAwIi8+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg==)",
			}
		}
	];

	app.contractionsGroup = [
		{
			key: 'contractionsRate', label: 'Rate of Contractions',
			marker: {
				lineWidth: 2,
				enabled: true,
				lineColor: null,
				width: 10,
				height: 10
			}
		},
		{
			key: 'contractionDuration', label: 'Duration of Contractions',
			marker: {
				enabled: true,
				lineColor: null,
				width: 10,
				height: 10,
				lineWidth: 2
			}
		}
	];

	app.addChartData = function (chartObj, value, param3) {
		var val = Number(value);
		if (value == undefined || val === 0 || value.trim() === '') {
			return null;
		}
		var now = (new Date()).getTime();
		var trueLabourTime = moment(app.patientLabourData.lp.trueLabourTime, 'YYYY-MM-DD hh:mm:ss').toDate().getTime();
		var xAxis = parseFloat(( now - trueLabourTime ) / 3600000);

		if (param3 !== undefined) {
			//we're adding bp reading
			var reading = value.split("/");
			var sys = reading[0];
			var dia = reading[1];
			if (Number(sys) !== 0 && Number(dia) !== 0) {
				chartObj.addData(xAxis, Number(sys));
				return {
					diastolic: {
						xAxis: xAxis,
						value: Number(dia)
					},
					systolic: {
						xAxis: xAxis,
						value: Number(sys)
					}
				};
			}
			return null;
		}

		chartObj.addData(xAxis, val);
		window.dispatchEvent(new Event('resize'));
		return {
			xAxis: xAxis,
			value: val
		};
	};

	app.formatChartData = function (data, type) {
		//var _data = [];
		var Type = [];
		if (typeof type === "string") {
			Type.push(type);
		} else {
			Type = type;
		}

		var dataSorted = {};

		//make the action and alert lines to be dynamically related to the starting point of the graph
		var x1 = null;
		var y1 = null;

		_.forEach(Type, function (key) {
			key = _.isObject(key) ? key.key : key;
			dataSorted[key] = [];
			_.forEach(data, function (value) {
				//get the first
				if (value[key] && key === 'cervicalDilation' && x1 === null && y1 === null) {
					x1 = value[key].xAxis;
					y1 = value[key].value;
				}
				if (value[key] && key !== 'bloodPressure') {
					dataSorted[key].push([value[key].xAxis, value[key].value]);
				} else if (value[key] && key === 'bloodPressure') {
					dataSorted[key].push({
						systolic: {
							value: value[key].systolic.value,
							xAxis: value[key].systolic.xAxis
						},
						diastolic: {
							value: value[key].diastolic.value,
							xAxis: value[key].diastolic.xAxis
						}
					});
				}
			});
		});
		if (_.isArray(type)) {
			var $data = [];

			// $data.push({name: 'Alert', data: [[8, 3], [15, 10]]});
			// $data.push({name: 'Action', data: [[11, 3], [18, 10]]});

			var gap1 = 3;
			var gap2 = 7;

			if (type == app.cervicoGroup) {
				$data.push({name: 'Alert', data: [[x1, y1], [x1+gap2, y1+gap2]]});
				$data.push({name: 'Action', data: [[x1+gap1, y1], [x1+gap1+gap2, y1+gap2]]});
			}
			_.forEach(type, function (idx) {
				$data.push({name: idx.label, data: dataSorted[idx.key], marker: idx.marker});
			});
			return $data;
		} else if (type == 'bloodPressure') {
			var $dataBp = [];
			var systolicData = [], diastolicData = [];
			_.forEach(dataSorted['bloodPressure'], function (idx) {
				_.forEach(Object.keys(idx), function (item) {
					if (item === 'systolic') {
						systolicData.push([idx[item].xAxis, idx[item].value]);
					} else if (item === 'diastolic') {
						diastolicData.push([idx[item].xAxis, idx[item].value]);
					}
				});
			});

			$dataBp.push({name: 'Systolic', data: systolicData});
			$dataBp.push({name: 'Diastolic', data: diastolicData});
			return $dataBp;
		} else {
			return dataSorted[type];
		}
	};

	app.xAxis = {
		gridLineWidth: 1,
		minPadding: 1,
		// maxZoom: 60,
		labels: {
			formatter: function () {
				return this.value;
			}
		},
		maxPadding: 1,
		showLastLabel: true,
		allowDecimals: false,
		pointStart: 0,
		min: 0,
		max: 24
	};

	app.chartOptions = {
		area: {
			fillColor: {
				linearGradient: {
					x1: 0,
					y1: 0,
					x2: 0,
					y2: 1
				},
				stops: [
					[0, Highcharts.getOptions().colors[0]],
					[1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
				]
			},
			marker: {radius: 2},
			lineWidth: 1,
			states: {hover: {lineWidth: 1}},
			threshold: null
		},
		line: {marker: {radius: 7, enabled: true}, /*dataLabels: {enabled: true}, enableMouseTracking: false*/}
	};
	app.tooltipSettings = {
		crosshairs: true,
		// shared: true,
		// valueSuffix: '',
		headerFormat: '{series.name} at <br>',
		pointFormat: '<em>{point.x:.2f}</em>hr: <b>{point.y}</b>'
	};
	app.legendOptions = {enabled: true};

	app.syncData = function () {
		app.$.backdrop.open();
		var formData = {};
		var db;
		var request = window.indexedDB.open(dbName);
		request.onsuccess = function (event) {
			db = event.target.result;
			var transaction = db.transaction(["labourAssessment", "labourMeasurement", "labourVitals", "labourEnrollment", "labourDelivery"], "readonly");
			var obj1 = transaction.objectStore('labourAssessment');
			var obj2 = transaction.objectStore('labourMeasurement');
			var obj21 = transaction.objectStore('labourVitals');
			var obj3 = transaction.objectStore('labourEnrollment');
			var obj4 = transaction.objectStore('labourDelivery');

			formData.enrollments = [];
			formData.measurements = [];
			formData.vitals = [];
			formData.assessments = [];
			formData.deliveries = [];

			obj1.openCursor().onsuccess = function (event) {
				var cursor = event.target.result;
				if (cursor) {
					formData.assessments.push(cursor.value);
					cursor.continue();
				}
			};
			obj2.openCursor().onsuccess = function (event) {
				var cursor = event.target.result;
				if (cursor) {
					formData.measurements.push(cursor.value);
					cursor.continue();
				}
			};
			obj21.openCursor().onsuccess = function (event) {
				var cursor = event.target.result;
				if (cursor) {
					formData.vitals.push(cursor.value);
					cursor.continue();
				}
			};
			obj3.openCursor().onsuccess = function (event) {
				var cursor = event.target.result;
				if (cursor) {
					formData.enrollments.push(cursor.value);
					cursor.continue();
				}
			};
			obj4.openCursor().onsuccess = function (event) {
				var cursor = event.target.result;
				if (cursor) {
					formData.deliveries.push(cursor.value);
					cursor.continue();
				}
			};
			transaction.oncomplete = function () {
				jQuery.post('/api/sync_labour_data.php', formData, function (data) {
					app.$.backdrop.close();
					if (data.status == "error") {
						app.messageInfo = data.message;
						app.$.appDialog.open();
					} else if (data.status == "success") {
						app.dataInfo = data.message;
						app.$.toast.open();
					}
				}, 'json');
			};

		};
		request.onerror = function (event) {
			// Do something with request.result!
			console.error(event);
		};
	};

	app.showHelp = function () {
		app.$.dialog.open();
	}

})(document);
