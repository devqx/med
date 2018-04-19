var idbSupported = false;
var db;
var dbName = 'labourMgt';
document.addEventListener('DOMContentLoaded',function() {
  window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB;
  window.IDBTransaction = window.IDBTransaction || window.webkitIDBTransaction;
  window.IDBKeyRange = window.IDBKeyRange || window.webkitIDBKeyRange || window.msIDBKeyRange;

  if ('indexedDB' in window) {
    idbSupported = true;
    try {
     indexedDB.deleteDatabase("labourDb");
     indexedDB.deleteDatabase("medicplus");
     console.log("deleted old database(s)");
    } catch(except){
     console.log("no old db found to delete");
    }
  }
  if (idbSupported) {
    var openRequest = window.indexedDB.open(dbName,1);
    openRequest.onupgradeneeded = function(e) {
      var thisDB = e.target.result;
      if (!thisDB.objectStoreNames.contains('labourEnrollment')) {
        var le = thisDB.createObjectStore('labourEnrollment', {keyPath: 'labourID', autoIncrement: true});
        le.createIndex('patientID', 'patientID', {unique: true});
        le.createIndex('idxLabourID', 'labourID', {unique: true});
      }
      if (!thisDB.objectStoreNames.contains('labourMeasurement')) {
        var lm = thisDB.createObjectStore('labourMeasurement', {keyPath: 'measureId', autoIncrement: true});
        lm.createIndex('labourID', 'labourID', {unique: false});
      }
      if (!thisDB.objectStoreNames.contains('labourVitals')) {
        var lv = thisDB.createObjectStore('labourVitals', {keyPath: 'vitalsId', autoIncrement: true});
        lv.createIndex('labourID', 'labourID', {unique: false});
      }
      if (!thisDB.objectStoreNames.contains('labourAssessment')) {
        var la = thisDB.createObjectStore('labourAssessment', {keyPath: 'assessmentId', autoIncrement: true});
        la.createIndex('labourID', 'labourID', {unique: false});
      }
      if (!thisDB.objectStoreNames.contains('labourDelivery')) {
        var ld = thisDB.createObjectStore('labourDelivery', {keyPath: 'deliveryId', autoIncrement: true});
        ld.createIndex('labourID', 'labourID', {unique: false});
      }
    };

    openRequest.onsuccess = function(e) {
      console.log('Success!');
      db = e.target.result;
    };

    openRequest.onerror = function(e) {
      console.log('Error');
      console.log(e);
    };
  }
},false);
