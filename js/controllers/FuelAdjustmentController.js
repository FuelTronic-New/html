'use strict';

/* Controllers */
// signin controller
app
    .controller('FuelAdjustmentController', ['$scope', '$http', '$state', 'CRUD', 'toaster', '$rootScope', '$modal', '$timeout', '$localStorage', function ($scope, $http, $state, CRUD, toaster, $rootScope, $modal, $timeout, $localStorage) {

        var listurl = 'fueladjustments';
        var createurl = 'fueladjustments/create';
        var viewurl = 'fueladjustments/'; //  user id required
        var saveurl = 'fueladjustments';
        var updateurl = 'fueladjustments/update/'; // user id required
        var deleteurl = 'fueladjustments/delete/'; // user id required

        $scope.isUpdateForm = false;
        $scope.rowCollection = [];
        $scope.selectedSite = '';
        $scope.sites = [];
        $scope.displayCollection = [];
        $scope.fuelData = [];

        $timeout(function () {
            $scope.$watch('fuelData.motivation', function(newVal, oldVal) {
                if (newVal) {
                    if (newVal.length > 60) {
                        $scope.fuelData.motivation = oldVal;
                    }
                }
            });
        },500);

        if ($state.is('app.fuel_adjustment_edit')) {
            if ($state.params.id != '') {
                CRUD.findById(viewurl, $state.params.id).then(function (response) {
                    $scope.isUpdateForm = true;
                    var data = response.data.data;
                    $scope.fuelData = {
                        tank_id: data.tank_id,
                        litres: data.litres,
                        motivation: data.motivation,
                        site_id: data.site_id,
                    };
                    if (data.mode == '+') {
                        $scope.fuelData.mode = 'add';
                    }
                    if (data.mode == '-') {
                        $scope.fuelData.mode = 'remove';
                    }
                }, function (error) {
                });
            }
            $scope.fuelData = {};
        }

        $scope.getAllFuelAdjustment = function () {
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
                    $scope.rowCollection = $scope.selectedSite.fuel_adjustments;
                    $scope.displayCollection = $scope.selectedSite.fuel_adjustments;
                }
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error','',error.data.message);
                //vm.errorMessage = error.message;
            }

        };

        $scope.changeSite = function () {
            $rootScope.siteData = $scope.selectedSite;
            $localStorage.site_id = $scope.selectedSite.id;
            $scope.rowCollection = $scope.selectedSite.fuel_adjustments;
            $scope.displayCollection = $scope.selectedSite.fuel_adjustments;
        }

        $scope.createFuelAdjustment = function (site_id) {
            $rootScope.site_id = site_id;
            $state.go('app.fuel_adjustment_new');
        }

        $scope.create = function () {
            $scope.fuelData = {
                mode: 'add'
            }
            if ($state.is('app.fuel_adjustment_edit')) {
                CRUD.getWithData('fueladjustments/' + $state.params.id + '/edit').then(success).catch(failed);
            }
            else {
                CRUD.getWithData(createurl + '/' + $rootScope.site_id).then(success).catch(failed);
            }
            function success(res) {
                $scope.tanks = res.data.data.sites[0].tanks;
            }

            function failed(error) {
            }

        }

        $scope.save = function (isValid) {

            if (isValid) {
                var data = $scope.fuelData;
                var filterData = {
                    tank_id: data.tank_id,
                    motivation: data.motivation,
                    litres: data.litres
                }
                if ($scope.fuelData.mode == 'add') {
                    filterData.mode = '+';
                }
                else {
                    filterData.mode = '-';
                }

                if ($state.is('app.fuel_adjustment_edit')) {
                    filterData.site_id = data.site_id;
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Fuel Adjustment", response.data.message);
                        $state.go('app.fuel_adjustment');
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
                        CRUD.store(saveurl, $.param(filterData)).then(function (response) {

                            if (response.data.status == "success") {
                                toaster.pop('success', "Fuel Adjustment", response.data.message);
                            }
                            $scope.fuelData = {};
                            $state.go('app.fuel_adjustment');

                        }, function (res) {
                            if (res.status == 400) {
                                angular.forEach(res.data.validation_errors, function (value, key) {
                                    toaster.pop('error', '', value[0]);
                                });
                            }
                        });
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
                    $scope.deleteFuelAdjustment(data);
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

        $scope.deleteFuelAdjustment = function (id) {
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    toaster.pop('success', "Fuel Adjustment", res.data.message);
                    $scope.getAllFuelAdjustment();
                }

            }, function (error) {
                toaster.pop('error', '', error.data.message);
            });

        };

    }]);