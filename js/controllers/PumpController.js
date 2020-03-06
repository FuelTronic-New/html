'use strict';

/* Controllers */
// Pump controller
app
    .controller('PumpController', ['$scope', '$http', '$state', '$localStorage', 'CRUD', 'toaster', '$filter', '$location', '$rootScope', '$modal', function ($scope, $http, $state, $localStorage, CRUD, toaster, $filter, $location, $rootScope, $modal) {

        var listurl = 'pumps';
        var guidurl = 'pumps/guid';
        var createurl = 'pumps/create';
        var viewurl = 'pumps/'; //  user id required
        var saveurl = 'pumps';
        var updateurl = 'pumps/update/'; // user id required
        var deleteurl = 'pumps/delete/'; // user id required

        $scope.isUpdateForm=false;
        $scope.rowCollection = [];
                $scope.selectedSite = '';
                $scope.sites = [];
                $scope.displayCollection = [];

        if($state.is('app.pumps_edit')) {

            if($state.params.id != ''){
                $rootScope.loading = true;
                CRUD.findById(viewurl,$state.params.id).then(function(response){
                    $scope.isUpdateForm=true;
                    var data=response.data.data;

                    $scope.pumpData = {
                        name: data.name,
                        ip: data.ip,
                        code: data.code,
                        guid: data.guid,
                        attendent_tag: (data.attendent_tag != null) ? data.attendent_tag.toString() : '',
                        vehicle_tag: (data.vehicle_tag != null) ? data.vehicle_tag.toString() : '',
                        odo_meter: data.odo_meter,
						driver_fingerprint: data.driver_fingerprint,
                        order_number: data.order_number,
                        pin: data.pin,
                        location: (data.location != null) ? data.location.toString() : "",
                        job_tag: (data.job_tag != null) ? data.job_tag.toString() : '',
                        site_id: data.site_id
                    };
                    $rootScope.loading = false;
                },function(error){
                    $rootScope.loading = false;
                    //toaster.pop('error','',error.data.message);
                });
            }
            $scope.pumpData={};
        }

        $scope.getAllPumps = function () {
            $rootScope.loading = true;
            CRUD.getAll(listurl).then(success).catch(failed);
            function success(res) {
                $scope.sites = res.data;
                if ($scope.sites.data && $scope.sites.data.length) {
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
                    $scope.rowCollection = $scope.selectedSite.pumps;
                    $scope.displayCollection = $scope.selectedSite.pumps;
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
            $scope.rowCollection = $scope.selectedSite.pumps;
            $scope.displayCollection = $scope.selectedSite.pumps;
        }

        $scope.createPump = function (site_id) {
            $rootScope.site_id = site_id;
            $state.go('app.pumps_new');
        }

        $scope.initForm = function(){
            $scope.pumpData = {
                name: '',
                ip: '',
                code: '',
                guid: '',
                attendent_tag: '',
                vehicle_tag: '',
                odo_meter: '',
         		driver_fingerprint: '',
                order_number: '',
                pin: '',
                job_tag: ''
            };
        }

        $scope.generateGuid = function () {
            $rootScope.loading = true;
            CRUD.getAll(guidurl).then(success).catch(failed);
            function success(res) {
                $scope.pumpData.guid = res.data.guid;
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                toaster.pop('error','',error.data.message);
            }

        }

        $scope.save = function (isValid) {

            if(isValid)
            {
                var data=$scope.pumpData;
                var filterData = {
                    name: data.name,
                    ip: data.ip,
                    code: data.code,
                    guid: data.guid,
                    attendent_tag: data.attendent_tag,
                    vehicle_tag: data.vehicle_tag,
                    odo_meter: data.odo_meter,
					driver_fingerprint: data.driver_fingerprint,
                    order_number: data.order_number,
                    pin: data.pin,
                    location: data.location,
                    job_tag: data.job_tag,
                 };

                if($state.is('app.pumps_edit'))
                {
                    filterData.site_id = data.site_id;
                    $rootScope.loading = true;
                    CRUD.update(updateurl, $state.params.id,  $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Pump", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.pumps');
                    }, function (res) {
                        if(res.status == 400)
                        {
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
                                toaster.pop('success', "Pump", response.data.message);
                            }
                            $rootScope.loading = false;
                            $scope.pumpData = {};
                            $state.go('app.pumps');

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
                        toaster.pop('error', "Pump", "Whoops Something Wrong");
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
                    $scope.deletePump(data);
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

        $scope.deletePump = function (id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if(res.data.status == "success")
                {
                    toaster.pop('success', "Pump", res.data.message);
                    $rootScope.loading = false;
                    $scope.getAllPumps();
                }

            }, function (error) {
                $rootScope.loading = false;
                toaster.pop('error','',error.data.message);
            });

        };
    }]);