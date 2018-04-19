/**
 * Created by robot on 2/18/16.
 */

var vars = {};
/*Array.prototype.get = function(object){
  key = Object.keys(object)[0];
  console.warn([Object.keys(object)[0], object.key]);
  //return Array[ Object.keys(object)[0] ];
};*/
window.addEventListener('WebComponentsReady', function() {
  var scope = document.querySelector('template#app');
  jQuery.getJSON('/api/sti_get_vars.php', function(data){
    scope.careEntryPoints = data.careEntryPoints;
    scope.modesOfTest = data.modesOfTest;
    scope.priorARTs = data.priorARTs;
    document.getElementById('t').careEntryPoints = data.careEntryPoints;
  });
  scope.selected = 0;

  scope.formatDateTime = function(dt) {
    return moment(dt, 'YYYY-MM-DD HH:mm:ss').format('Do MMM, YYYY h:mm a');
  };
  scope.formatDate = function(dt) {
    return moment(dt, 'YYYY-MM-DD').format('Do MMM, YYYY');
  };
  scope.formatTime = function(dt) {
    return moment(dt, 'YYYY-MM-DD HH:mm:ss').format('h:mm a');
  };

  scope.__doCloseMgt = function () {
    var transaction = db.transaction(['arvEnrollment'], 'readwrite');
    var objectStore = transaction.objectStore('arvEnrollment');
    var request = objectStore.get(parseInt(app.params.patientId));
    request.onsuccess = function() {
      var data = request.result;
      data.active = false;
      var updateRequest = objectStore.put(data);
      updateRequest.onsuccess = function() {
        app.dataInfo = 'Patient closed!';
        document.querySelector('#toast').open();
        page.redirect('/patients');
      };
    };
  };

  scope.doCloseMgt = function() {
    app.$.confirmCloseMgt.open();
  };

  scope.lookUpPatient= function () {
    app.patientName = null;
    app.patientDOB = null;
    app.$.backdrop.open();
    jQuery.getJSON("/api/search_patients.php", {pid: document.querySelector('#emrId').value})
        .done(function(data) {
          if(data !== null){
            app.patientName = data.fullname;
            app.patientDOB = data.dateOfBirth;
            app.passport = data.passportPath;
          } else {
            app.dataInfo = 'Search did not find ANY record';
            document.querySelector('#toast').open();
          }
          app.$.backdrop.close();
        });
  };

  document.getElementById('enroll-patient').addEventListener('iron-form-submit', doEnrollPatient);
});

angular
    .module('polymer-labour', ['ng-polymer-elements', 'indexedDB'], function($interpolateProvider) {
      $interpolateProvider.startSymbol('<%');
      $interpolateProvider.endSymbol('%>');
    })
    .config(function($indexedDBProvider) {
      $indexedDBProvider.connection('medicplusArv');
    });

//Enroll Patient
function enrollPatient(event) {
  Polymer.dom(event).localTarget.parentElement.submit();
}

function doEnrollPatient(event) {
  var transaction = db.transaction(['arvEnrollment'], 'readwrite');
  var objectStore = transaction.objectStore('arvEnrollment');
  var patientData_ = event.detail;
  patientData_.dateEnrolled = moment(new Date()).format('YYYY-MM-DD HH:mm:ss');
  patientData_.active = true;
  patientData_.patientId = parseInt(event.detail.patientId);
  var now = moment(new Date());
  patientData_.patientAge = now.diff(moment(event.detail.patientDOB), 'year');
  patientData_.careEntryPoint = document.querySelector('#careEntryPoint').selected;
  var request = objectStore.add(patientData_);
  request.onsuccess = function(data) {
    app.arvPatients.push(patientData_);
    _arvPatients.push(patientData_);
    app.dataInfo = 'Patient enrolled successfully!';
    document.querySelector('#toast').open();
    document.querySelector('#enroll-patient').reset();
    jQuery.post('/api/sync_arv_enrollments_data.php', {formData:patientData_}, function(data){
      app.$.backdrop.close();
      if(data.status=="error"){
        app.messageInfo = data.message;
        app.$.appDialog.open();
      } else if(data.status=="success"){
        app.dataInfo = data.message;
        app.$.toast.open();
      } else {
        app.dataInfo = "Unknown response from server";
        app.$.toast.open();
      }
    }, 'json').error(function(evt){
      app.$.backdrop.close();
      app.messageInfo = evt.status+": "+evt.statusText;
      app.$.appDialog.open();
    });
    page.redirect('/patients');
    //page.redirect('/patient-profile/' + parseInt(data.target.result));
  };
  request.onerror = function(error) {
    //console.log(error);
    app.dataInfo = 'Patient is already been enrolled in the ARV management';
    document.querySelector('#toast').open();
  };
}

