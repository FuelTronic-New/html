'use strict';

/* Controllers */
// hoses controller
app
    .controller('HosesController', ['$scope', '$http', '$state', '$localStorage', 'CRUD', 'toaster', '$filter', '$location','$rootScope','$modal', function ($scope, $http, $state, $localStorage, CRUD, toaster, $filter, $location,$rootScope,$modal) {

        var listurl = 'hoses';
        var createurl = 'hoses/create/';
        var viewurl = 'hoses/'; //  user id required
        var saveurl = 'hoses';
        var updateurl = 'hoses/update/'; // user id required
        var deleteurl = 'hoses/delete/'; // user id required

        $scope.isUpdateForm = false;
        $scope.rowCollection = [];
                $scope.selectedSite = '';
                $scope.sites = [];
                $scope.displayCollection = [];

        if ($state.is('app.hoses_edit')) {
            $rootScope.loading = true;
            if ($state.params.id != '') {
                CRUD.findById(viewurl, $state.params.id).then(function (response) {
                    $scope.isUpdateForm = true;
                    var data = response.data.data;

                    $scope.hosesData = {
                        name: data.name,
                        pump_id: data.pump_id,
                        tank_id: data.tank_id.toString(),
                        site_id: data.site_id,
                        optional1: data.optional1,
                    };

                }, function (error) {
                    //toaster.pop('error','',error.data.message);
                });
            }
            $scope.hosesData = {};
            $rootScope.loading = false;
        }

        $scope.getAllHoses = function () {
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
                    $scope.rowCollection = $scope.selectedSite.hoses;
                    $scope.displayCollection = $scope.selectedSite.hoses;
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
            $scope.rowCollection = $scope.selectedSite.hoses;
            $scope.displayCollection = $scope.selectedSite.hoses;
        }

        $scope.createHoses = function (site_id) {
            $rootScope.site_id = site_id;
            $state.go('app.hoses_new');
        }
        $scope.create = function () {
            $rootScope.loading = true;
            if ($state.is('app.hoses_edit')) {
                CRUD.getWithData('hoses/' + $state.params.id + '/edit').then(success).catch(failed);
            }
            else {
                CRUD.getWithData(createurl+ $rootScope.site_id).then(success).catch(failed);
            }
            function success(res) {
                $scope.hoses = res.data;
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error','',error.data.message);
            }

        }

        $scope.save = function (isValid) {

            if (isValid) {
                var data = $scope.hosesData;
                var filterData = {
                    name: data.name,
                    pump_id: data.pump_id,
                    tank_id: data.tank_id,
                    optional1: data.optional1,
                };
                if ($state.is('app.hoses_edit')) {
                    filterData.site_id = data.site_id;
                    $rootScope.loading = true;
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Hose", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.hoses');
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
                                toaster.pop('success', "Hose", response.data.message);
                            }
                            $rootScope.loading = false;
                            $scope.hosesData = {};
                            $state.go('app.hoses');

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
                    $scope.deleteHose(data);
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

        $scope.deleteHose = function (id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    $rootScope.loading = false;
                    toaster.pop('success', "Hose", res.data.message);
                    $scope.getAllHoses();
                }

            }, function (error) {
                $rootScope.loading = false;
                toaster.pop('error','',error.data.message);
            });

        };

    }]);