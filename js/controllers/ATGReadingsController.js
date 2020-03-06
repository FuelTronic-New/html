'use strict';

/* Controllers */
// ATG Reading controller
app
    .controller('ATGReadingsController', ['$scope', '$state', 'CRUD', 'toaster', '$rootScope','$filter','$modal', '$localStorage', function ($scope, $state, CRUD, toaster, $rootScope,$filter,$modal, $localStorage) {

        var listurl = 'atgreadings';
        var createurl = 'atgreadings/create/';
        var viewurl = 'atgreadings/'; //  user id required
        var saveurl = 'atgreadings';
        var updateurl = 'atgreadings/update/'; // user id required
        var deleteurl = 'atgreadings/delete/'; // user id required
        $scope.format = 'dd-MMMM-yyyy';
        $scope.isUpdateForm = false;
        $scope.rowCollection = [];
        $scope.selectedSite = '';
        $scope.sites = [];
        $scope.displayCollection = [];
        $scope.dateTimeNow = function() {
           $scope.reading_time = new Date();
         };

        $scope.dateTimeNow();

         $scope.toggleMinDate = function() {
           $scope.minDate = $scope.minDate ? null : new Date();
         };

         $scope.maxDate = new Date('2014-06-22');
         $scope.toggleMinDate();

         $scope.dateOptions = {
           showWeeks: false
         };

         // Disable weekend selection
         $scope.disabled = function(calendarDate, mode) {
           return mode === 'day' && ( calendarDate.getDay() === 0 || calendarDate.getDay() === 6 );
         };

         $scope.open = function($event,opened) {
           $event.preventDefault();
           $event.stopPropagation();
           $scope.dateOpened = true;
           console.log('opened');
         };

         $scope.dateOpened = false;
         $scope.hourStep = 1;
         $scope.minuteStep = 1;

         $scope.timeOptions = {
           hourStep: [1, 2, 3],
           minuteStep: [1, 5, 10, 15, 25, 30]
         };

         $scope.showMeridian = true;
         $scope.timeToggleMode = function() {
           $scope.showMeridian = !$scope.showMeridian;
         };

         $scope.$watch("reading_time", function(date) {
             $scope.reading_time=date;

           // read date value
         }, true);

         $scope.resetHours = function() {
           $scope.reading_time.setHours(1);
         };


        if ($state.is('app.atg_readings_edit')) {

            if ($state.params.id != '') {
                $rootScope.loading = true;
                CRUD.findById(viewurl, $state.params.id).then(function (response) {
                    $scope.isUpdateForm = true;
                    var data = response.data.data;

                    $scope.atgReadingData = {
                        name: data.name,
                        litre_readings: data.litre_readings,
                        tank_id: data.tank_id,
                        site_id: data.site_id,
                        reading_time: new Date(data.reading_time),
                    };
                    $scope.reading_time=new Date(data.reading_time);
                    $rootScope.loading = false;
                }, function (error) {
                    $rootScope.loading = false;
                    //toaster.pop('error','',error.data.message);
                });
            }
            $scope.atgReadingData = {};

        }

        $scope.getAllReadings = function () {
            CRUD.getAll(listurl).then(success).catch(failed);
            function success(res) {
                $scope.sites = res.data;
                if ($scope.sites.data.length) {
                    if ($localStorage.site_id) {
                        angular.forEach($scope.sites.data, function (value, key) {
                            if (value.id == $localStorage.site_id) {
                                $scope.selectedSite = value;
                            }
                        });
                    }
                    else {
                        $rootScope.siteData = $scope.sites.data[0];
                        $scope.selectedSite = $scope.sites.data[0];
                        $localStorage.site_id = $scope.sites.data[0].id;
                    }
                    $scope.rowCollection = $scope.selectedSite.atg_readings;
                    $scope.displayCollection = $scope.selectedSite.atg_readings;
                }
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error','',error.data.message);
                //vm.errorMessage = error.message;
            }

        };

        $scope.createReading = function (site_id) {
            $rootScope.site_id = site_id;
            $state.go('app.atg_readings_new');
        }

        $scope.changeSite = function () {
            $rootScope.siteData = $scope.selectedSite;
            $localStorage.site_id = $scope.selectedSite.id;
            $scope.rowCollection = $scope.selectedSite.atg_readings;
            $scope.displayCollection = $scope.selectedSite.atg_readings;
        }

        $scope.create = function () {
            $rootScope.loading = true;
            if ($state.is('app.atg_readings_edit')) {
                CRUD.getWithData('atgreadings/' + $state.params.id + '/edit').then(success).catch(failed);
            }
            else {
                CRUD.getWithData(createurl + $rootScope.site_id).then(success).catch(failed);
            }
            function success(res) {
                $rootScope.loading = false;
                $scope.tanks = res.data;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error', '', error.data.message);
            }

        }

        $scope.save = function (isValid) {

            $scope.readingTime = $filter('date')($scope.reading_time,"yyyy-MM-dd H:m:s");
            if (isValid) {
                var data = $scope.atgReadingData;
                var filterData = {
                    name: data.name,
                    litre_readings: data.litre_readings,
                    tank_id: data.tank_id,
                    reading_time: $scope.readingTime,
                };
                if ($state.is('app.atg_readings_edit')) {
                    filterData.site_id = data.site_id;
                    $rootScope.loading = true;
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Attendants", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.atg_readings');
                    }, function (res) {
                        if (res.status == 400) {
                            angular.forEach(res.data.validation_errors, function (value, key) {
                                toaster.pop('error', '', value[0]);
                            });
                        }
                        $rootScope.loading = false;
                    });
                }
                else {
                    if ($rootScope.site_id) {
                        filterData.site_id = $rootScope.site_id;
                        $rootScope.loading = true;
                        CRUD.store(saveurl, $.param(filterData)).then(function (response) {

                            if (response.data.status == "success") {
                                toaster.pop('success', "Attendants", response.data.message);
                            }
                            $scope.atgReadingData = {};
                            $rootScope.loading = false;
                            $state.go('app.atg_readings');

                        }, function (res) {
                            if (res.status == 400) {
                                angular.forEach(res.data.validation_errors, function (value, key) {
                                    toaster.pop('error', '', value[0]);
                                });
                            }
                            $rootScope.loading = false;
                        });
                    } else {
                        toaster.pop('error', "Attendants", "Whoops Something Wrong.");
                        $state.go('app.attendants');
                    }
                }
            }

        }

        $scope.deleteConfirm = function (ID) {
            var modalInstance = $modal.open({
                templateUrl: 'uploadSIGNFileModal.html',
                controller: ('remove', ['$scope', '$rootScope', '$localStorage', '$modalInstance', 'toaster', 'ID', remove]),
                size: 'med',
                resolve: {
                    ID: function () {
                        return ID;
                    },

                }
            });

            modalInstance.result.then(function (data) {
                if (data) {
                    $scope.deleteReading(data);
                }
            });

        }
        function remove($scope, $rootScope, $localStorage, $modalInstance, toaster, ID) {

            $scope.confirm = function () {
                $modalInstance.close(ID);
            }
            $scope.closeModal = function () {
                $modalInstance.close();
            }
        }

        $scope.deleteReading = function (id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    toaster.pop('success', "Attendants", res.data.message);
                    $rootScope.loading = false;
                    $scope.getAllReadings();
                }

            }, function (error) {
                $rootScope.loading = false;
                toaster.pop('error', '', error.data.message);
            });

        };

    }]);