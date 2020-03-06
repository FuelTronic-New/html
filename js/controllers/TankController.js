'use strict';

/* Controllers */
// Tank controller
app

    .controller('TankController', ['$scope', '$http', '$state', '$localStorage', 'CRUD', 'toaster', '$filter', '$location', '$rootScope', '$modal', function ($scope, $http, $state, $localStorage, CRUD, toaster, $filter, $location, $rootScope, $modal) {

        var listurl = 'tanks';
        var createurl = 'tanks/create/';
        var viewurl = 'tanks/'; //  user id required
        var saveurl = 'tanks';
        var updateurl = 'tanks/update/'; // user id required
        var deleteurl = 'tanks/delete/'; // user id required

        $scope.isUpdateForm = false;
        $scope.rowCollection = [];
                $scope.selectedSite = '';
                $scope.sites = [];
                $scope.displayCollection = [];

        if ($state.is('app.tanks_edit')) {

            if ($state.params.id != '') {
                $rootScope.loading = true;
                CRUD.findById(viewurl, $state.params.id).then(function (response) {
                    $scope.isUpdateForm = true;
                    var data = response.data.data.tank;

                    $scope.tankData = {
                        name: data.name,
                        grade_id: data.grade_id,
                        site_id: data.site_id,
                        //optional1: data.optional1,
                        volume: data.volume,
                        initial_level: data.initial_level,
                        min_level: data.min_level,
                        last_dip_reading: data.last_dip_reading,
                        cur_atg_level: data.cur_atg_level,
                        atg: data.atg,
                        manual_reading: data.manual_reading,
                        status: data.status,
                        cur_level_stock : data.litre,
                        atg_id : data.atg_id,
                    };
                    $rootScope.loading = false;
                    $scope.atgList = response.data.data.atg;
                }, function (error) {
                    $rootScope.loading = false;
                    //toaster.pop('error','',error.data.message);
                });
            }
            $scope.tankData = {};

        }

        $scope.getAllTanks = function () {
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
                    $scope.rowCollection = $scope.selectedSite.tanks;
                    $scope.displayCollection = $scope.selectedSite.tanks;
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
            $scope.rowCollection = $scope.selectedSite.tanks;
            $scope.displayCollection = $scope.selectedSite.tanks;
        }

        $scope.createTank = function (site_id) {
            $rootScope.site_id = site_id;
            $state.go('app.tanks_new');
        }
        $scope.create = function(){
            $rootScope.loading = true;
            $scope.tankData = {
                initial_level: '',
                volume: '',
                min_level: '',
                manual_reading: 'On',
                atg: 'On',
                status: 'Active',
                cur_level_stock :0
            };

            if($state.is('app.tanks_edit')) {
                CRUD.getWithData('tanks/'+$state.params.id+'/edit').then(success).catch(failed);
            }
            else{
                CRUD.getWithData(createurl + $rootScope.site_id).then(success).catch(failed);
            }
            function success(res) {
                $scope.grades = res.data.data.grades;
                $scope.atgList = res.data.data.atg;
                if($state.is('app.tanks_edit')) {
                    $scope.cur_level_stock = $scope.tankData.cur_level_stock;
                }else{
                    $scope.cur_level_stock = 0;
                }
                $scope.tankData.last_dip_reading = res.data.data.lastDipReading;
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error','',error.data.message);
            }

        }

        $scope.save = function (isValid) {

            if (isValid) {
                var data = $scope.tankData;
                var filterData = {
                    name: data.name,
                    grade_id: data.grade_id,
                    //optional1: data.optional1,
                    volume: data.volume,
                    initial_level: data.initial_level,
                    litre: data.initial_level,
                    min_level: data.min_level,
                    cur_level_stock: data.cur_level_stock,
                    last_dip_reading: data.last_dip_reading,
                    cur_atg_level: data.cur_atg_level,
                    atg: data.atg,
                    manual_reading: data.manual_reading,
                    status: data.status,
                    atg_id: (data.atg=='On') ? data.atg_id : 0,
                };
                if ($state.is('app.tanks_edit')) {
                    filterData.site_id = data.site_id;
                    $rootScope.loading = true;
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Tank", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.tanks');
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
                                toaster.pop('success', "Tank", response.data.message);
                            }
                            $rootScope.loading = false;
                            $scope.tankData = {};
                            $state.go('app.tanks');

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
                    $scope.deleteTank(data);
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

        $scope.total_level = function(){
            $scope.tankData.cur_level_stock = parseFloat($scope.cur_level_stock) + parseFloat($scope.tankData.initial_level);
        }

        $scope.deleteTank = function (id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    $rootScope.loading = false;
                    toaster.pop('success', "Tank", res.data.message);
                    $scope.getAllTanks();
                }

            }, function (error) {
                $rootScope.loading = false;
                toaster.pop('error','',error.data.message);
            });

        };
    }]);