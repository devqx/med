var idbSupported = false;
var db;

document.addEventListener('DOMContentLoaded',function() {
  window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB;
  window.IDBTransaction = window.IDBTransaction || window.webkitIDBTransaction;
  window.IDBKeyRange = window.IDBKeyRange || window.webkitIDBKeyRange || window.msIDBKeyRange;

  if ('indexedDB' in window) {
    idbSupported = true;
    //try {
    //  indexedDB.deleteDatabase("medicplusArv");
    //  console.log("deleted old database");
    //} catch(except){
    //  console.log("no old db found to delete");
    //}
  }
  if (idbSupported) {
    var openRequest = window.indexedDB.open('medicplusArv', 1);
    openRequest.onupgradeneeded = function(e) {
      var thisDB = e.target.result;
      if (!thisDB.objectStoreNames.contains('arvEnrollment')) {
        var le = thisDB.createObjectStore('arvEnrollment', {keyPath: 'patientId', autoIncrement: true});
        le.createIndex('patientId', 'patientId', {unique: true});
        le.createIndex('idxArvID', 'id', {unique: true});
      }
    };

    openRequest.onsuccess = function(e) {
      //app.dataInfo = ('Successfully opened database');
      db = e.target.result;
      //document.querySelector('#toast').open();
    };

    openRequest.onerror = function(e) {
      app.dataInfo = ('Error opening database');
      document.querySelector('#toast').open();
    };
  }
},false);
