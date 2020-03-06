'use strict';

/* Controllers */
// signin controller
app
    .controller('FuelTransferController', ['$scope', '$http', '$state', 'CRUD', 'toaster', '$rootScope', '$localStorage', '$modal', function ($scope, $http, $state, CRUD, toaster, $rootScope, $localStorage, $modal) {

        var listurl = 'fueltransfers';
        var createurl = 'fueltransfers/create';
        var viewurl = 'fueltransfers/'; //  user id required
        var saveurl = 'fueltransfers';
        var updateurl = 'fueltransfers/update/'; // user id required
        var deleteurl = 'fueltransfers/delete/'; // user id required

        $scope.isUpdateForm = false;
        $scope.rowCollection = [];
        $scope.selectedSite = '';
        $scope.sites = [];
        $scope.displayCollection = [];

        $scope.getAllFuelTransfer = function () {
            $rootScope.loading = true;
            CRUD.getWithData(listurl, {site_id: $localStorage.site_id}).then(success).catch(failed);
            function success(res) {
                $scope.rowCollection = res.data.data;
                $scope.displayCollection = res.data.data;
                $scope.sites = res.data.sites;
                if ($scope.sites.length) {
                    if ($localStorage.site_id) {
                        angular.forEach($scope.sites, function (value, key) {
                            if (value.id == $localStorage.site_id) {
                                $scope.selectedSite = value;
                                $localStorage.site_id = value.id;
                            }
                        });
                    }
                    else {
                        $rootScope.siteData = $scope.sites[0];
                        $scope.selectedSite = $scope.sites[0];
                        $localStorage.site_id = $scope.selectedSite.id;
                    }
                }
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
            }
        };

        $scope.changeSite = function () {
            $rootScope.siteData = $scope.selectedSite;
            $localStorage.site_id = $scope.selectedSite.id;
            $scope.getAllFuelTransfer();
        }

        $scope.changeFromSite = function () {

            angular.forEach($scope.sites, function (value, key) {
                if (value.id == $scope.fuelTransferData.from_site) {
                    $scope.from_tanks = value.tanks;
                }
            })
        }
        $scope.changeToSite = function () {
            angular.forEach($scope.sites, function (value, key) {
                if (value.id == $scope.fuelTransferData.to_site) {
                    $scope.to_tank = value.tanks;
                }
            })
            //$scope.to_tank = $scope.fuelTransferData.to_site.tanks;
        }

        $scope.maxCheck = function () {
            angular.forEach($scope.sites, function (value, key) {
                if (value.id == $scope.fuelTransferData.from_site) {
                    angular.forEach(value.tanks, function (value1, key1) {
                        if (value1.id == $scope.fuelTransferData.from_tank) {
                            $scope.max_litres = value1.litre;
                        }
                    })
                }
            })

        }

        $scope.create = function () {
            $rootScope.loading = true;
            if ($state.is('app.fuel_transfer_edit')) {
                CRUD.getWithData('fueltransfers/' + $state.params.id + '/edit').then(success).catch(failed);
            }
            else {
                CRUD.getWithData(createurl).then(success).catch(failed);
            }
            function success(res) {
                $rootScope.loading = true;
                $scope.sites = res.data.data.sites;

                if ($state.is('app.fuel_transfer_edit')) {
                    if ($state.params.id != '') {
                        CRUD.findById(viewurl, $state.params.id).then(function (response) {
                            $scope.isUpdateForm = true;
                            var data = response.data.data;

                            $scope.fuelTransferData = {
                                from_site: data.from_site,
                                from_tank: data.from_tank.toString(),
                                to_site: data.to_site,
                                to_tank: data.to_tank.toString(),
                                litres: parseInt(data.litres),
                            };

                            angular.forEach($scope.sites, function (value, key) {
                                if (value.id == data.from_site) {
                                    //console.log(value.tanks)
                                    $scope.from_tanks = value.tanks;
                                }
                            })
                            angular.forEach($scope.sites, function (value, key) {
                                if (value.id == data.to_site) {
                                    $scope.to_tank = value.tanks;
                                }
                            })
                        }, function (error) {
                            //toaster.pop('error','',error.data.message);
                        });
                    }
                    $scope.fuelTransferData = {};
                }
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error', '', error.data.message);
            }

        }

        $scope.save = function (isValid) {

            if (isValid) {
                var data = $scope.fuelTransferData;
                var filterData = {
                    from_site: data.from_site,
                    from_tank: data.from_tank,
                    to_site: data.to_site,
                    to_tank: data.to_tank,
                    litres: data.litres,
                };

                if ($state.is('app.fuel_transfer_edit')) {
                    $rootScope.loading = true;
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Fuel Transfer", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.fuel_transfer');
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
                    $rootScope.loading = true;
                    CRUD.store(saveurl, $.param(filterData)).then(function (response) {

                        if (response.data.status == "success") {
                            toaster.pop('success', "Fuel Transfer", response.data.message);
                        }
                        $scope.fuelTransferData = {};
                        $rootScope.loading = false;
                        $state.go('app.fuel_transfer');

                    }, function (res) {
                        if (res.status == 400) {
                            angular.forEach(res.data.validation_errors, function (value, key) {
                                toaster.pop('error', '', value[0]);
                            });
                        }
                        $rootScope.loading = false;
                    });

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
                    $scope.deleteFuelTransfer(data);
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

        $scope.deleteFuelTransfer = function (id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    toaster.pop('success', "Fuel Transfer", res.data.message);
                    $rootScope.loading = false;
                    $scope.getAllFuelTransfer();
                }

            }, function (error) {
                $rootScope.loading = false;
                toaster.pop('error', '', error.data.message);
            });

        };


    }]);