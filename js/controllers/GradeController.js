'use strict';

/* Controllers */
// grade controller
app
    .controller('GradeController', ['$modal','$scope', '$http', '$state', '$localStorage', 'CRUD', 'toaster', '$filter', '$location', '$rootScope', function ($modal,$scope, $http, $state, $localStorage, CRUD, toaster, $filter, $location, $rootScope) {

        var listurl = 'grades';
        var viewurl = 'grades/'; //  user id required
        var saveurl = 'grades';
        var updateurl = 'grades/update/'; // user id required
        var deleteurl = 'grades/delete/'; // user id required

        $scope.isUpdateForm = false;
        $scope.rowCollection = [];
        $scope.selectedSite = '';
        $scope.sites = [];
        $scope.displayCollection = [];

        $scope.format = 'dd-MMMM-yyyy';

        $scope.dateTimeNow = function () {
            $scope.rate_increased_at = new Date();
        };

        $scope.dateTimeNow();

        $scope.toggleMinDate = function () {
            $scope.minDate = $scope.minDate ? null : new Date();
        };

        $scope.maxDate = new Date('2014-06-22');
        $scope.toggleMinDate();

        $scope.dateOptions = {
            showWeeks: false
        };

        // Disable weekend selection
        $scope.disabled = function (calendarDate, mode) {
            return mode === 'day' && ( calendarDate.getDay() === 0 || calendarDate.getDay() === 6 );
        };

        $scope.open = function ($event, opened) {
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
        $scope.timeToggleMode = function () {
            $scope.showMeridian = !$scope.showMeridian;
        };

        $scope.$watch("rate_increased_at", function (date) {
            $scope.rate_increased_at = date;

            // read date value
        }, true);

        $scope.resetHours = function () {
            $scope.rate_increased_at.setHours(1);
        };


        if ($state.is('app.grades_edit')) {
            if ($state.params.id != '') {
                $rootScope.loading = true;
                CRUD.findById(viewurl, $state.params.id).then(function (response) {
                    $scope.isUpdateForm = true;
                    var data = response.data.data;

                    $scope.gradeData = {
                        name: data.name,
                        price: data.price,
                        optional1: data.optional1,
                        site_id: data.site_id,
                        new_rate: data.new_rate,
                       // cur_rate: data.cur_rate,
                        vat_rate: data.vat_rate,
                        rate_increased_at: new Date(data.rate_increased_at),
                    };
                    $scope.rate_increased_at = new Date(data.rate_increased_at);
                    $rootScope.loading = false;
                }, function (error) {
                    $rootScope.loading = false;
                    //toaster.pop('error','',error.data.message);
                });
            }
            $scope.gradeData = {};
        }

        $scope.getAllGrades = function () {
            $rootScope.loading = true;
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
                    $scope.rowCollection = $scope.selectedSite.grades;
                    $scope.displayCollection = $scope.selectedSite.grades;
                }
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error','',error.data.message);
            }

        };

        $scope.createGrade = function (site_id) {
            $rootScope.site_id = site_id;
            $state.go('app.grades_new');
        }

        $scope.changeSite = function () {
            $rootScope.siteData = $scope.selectedSite;
            $localStorage.site_id = $scope.selectedSite.id;
            $scope.rowCollection = $scope.selectedSite.grades;
            $scope.displayCollection = $scope.selectedSite.grades;
        }

        $scope.save = function (isValid) {

            $scope.rateIncreaseAt = $filter('date')($scope.rate_increased_at,"yyyy-MM-dd H:m:s");
            if (isValid) {
                var data = $scope.gradeData;
                var filterData = {
                    name: data.name,
                    price: data.price,
                    new_rate: data.new_rate,
                    //cur_rate: data.cur_rate,
                    vat_rate: data.vat_rate,
                    rate_increased_at: $scope.rateIncreaseAt,
                };
                if ($state.is('app.grades_edit')) {
                    filterData.site_id = data.site_id;
                    $rootScope.loading = true;
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Grade", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.grades');
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
                                toaster.pop('success', "Grade", response.data.message);
                            }
                            $rootScope.loading = false;
                            $scope.gradeData = {};
                            $state.go('app.grades');

                        }, function (res) {
                            if (res.status == 400) {
                                angular.forEach(res.data.validation_errors, function (value, key) {
                                    toaster.pop('error', '', value[0]);
                                });
                            }
                            $rootScope.loading = false;
                        });
                    } else {
                        toaster.pop('error', '', 'Whoops Something Wrong');
                    }
                }
            }

        }

        $scope.deleteConfirm = function (ID) {
            var modalInstance = $modal.open({
                templateUrl: 'deleteFileModal.html',
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
                    $scope.deleteGrade(data);
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

        $scope.deleteGrade = function (id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    toaster.pop('success', "Grade", res.data.message);
                    $rootScope.loading = false;
                    $scope.getAllGrades();
                }

            }, function (error) {
                $rootScope.loading = false;
                toaster.pop('error','',error.data.message);
            });

        };

    }]);