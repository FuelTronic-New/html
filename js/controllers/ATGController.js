'use strict';

/* Controllers */
// ATG Reading controller
app
    .controller('ATGController', ['$scope', '$state', 'CRUD', 'toaster', '$rootScope','$modal','$localStorage', function ($scope, $state, CRUD, toaster, $rootScope,$modal, $localStorage) {
        $rootScope.loading = false;
        var listurl = 'atgdata';
        var createurl = 'atgdata/create/';
        var viewurl = 'atgdata/'; //  user id required
        var saveurl = 'atgdata';
        var updateurl = 'atgdata/update/'; // user id required
        var deleteurl = 'atgdata/delete/'; // user id required
        var guidurl = 'atgdata/guid';

        $scope.isUpdateForm = false;
        $scope.rowCollection = [];
        $scope.selectedSite = '';
        $scope.sites = [];
        $scope.displayCollection = [];

        if ($state.is('app.atg_edit')) {

            if ($state.params.id != '') {
                $rootScope.loading = true;
                CRUD.findById(viewurl, $state.params.id).then(function (response) {
                    $scope.isUpdateForm = true;
                    var data = response.data.data;

                    $scope.atgData = {
                        name: data.name,
                        litrname: data.name,
                        ip_address: data.ip_address,
                        port_num: data.port_num.toString(),
                        tank_type: data.tank_type,
                        sensor_height: data.sensor_height,
                        fill_height: data.fill_height,
                        riser_height: data.riser_height,
                        tank_height: data.tank_height,
                        cylinder_length: data.cylinder_length,
                        endcap_length: data.endcap_length,
                        tank_diameter: data.tank_diameter,
                        height: data.height,
                        guid: data.guid,
                    };
                    $rootScope.loading = false;
                }, function (error) {
                    //toaster.pop('error','',error.data.message);
                });
            }
            $scope.atgData = {};
        }

//////////////////////////////////////////  Site Change  //////////////////////////////////////////////////////////////
        $scope.changeSite = function (site_id) {
            $localStorage.site_id = site_id;
            $scope.getAllATG();
        }
        $scope.getAllATG = function () {
            $rootScope.loading = true;
            CRUD.getWithData(listurl, {site_id: $localStorage.site_id}).then(success).catch(failed);
            function success(res) {
                $scope.rowCollection = res.data.data.atg;
                $scope.displayCollection = res.data.data.atg;
                $rootScope.loading = false;
                $scope.sites = res.data.data.sites;

                if ($scope.sites.length) {
                    if ($localStorage.site_id) {
                        angular.forEach($scope.sites, function (value, key) {
                            if (value.id == $localStorage.site_id) {
                                $scope.selectedSite = value.id;
                            }
                        });
                    }
                    else {
                        $rootScope.siteData = $scope.sites[0];
                        $scope.selectedSite = $scope.sites[0].id;
                        $localStorage.site_id = $scope.sites[0].id;
                    }
                    //$scope.selectedSite = (site_id) ? site_id : $scope.sites[0].id;
                    for (var row in $scope.customers) {
                        if ($scope.customers[row].customerSites.indexOf($scope.selectedSite) != -1) {
                            $scope.rowCollection.push($scope.customers[row]);
                            $scope.displayCollection.push($scope.customers[row]);
                        }
                    }
                }
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error','',error.data.message);
                //vm.errorMessage = error.message;
            }
        };

        $scope.createATG = function (site_id) {
            $rootScope.site_id = site_id;
            $state.go('app.atg_readings_new');
        }

        $scope.generateGuid = function () {
            $rootScope.loading = true;
            CRUD.getAll(guidurl).then(success).catch(failed);
            function success(res) {
                $scope.atgData.guid = res.data.guid;
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                toaster.pop('error', '', error.data.message);
            }
        }
        $scope.create = function () {
            var siteID = ($state.params.site_id) ? $state.params.site_id : $localStorage.site_id;
            $rootScope.loading = true;
            $scope.atgData = {
                name: '',
                ip: '',
                code: '',
                guid: '',
                attendent_tag: '',
                vehicle_tag: '',
                odo_meter: '',
                pin: '',
                job_tag: ''
            };
            if ($state.is('app.atg_edit')) {
                CRUD.getWithData('atgdata/' + $state.params.id + '/edit').then(success).catch(failed);
            }
            else {
                CRUD.getWithData(createurl + siteID).then(success).catch(failed);
            }
            function success(res) {
                $scope.tanks = res.data;
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error', '', error.data.message);
            }
        }

        $scope.save = function (isValid) {
            var siteID = ($state.params.site_id) ? $state.params.site_id : $localStorage.site_id;
            //console.log($scope.reading_time);
            //return;
            if (isValid) {
                var data = $scope.atgData;
                var filterData = {
                    name: data.name,
                    ip_address: data.ip_address,
                    port_num: data.port_num,
                    tank_type: data.tank_type,
                    sensor_height: data.sensor_height,
                    fill_height: data.fill_height,
                    riser_height: data.riser_height,
                    tank_height: data.tank_height,
                    cylinder_length: data.cylinder_length,
                    endcap_length: data.endcap_length,
                    tank_diameter: data.tank_diameter,
                    height: data.height,
                    guid: data.guid,
                };
                if ($state.is('app.atg_edit')) {
                    $rootScope.loading = true;
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "ATG", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.atg');
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
                    filterData.site_id = siteID;
                    $rootScope.loading = true;
                    CRUD.store(saveurl, $.param(filterData)).then(function (response) {

                        if (response.data.status == "success") {
                            toaster.pop('success', "ATG", response.data.message);
                        }
                        $scope.atgData = {};
                        $rootScope.loading = false;
                        $state.go('app.atg');

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
                    $scope.deleteATG(data);
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

        $scope.deleteATG = function (id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    toaster.pop('success', "ATG", res.data.message);
                    $rootScope.loading = false;
                    $scope.getAllATG();
                }

            }, function (error) {
                $rootScope.loading = false;
                toaster.pop('error', '', error.data.message);
            });
        };

    }]);