var _arvPatients = [];
//all patients
var db;
var oReq;
function __getPatients(callback) {
  var patients = [];
  app.$.backdrop.open();
  jQuery.getJSON('/api/get_arv_enrollments.php', function(data){
    for(var i=0;i<data.length;i++){
      var patient = {};
      patient.patientName = data[i].patient.fullname;
      patient.patientDOB = data[i].patient.dateOfBirth;
      patient.passport = data[i].patient.passportPath;
      patient.dateEnrolled = moment(data[i].enrolledOn).format('YYYY-MM-DD HH:mm:ss');
      patient.dateEnrolledIntoCare = moment(data[i].dateEnrolledIntoCare).format('YYYY-MM-DD');
      patient.dateHIVConfirmedTest = moment(data[i].dateHIVConfirmedTest).format('YYYY-MM-DD');
      patient.careEntryPoint = data[i].careEntryPoint.name;
      patient.modeOfTest = data[i].modeOfTest.name;
      patient.priorART = data[i].priorART.name;
      patient.active = Boolean(data[i].active);
      patient.patientId = parseInt(data[i].patient.patientId);
      patient.locationOfTest = data[i].locationOfTest;
      patient.uniqueID = data[i].uniqueId;
      var now = moment(new Date());
      patient.patientAge = now.diff(moment(data[i].patient.dateOfBirth), 'year');
      patients.push(patient);
    }
  }).done(function () {
    oReq = window.indexedDB.open('medicplusArv');
    oReq.onsuccess = function(evt) {
      db = evt.target.result;
      var transaction = db.transaction(['arvEnrollment'], 'readwrite');
      for (var i=0;i<patients.length;i++) {
        var request = transaction.objectStore('arvEnrollment').add(patients[i]);
        request.onsuccess = function(event) {
          app.dataInfo = "Added data from server";
          document.querySelector('#toast').open();
          // event.target.result == patients[i].patientId;//not true
        };
        request.onerror = function (event) {
          // item already exists
        };
      }
    };
    oReq.onerror = function(evt) {
      console.error(evt);
    }
  }).done(function () {
    oReq = window.indexedDB.open('medicplusArv');
    oReq.onsuccess = function(evt) {
      //console.log("reading data from db");
      db = evt.target.result;
      var transaction = db.transaction(['arvEnrollment'], 'readwrite');
      _arvPatients = [];
      transaction.objectStore('arvEnrollment').index('patientId').openCursor().onsuccess = function (e) {
        var cursor = e.target.result;
        if (cursor && cursor.value.active === true) {
          _arvPatients.push(cursor.value);
          cursor.continue();
        }
      };
      transaction.oncomplete = function(){
        app.arvPatients = _arvPatients;
        app.$.backdrop.close();
        Polymer.dom(document).querySelector('#itemsList').fire('resize');
      };
    };
  });
}

//patient profile
function __getPatient(id) {
  //var __patientData = {};
  var db;
  var oReq = window.indexedDB.open('medicplusArv');
  oReq.onsuccess = function(evt) {
    db = evt.target.result;
    var transaction = db.transaction(['arvEnrollment'], 'readwrite');
    var _getPatient = transaction.objectStore('arvEnrollment');
    var request = _getPatient.get(parseInt(id));
    request.onsuccess = function() {
      //__patientData = request.result;
      app.patientArvData = request.result;
    };
    /*setTimeout(function() {
      app.patientArvData = __patientData;
    }, 200);*/
  };
}

function clearThisFrm(form) {
  document.getElementById(form).reset();
}
