'use strict';

/* Controllers */
// Vehicle controller
app

    .controller('VehicleController', ['$scope', '$modal', '$http', '$state', '$localStorage', 'CRUD', 'toaster', '$filter', '$location', '$rootScope', 'FileUploader', function ($scope, $modal, $http, $state, $localStorage, CRUD, toaster, $filter, $location, $rootScope, FileUploader) {

        var listurl = 'vehicles';
        var createurl = 'vehicles/create';
        var viewurl = 'vehicles/'; //  user id required
        var saveurl = 'vehicles';
        var updateurl = 'vehicles/update/'; // user id required
        var deleteurl = 'vehicles/delete/'; // user id required

        $scope.is_check_site = [];
        $scope.isUpdateForm = false;
        $scope.fileUploaded = true;
        $scope.rowCollection = [];
        $scope.selectedSite = '';
        $scope.sites = [];
        $scope.displayCollection = [];

///////////////////////////////////////////////  Image Upload  //////////////////////////////////////////////////
        var uploader = $scope.uploader = new FileUploader({
            url: $rootScope.API_BASE_URL + 'vehicles/uploadimage?token=' + $localStorage.token,

        });

        uploader.onAfterAddingAll = function (addedFileItems) {

            if (addedFileItems[0].file.type == "image/jpeg" || addedFileItems[0].file.type == "image/png" || addedFileItems[0].file.type == "image/gif") {
                $scope.fileUploaded = false;
                $scope.uploader.uploadAll();
            } else {
                toaster.pop('error', '', 'The file extension is not supported');
            }
        };

        uploader.onSuccessItem = function (fileItem, response, status, headers) {
            $scope.image = response.saved_image.image_path;
            $scope.vehicle_image = true;
            $scope.vehicleData.image = response.saved_image.image;
            $scope.fileUploaded = true;
        };

        uploader.onErrorItem = function (fileItem, response, status, headers) {
            console.info('onErrorItem', fileItem, response, status, headers);
        };

////////////////////////////////////  Vehicle Edit Form Data  ////////////////////////////////////////////////////////
        if ($state.is('app.vehicles_edit')) {
            if ($state.params.id != '') {
                $rootScope.loading = true;
                CRUD.findById(viewurl, $state.params.id).then(function (response) {
                    $scope.isUpdateForm = true;
                    var data = response.data.data;

                    $scope.vehicleData = {
                        name: data.name,
                        make: data.make,
                        model: data.model,
                        registration_number: data.registration_number,
                        customer_id: data.customer_id,
                        tag_id: parseInt(data.tag_id),
                        odo_meter: data.odo_meter,
                        tag_name: data.tag_name,
                        image: data.image,
                        status: data.status,
                        sars: data.sars_type,
                        code: data.code,
                        fuel_allocation: data.fuel_allocation,
                    };
                    var extn = data.image.split(".").pop();
                    if (extn == 'jpg' || extn == 'png' || extn == 'jpeg') {
                        $scope.vehicle_image = extn;
                    }
                    $rootScope.loading = false;
                }, function (error) {
                    $rootScope.loading = false;
                    //toaster.pop('error','',error.data.message);
                });
            }
            $scope.vehicleData = {};
        }


////////////////////////////////////////   Vehicles Listing  ///////////////////////////////////////////////////////
        $scope.getAllVehicles = function (site_id) {
            $rootScope.loading = true;
			$(".loading-spiner").show();
            CRUD.getAll(listurl).then(success).catch(failed);
            function success(res) {
                $scope.vehicles = res.data.data.vehicles;
                $scope.rowCollection = [];
                $scope.displayCollection = [];
                $scope.sites = res.data.data.sites;

                if ($scope.sites.length) {
                    if ($localStorage.site_id) {
                        angular.forEach($scope.sites, function (value, key) {
                            if (value.id == $localStorage.site_id) {
                                $scope.selectedSite = value.id;
                            }
                        });
                    }
                    else if (site_id) {
                        $scope.selectedSite = site_id;
                        $localStorage.site_id = site_id;
                    }
                    else {
                        $rootScope.siteData = $scope.sites[0];
                        $scope.selectedSite = $scope.sites[0].id;
                        $localStorage.site_id = $scope.sites[0].id;
                    }

                    //$scope.selectedSite = (site_id) ? site_id : $scope.sites[0].id;
                    for (var row in $scope.vehicles) {
                        if ($scope.vehicles[row].vehicleSites.indexOf($scope.selectedSite) != -1) {
                            $scope.rowCollection.push($scope.vehicles[row]);
                            $scope.displayCollection.push($scope.vehicles[row]);
                        }
                    }
                }

                for (var row in $scope.vehicles) {
                    if (!$scope.is_check_site[$scope.vehicles[row].id]) {
                        $scope.is_check_site[$scope.vehicles[row].id] = [];
                    }
                    for (var key in $scope.sites) {
                        if (!$scope.is_check_site[$scope.vehicles[row].id][$scope.sites[key].id]) {
                            $scope.is_check_site[$scope.vehicles[row].id][$scope.sites[key].id] = false;
                        }
                        $scope.is_check_site[$scope.vehicles[row].id][$scope.sites[key].id] = ($scope.vehicles[row].vehicleSites.indexOf($scope.sites[key].id) != -1);
                    }
                    //for (var key in $scope.vehicles[row].vehicleSites) {console.log($scope.vehicles[row].id+ ' ' + $scope.vehicles[row].vehicleSites[key])
                    //    $scope.is_check_site[$scope.vehicles[row].id][$scope.vehicles[row].vehicleSites[key]] = true;
                    //}
                }
                $rootScope.loading = false;
				$(".loading-spiner").hide();
            }

            function failed(error) {
                $rootScope.loading = false;
				$(".loading-spiner").hide();
                //toaster.pop('error','',error.data.message);
            }
        };

//////////////////////////////////////////  Site Change  //////////////////////////////////////////////////////////////
        $scope.changeSite = function (site_id) {
            $localStorage.site_id = site_id;
            $scope.rowCollection = [];
            $scope.displayCollection = [];
            angular.forEach($scope.sites, function (value, key) {
                if (value.id == site_id) {
                    $rootScope.siteData = value;
                }
            });
            for (var row in $scope.vehicles) {
                if ($scope.vehicles[row].vehicleSites.indexOf(site_id) != -1) {
                    $scope.rowCollection.push($scope.vehicles[row]);
                    $scope.displayCollection.push($scope.vehicles[row]);
                }
            }

            //$scope.rowCollection = $scope.selectedSite.vehicles;
            //$scope.displayCollection = $scope.selectedSite.vehicles;
        }

///////////////////////////////////////////////  Create or Edit form data /////////////////////////////////////////////
        $scope.createVehicle = function (site_id) {
            $rootScope.site_id = site_id;
            $state.go('app.vehicles_new');
        }
        $scope.create = function () {
            $rootScope.loading = true;
            $scope.vehicleData = {
                status: 'Active',
                fuel_allocation : -1
            };
            if ($state.is('app.vehicles_edit')) {
                CRUD.getWithData('vehicles/' + $state.params.id + '/edit',{site_id: $localStorage.site_id}).then(success).catch(failed);
            }
            else {
                CRUD.getWithData(createurl,{site_id: $localStorage.site_id}).then(success).catch(failed);
            }
            function success(res) {
                if (res.data.data.customers) {
                    $scope.customers = res.data.data.customers;
                }
                if (res.data.data.tags) {
                    $scope.tags = res.data.data.tags;
                }
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error','',error.data.message);
            }
        }

//////////////////////////////////////// Save Or Update Vehicle   ////////////////////////////////////////////////
        $scope.save = function (isValid) {
            if (isValid) {
                var data = $scope.vehicleData;
                var filterData = {
                    name: data.name,
                    make: data.make,
                    model: data.model,
                    registration_number: data.registration_number,
                    customer_id: data.customer_id,
                    tag_id: data.tag_id,
                    odo_meter: data.odo_meter,
                    status: data.status,
                    sars_type: data.sars,
                    fuel_allocation: data.fuel_allocation,
                    code: data.code,
                };

                if ($scope.image) {
                    filterData.image = $scope.image;
                }
                filterData.site_id = $localStorage.site_id;
                if ($state.is('app.vehicles_edit')) {
                    $rootScope.loading = true;
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Vehicle", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.vehicles');
                    }, function (res) {
                        if (res.status == 400) {
                            if (res.data.validation_errors) {
                                angular.forEach(res.data.validation_errors, function (value, key) {
                                    toaster.pop('error', '', value[0]);
                                });
                            }else{
                                toaster.pop('error', 'Vehicle', res.data.message);
                            }
                        }
                        $rootScope.loading = false;
                    });
                }
                else {
                    if ($rootScope.site_id) {
                        // filterData.site_id = $rootScope.site_id;
                        $rootScope.loading = true;
                        CRUD.store(saveurl, $.param(filterData)).then(function (response) {

                            if (response.data.status == "success") {
                                toaster.pop('success', "Vehicle", response.data.message);
                            }
                            $rootScope.loading = false;
                            $scope.vehicleData = {};
                            $state.go('app.vehicles');

                        }, function (res) {
                            if (res.status == 400) {
                                if (res.data.validation_errors) {
                                    angular.forEach(res.data.validation_errors, function (value, key) {
                                        toaster.pop('error', '', value[0]);
                                    });
                                }else{
                                    toaster.pop('error', 'Vehicle', res.data.message);
                                }
                            }
                            $rootScope.loading = false;
                        });
                    }
                }
            }
        }

///////////////////////////////////////////////  Modal Assign Site  //////////////////////////////////////////////////
        $scope.assignSite = function (ID, vehicleName, siteId) {
            var modalInstance = $modal.open({
                templateUrl: 'uploadSIGNFileModal.html',
                controller: ('sitesDetails', ['$scope', '$rootScope', '$localStorage', '$modalInstance', 'toaster', 'ID', 'vehicleName', 'siteId', sitesDetails]),
                size: 'med',
                resolve: {
                    ID: function () {
                        return ID;
                    },
                    siteId: function () {
                        return siteId;
                    },
                    vehicleName: function () {
                        return vehicleName;
                    }
                }
            });

            modalInstance.result.then(function (site_id) {
                $scope.getAllVehicles(site_id);
            });

        }
        function sitesDetails($scope, $rootScope, $localStorage, $modalInstance, toaster, ID, vehicleName, siteId) {
            $rootScope.loading = true;
            CRUD.findById('vehicles/getsites/', ID).then(function (res) {
                $scope.sitesData = res.data.data;
                for (var row in $scope.sitesData.vehicleSites) {
                    $scope.vehicle_sites[$scope.sitesData.vehicleSites[row]] = true;
                }
                $rootScope.loading = false;
            });
            $scope.vehicle_name = vehicleName;
            $scope.siteID = siteId;
            $scope.vehicle_sites = [];
            $scope.user_site = [];
            $scope.saveUserVehicles = function (siteID) {
                $rootScope.loading = true;
                $scope.user_site = [];
                //console.log($scope.userSites)
                angular.forEach($scope.vehicle_sites, function (value, key) {
                    if (value) {
                        $scope.user_site.push(key);
                    }
                })

                var filterData = {
                    vehicle_id: ID,
                    site_ids: $scope.user_site.toString(),
                };

                var token = $localStorage.token || '';
                $http({
                    method: 'POST',
                    url: $rootScope.API_BASE_URL + 'vehicles/updatesitesofvehicle?token=' + token,
                    params: filterData,
                    withCredentials: true
                }).then(function (res) {
                    $rootScope.loading = false;
                    toaster.pop('success', 'Vehicle', res.data.message);
                    $modalInstance.close(siteID);
                }, function (error) {
                    if (error.data.validation_errors) {
                        angular.forEach(error.data.validation_errors, function (value, key) {
                            toaster.pop('error', 'Vehicle', value[0]);
                        });
                    }else{
                        toaster.pop('error', 'Vehicle', error.data.message);
                    }
                    $rootScope.loading = false;
                });
            }

            $scope.closeModal = function (site_id) {
                $modalInstance.close(site_id);
            }
        }

////////////////////////////////////////  Delete Confirmation Modal  //////////////////////////////////////////////////
        $scope.deleteConfirm = function (ID, site_id) {
            var modalInstance = $modal.open({
                templateUrl: 'deleteFileModal.html',
                controller: ('remove', ['$scope', '$rootScope', '$localStorage', '$modalInstance', 'toaster', 'ID', 'site_id', remove]),
                size: 'med',
                resolve: {
                    ID: function () {
                        return ID;
                    },
                    site_id: function () {
                        return site_id;
                    },

                }
            });

            modalInstance.result.then(function (data) {
                if (data) {
                    $scope.deleteVehicle(data[0].id, data[0].site_id);
                }
            });

        }
        function remove($scope, $rootScope, $localStorage, $modalInstance, toaster, ID, site_id) {

            $scope.confirm = function () {
                var ids = [];
                ids.push({id: ID, site_id: site_id});
                $modalInstance.close(ids);
            }
            $scope.closeModal = function () {
                $modalInstance.close();
            }
        }

/////////////////////////////////////////  Delete Vehicle  ///////////////////////////////////////////////////////////
        $scope.deleteVehicle = function (id, site_id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    toaster.pop('success', "Vehicle", res.data.message);
                    $rootScope.loading = false;
                    $scope.getAllVehicles(site_id);
                }

            }, function (error) {
                toaster.pop('error', '', error.data.message);
                $rootScope.loading = false;
            });

        };

