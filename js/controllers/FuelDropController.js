'use strict';

/* Controllers */
// Pump controller
app
    .controller('FuelDropController', ['$scope', '$http', '$state', '$localStorage', 'CRUD', 'toaster', '$filter', '$location', '$rootScope','$modal', function ($scope, $http, $state, $localStorage, CRUD, toaster, $filter, $location, $rootScope,$modal) {

        var listurl = 'fueldrops';

        var createurl = 'fueldrops/create';
        var viewurl = 'fueldrops/'; //  user id required
        var saveurl = 'fueldrops';
        var updateurl = 'fueldrops/update/'; // user id required
        var deleteurl = 'fueldrops/delete/'; // user id required

        $scope.isUpdateForm = false;
        $scope.rowCollection = [];
        $scope.selectedSite = '';
        $scope.sites = [];
        $scope.displayCollection = [];

        $scope.format = 'dd-MMMM-yyyy';

        $scope.dateTimeNow = function () {
            $scope.purchase_date = new Date();
        };

        $scope.dateTimeNow();

        $scope.toggleMinDate = function () {
            $scope.minDate = $scope.minDate ? null : new Date();
        };

        $scope.maxDate = new Date('2014-06-22');
        $scope.toggleMinDate();

        $scope.dateOptions = {
            showWeeks: true
        };

        // Disable weekend selection
        $scope.disabled = function (calendarDate, mode) {
            return mode === 'day' && ( calendarDate.getDay() === 0 || calendarDate.getDay() === 6 );
        };

        $scope.open = function ($event, opened) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.dateOpened = true;
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

        $scope.$watch("purchase_date", function (date) {
            $scope.purchase_date = date;

            // read date value
        }, true);

        $scope.resetHours = function () {
            $scope.purchase_date.setHours(1);
        };


        if ($state.is('app.fuel_drop_edit')) {

            if ($state.params.id != '') {
                $rootScope.loading = true;
                CRUD.findById(viewurl, $state.params.id).then(function (response) {
                    $scope.isUpdateForm = true;
                    var data = response.data.data;

                    $scope.fuelData = {
                        supplier_id: data.supplier_id,
                        tank_id: data.tank_id,
                        litres: data.litres,
                        purchase_date: new Date(data.purchase_date),
                        site_id: data.site_id,
                        tot_exc_vat: data.tot_exc_vat,
                        vat: data.vat,
                        tot_inc_vat: data.tot_inc_vat,
                    };

                    $scope.purchase_date = new Date(data.purchase_date);
                    $rootScope.loading = false;
                }, function (error) {
                    $rootScope.loading = false;
                    //toaster.pop('error','',error.data.message);
                });
            }
            $scope.fuelData = {};

        }

        $scope.getAllFuelDrop = function () {
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
                    $scope.rowCollection = $scope.selectedSite.fuel_drops;
                    $scope.displayCollection = $scope.selectedSite.fuel_drops;
                }
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error','',error.data.message);
            }

        };

        $scope.changeSite = function () {
            $rootScope.siteData = $scope.selectedSite;
            $localStorage.site_id = $scope.selectedSite.id;
            $scope.rowCollection = $scope.selectedSite.fuel_drops;
            $scope.displayCollection = $scope.selectedSite.fuel_drops;
        }

        $scope.createFuelDrop = function (site_id) {
            console.log(site_id)
            $rootScope.site_id = site_id;
            $state.go('app.fuel_drop_new');
        }

        $scope.create = function () {
            $rootScope.loading = true;
            $scope.fuelData = {
                supplier_id: '',
                tank_id: '',
                litres: '',
                purchase_date: '',
                site_id: ''
            };

            if ($state.is('app.fuel_drop_edit')) {
                CRUD.getWithData('fueldrops/' + $state.params.id + '/edit').then(success).catch(failed);
            }
            else {
                CRUD.getWithData(createurl + '/' + $rootScope.site_id).then(success).catch(failed);
            }
            function success(res) {
                if (res.data.data.sites[0]) {
                    $scope.suppliers = res.data.data.sites[0].suppliers;
                }
                if (res.data.data.sites[0]) {
                    $scope.tanks = res.data.data.sites[0].tanks;
                }
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error','',error.data.message);
            }

        }

        $scope.save = function (isValid) {

            $scope.purchaseDate = $filter('date')($scope.purchase_date,"yyyy-MM-dd H:m:s");
            if (isValid) {
                var data = $scope.fuelData;
                if (data.vat > 100) {
                    toaster.pop('error', 'Fuel Drop', 'Vat must be less than 100.!');
                    return;
                }
                var filterData = {
                    supplier_id: data.supplier_id,
                    tank_id: data.tank_id,
                    litres: data.litres,
                    tot_exc_vat: data.tot_exc_vat,
                    vat: data.vat,
                    tot_inc_vat: data.tot_inc_vat,
                    purchase_date: $scope.purchaseDate,
                };

                if ($state.is('app.fuel_drop_edit')) {
                    filterData.site_id = data.site_id;
                    $rootScope.loading = true;
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Fuel Drop", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.fuel_drop');
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
                                toaster.pop('success', "Fuel Drop", response.data.message);
                            }
                            $rootScope.loading = false;
                            $scope.fuelData = {};
                            $state.go('app.fuel_drop');

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
                    $scope.deleteFuelDrop(data);
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

        $scope.deleteFuelDrop = function (id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    toaster.pop('success', "Fuel Drop", res.data.message);
                    $rootScope.loading = false;
                    $scope.getAllFuelDrop();
                }

            }, function (error) {
                $rootScope.loading = false;
                toaster.pop('error', '', error.data.message);
            });

        };

        $scope.totalvat = function () {
            if ($scope.fuelData.vat > 100) {
                toaster.pop('error', 'Fuel Drop', 'Vat must be less than 100.!');
                return;
            }
            $scope.fuelData.tot_inc_vat = (parseFloat($scope.fuelData.litres) * parseFloat($scope.fuelData.tot_exc_vat) * ((parseFloat($scope.fuelData.vat) / 100) + 1));
            // $scope.fuelData.tot_inc_vat = (parseFloat($scope.fuelData.vat) * parseFloat($scope.fuelData.tot_exc_vat)) / 100;
        }
    }]);