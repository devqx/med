
angular.module("inPatientApp", ["ui.bootstrap", "ui.router", "angularMoment", "user-controller", "user-service"])
        .config(function ($stateProvider, $urlRouterProvider) {
            $urlRouterProvider.otherwise('/inbound-patients');
            $stateProvider
                    .state("admitted-patients", {url: "/admitted-patients", templateUrl: "/admissions/admittedPatient.html", controller: "AdmittedPatientCtrl"})
                    .state('my-patients', {url: '/my-patient', templateUrl: '/admissions/myPatient.html', controller: "MyPatientCtrl"})
                    .state('clinical', {url: '/clinical', templateUrl: '/admissions/clinical.html', controller: "ClinicalCtrl"})
                    .state('inbound-patients', {url: '/inbound-patients', templateUrl: '/admissions/inboundPatient.html', controller: "InboundPatientCtrl"})
                    .state('history', {url: '/admission/histroy', templateUrl: '/admissions/history.html', controller: 'HistoryCtrl'})

                    .state('assign-bed', {url: '/assign-bed', templateUrl: '/admissions/history.html'})

        })
        .run(function ($rootScope, $state, IPFactory) {
            $rootScope.pageSize = IPFactory.pageSize;
            $rootScope.setPageSize = function (size) {
                IPFactory.pageSize = size;
            };
            $rootScope.goto = function (state, params) {
                $state.go(state)
            };
            $rootScope.gotoNumber = function (index, params) {
                $("#tabbedPane li").each(function () {
                    $(this).removeClass('active');
                });
                $(params.target).parent().parent().addClass('active');

                switch (index) {
                    case(1): {
                        $rootScope.goto("inbound-patients", params);
                        break;
                    }
                    case(2): {
                        $rootScope.goto("admitted-patients", params);
                        break;
                    }
                    case(3): {
                        $rootScope.goto("my-patients", params);
                        break;
                    }
                    case(4): {
                        $rootScope.goto("clinical", params);
                        break;
                    }
                    case(5): {
                        $rootScope.goto("history", params);
                        break;
                    }
                    default: {
                        $rootScope.goto("inbound-patients", params);
                    }
                }
                //$('#admission_container table').dataTable();
                //$('.dataTables_length select').select2();
            }
        });

        //We already have a limitTo filter built-in to angular, let's make a startFrom filter
        //.filter('startFrom', function () {
        //    return function (input, start) {
        //        //  console.log(input, " :  ", start)
        //        start = +start; //parse to int
        //        return input.slice(start);
        //    }
        //});