///////////////////////////////////////////////  Listing Assign Site  //////////////////////////////////////////////////
        $scope.siteAssign = function (site_id, vehicle_id) {
            $rootScope.loading = true;
            $scope.user_site = [];

            angular.forEach($scope.is_check_site[vehicle_id], function (value, key) {
                if (value) {
                    $scope.user_site.push(key);
                }
            })

            var filterData = {
                vehicle_id: vehicle_id,
                site_ids: $scope.user_site.toString(),
            };

            var token = $localStorage.token || '';
            $http({
                method: 'POST',
                url: $rootScope.API_BASE_URL + 'vehicles/updatesitesofvehicle?token=' + token,
                params: filterData,
                withCredentials: true
            }).then(function (res) {
                //console.log(res)
                toaster.pop('success', 'Vehicle', res.data.message);
                $rootScope.loading = false;
                $scope.getAllVehicles(site_id);
                //$scope.changeSite(site_id);
            }, function (error) {
                if(error.data.validation_errors) {
                    angular.forEach(error.data.validation_errors, function (value, key) {
                        toaster.pop('error', 'Vehicle', value[0]);
                    });
                }else{
                    toaster.pop('error', 'Vehicle', error.data.message);
                    $scope.is_check_site[vehicle_id][site_id] = !$scope.is_check_site[vehicle_id][site_id];
                }
                $rootScope.loading = false;
            });
        }
    }]);