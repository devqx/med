/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


angular.module("user-controller", [])
        .controller("AdmittedPatientCtrl", function ($scope, $http, IPFactory, $stateParams) {
            $scope.temp = "llll";
            $scope.setTemp = function (wid) {
                $scope.temp = wid;
//                if(ward==$scope.temp){
//                    
//                }
                return true;
            };
            $scope.patients = [];
            $scope.wardsWrapper = [];
            $http.get("/api/in_patient.php?all=true&full=true").success(function (data) {
                var currentWID = null;
                $scope.patients = data;
            }).error(function (data) {
                alert("error: we are unable to fetch the patient list");
            });

            $scope.currentPage = 0;
            $scope.numberOfPages = function () {
                return Math.ceil($scope.patients.length / $scope.pageSize);
            }
        })

        .controller("MyPatientCtrl", function ($scope, $http, IPFactory) {
            $scope.patients = [];
            $http.get("/api/in_patient.php?myPatient=true&full=true").success(function (data) {
                $scope.patients = data;
            }).error(function (data) {
                alert("error: we are unable to fetch the patient list");
            });
            $scope.currentPage = 0;
            $scope.numberOfPages = function () {
                return Math.ceil($scope.patients.length / $scope.pageSize);
            }
        })

        .controller("RoundingCtrl", function ($scope, $http, IPFactory) {

        })
        .controller("InboundPatientCtrl", function ($scope, $http, IPFactory) {
            $scope.patients = [];
            $http.get("/api/in_patient.php?inbound=true&full=true").success(function (data) {
                $scope.patients = data;
            }).error(function (data) {
                alert("error: we are unable to fetch the patient list");
            });

            $scope.currentPage = 0;
            $scope.numberOfPages = function () {
                return Math.ceil($scope.patients.length / $scope.pageSize);
            };
            $scope.assignBed=function(url, title){
                Boxy.load(url, {title:title})
            }
        })
        .controller("HistoryCtrl", function ($scope, $http, IPFactory) {
            $scope.patients=[];
            $scope.duration=function(from, to){
                console.log(from, "  ", to)
                var diff=moment(to).diff(moment(from), "days")+1;
                return diff +" day"+((diff>1)?"s":"");
            }
            $http.get("/api/in_patient.php?history=true&full=true").success(function (data) {
                $scope.patients = data;
            }).error(function (data) {
                alert("error: we are unable to fetch the patient list");
            });
            
            $scope.currentPage = 0;
            $scope.numberOfPages = function () {
                return Math.ceil($scope.patients.length / $scope.pageSize);
            }
        })
        .controller("ClinicalCtrl", function ($scope, $http, IPFactory) {
            $scope.ctData=[];
            $scope.isState=function(next){
                console.log(moment(next).isBefore())
                return moment(next).isBefore();
            };
            $http.get("/api/clinical_task.php?active=Active&full=true").success(function (data) {
                $scope.ctData = data;
                console.log(data)
            }).error(function (data) {
                alert("error: we are unable to fetch the patient list");
            });
            
            $scope.currentPage = 0;
            $scope.numberOfPages = function () {
                return Math.ceil($scope.ctData.length / $scope.pageSize);
            }
        })
var test