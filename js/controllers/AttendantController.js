'use strict';

/* Controllers */
// signin controller
app
    .controller('AttendantController', ['$scope', '$http', '$state', 'CRUD', 'toaster', '$rootScope', 'FileUploader', '$localStorage', '$modal', function ($scope, $http, $state, CRUD, toaster, $rootScope, FileUploader, $localStorage, $modal) {

        var listurl = 'attendants';
        var createurl = 'attendants/create';
        var viewurl = 'attendants/'; //  user id required
        var saveurl = 'attendants';
        var updateurl = 'attendants/update/'; // user id required
        var deleteurl = 'attendants/delete/'; // user id required

        $scope.isUpdateForm = false;
        $scope.fileUploaded = true;
        $scope.rowCollection = [];
        $scope.selectedSite = '';
        $scope.sites = [];
        $scope.displayCollection = [];
        $scope.is_check_site = [];

///////////////////////////////////////////////  Image Upload  //////////////////////////////////////////////////
        var uploader = $scope.uploader = new FileUploader({
            url: $rootScope.API_BASE_URL + 'attendants/uploadimage?token=' + $localStorage.token,
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
            $scope.attendant_image = true;
            $scope.attendantsData.image = response.saved_image.image;
            $scope.fileUploaded = true;
        };

        uploader.onErrorItem = function (fileItem, response, status, headers) {
            console.info('onErrorItem', fileItem, response, status, headers);
        };

////////////////////////////////////  Attendant Edit Form Data  ////////////////////////////////////////////////////////
        if ($state.is('app.attendants_edit')) {

            if ($state.params.id != '') {
                $rootScope.loading = true;


                CRUD.findById(viewurl, $state.params.id).then(function (response) {
                    $scope.isUpdateForm = true;
                    var data = response.data.data;
                    $rootScope.loading = false;

                    $scope.attendantsData = {
                        name: data.name,
                        surname: data.surname,
                        cell: data.cell,
                        said: data.said,
                        tag_id: parseInt(data.tag_id),
                        tag_name: data.tag_name,
                        code: data.code,
                        image: data.image,
                        pin: data.pin,
                        fuel_allocation: data.fuel_allocation,
                    };
                    var extn = data.image.split(".").pop();
                    if (extn == 'jpg' || extn == 'png' || extn == 'jpeg') {
                        $scope.attendant_image = extn;
                    }

                }, function (error) {
                    $rootScope.loading = false;
                    //toaster.pop('error','',error.data.message);
                });

            }
            $scope.attendantsData = {};
        }

////////////////////////////////////////   Attendants Listing  ///////////////////////////////////////////////////////
        $scope.getAllAttendants = function (site_id) {
            $rootScope.loading = true;
			$(".loading-spiner").show();
            CRUD.getAll(listurl).then(success).catch(failed);
            function success(res) {
                $scope.attendants = res.data.data.attendants;
                $scope.rowCollection = [];
                $scope.displayCollection = [];
                $scope.sites = res.data.data.sites;
                $rootScope.loading = false;
			$(".loading-spiner").hide();
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
                    for (var row in $scope.attendants) {
                        if ($scope.attendants[row].attendantSites.indexOf($scope.selectedSite) != -1) {
                            $scope.rowCollection.push($scope.attendants[row]);
                            $scope.displayCollection.push($scope.attendants[row]);
                        }
                    }
                }

                for (var row in $scope.attendants) {
                    if (!$scope.is_check_site[$scope.attendants[row].id]) {
                        $scope.is_check_site[$scope.attendants[row].id] = [];
                    }
                    for (var key in $scope.sites) {
                        if (!$scope.is_check_site[$scope.attendants[row].id][$scope.sites[key].id]) {
                            $scope.is_check_site[$scope.attendants[row].id][$scope.sites[key].id] = false;
                        }
                        $scope.is_check_site[$scope.attendants[row].id][$scope.sites[key].id] = ($scope.attendants[row].attendantSites.indexOf($scope.sites[key].id) != -1);
                    }
                }
            }

            function failed(error) {
                $rootScope.loading = false;
				$(".loading-spiner").hide();
                //toaster.pop('error','',error.data.message);
                //vm.errorMessage = error.message;
            }
        };

///////////////////////////////////////////////  Create or Edit form data  /////////////////////////////////////////////
        $scope.createAttendant = function (site_id) {
            $rootScope.site_id = site_id;
            $state.go('app.attendants_new');
        }
        $scope.create = function () {
            $rootScope.loading = true;
            $scope.attendantsData = {
                fuel_allocation: -1
            };
            if ($state.is('app.attendants_edit')) {
                CRUD.getWithData('attendants/' + $state.params.id + '/edit').then(success).catch(failed);
            }
            else {
                CRUD.getWithData(createurl).then(success).catch(failed);
            }
            function success(res) {
                $scope.tags = res.data;
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error', '', error.data.message);
            }
        }

//////////////////////////////////////// Save Or Update Attendant   ////////////////////////////////////////////////
        $scope.save = function (isValid) {

            if (isValid) {
                var data = $scope.attendantsData;
                var filterData = {
                    name: data.name,
                    surname: data.surname,
                    cell: data.cell,
                    said: data.said,
                    tag_id: data.tag_id,
                    pin: data.pin,
                    code: data.code,
                    fuel_allocation: data.fuel_allocation,
                };
                if ($scope.image) {
                    filterData.image = $scope.image;
                }
                if ($state.is('app.attendants_edit')) {
                    $rootScope.loading = true;
                    filterData.site_id = $localStorage.site_id;
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Attendants", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.attendants');
                    }, function (res) {
                        if (res.status == 400) {
                            if (res.data.validation_errors) {
                                angular.forEach(res.data.validation_errors, function (value, key) {
                                    toaster.pop('error', '', value[0]);
                                });
                            }else{
                                toaster.pop('error', '', res.data.message);
                            }
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
                            $scope.attendantsData = {};
                            $rootScope.loading = false;
                            $state.go('app.attendants');

                        }, function (res) {
                            if (res.status == 400) {
                                if (res.data.validation_errors) {
                                    angular.forEach(res.data.validation_errors, function (value, key) {
                                        toaster.pop('error', '', value[0]);
                                    });
                                }else{
                                    toaster.pop('error', '', res.data.message);
                                }
                            }
                            $rootScope.loading = false;
                        });
                    }
                }
            }
        }

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
            for (var row in $scope.attendants) {
                if ($scope.attendants[row].attendantSites.indexOf(site_id) != -1) {
                    $scope.rowCollection.push($scope.attendants[row]);
                    $scope.displayCollection.push($scope.attendants[row]);
                }
            }

            //$scope.rowCollection = $scope.selectedSite.vehicles;
            //$scope.displayCollection = $scope.selectedSite.vehicles;
        }

///////////////////////////////////////////////  Modal Assign Site  //////////////////////////////////////////////////
        $scope.assignSite = function (ID, attendantName, siteId) {
            var modalInstance = $modal.open({
                templateUrl: 'uploadSIGNFileModal.html',
                controller: ('sitesDetails', ['$scope', '$rootScope', '$localStorage', '$modalInstance', 'toaster', 'ID', 'attendantName', 'siteId', sitesDetails]),
                size: 'med',
                resolve: {
                    ID: function () {
                        return ID;
                    },
                    siteId: function () {
                        return siteId;
                    },
                    attendantName: function () {
                        return attendantName;
                    }
                }
            });

            modalInstance.result.then(function (siteID) {
                $scope.getAllAttendants(siteID);
            });

        }
        function sitesDetails($scope, $rootScope, $localStorage, $modalInstance, toaster, ID, attendantName, siteId) {
            $rootScope.loading = true;
            CRUD.findById('attendants/getsites/', ID).then(function (res) {
                $scope.sitesData = res.data.data;
                $rootScope.loading = false;
                for (var row in $scope.sitesData.attendantSites) {
                    $scope.attendant_sites[$scope.sitesData.attendantSites[row]] = true;
                }
            });
            $scope.attendant_name = attendantName;
            $scope.siteID = siteId;
            $scope.attendant_sites = [];
            $scope.user_site = [];
            $scope.saveUserAttendant = function (siteID) {
                //console.log($scope.userSites)
                $rootScope.loading = true;
                var token = $localStorage.token || '';
                $scope.user_site = [];

                var filterData = {
                    attendant_id: ID,
                };
                angular.forEach($scope.attendant_sites, function (value, key) {
                    if (value) {
                        $scope.user_site.push(key);
                        if (key == siteID){
                            filterData.new_site_id= siteID;
                        }
                    }
                })

                filterData.site_ids= $scope.user_site.toString();
                $http({
                    method: 'POST',
                    url: $rootScope.API_BASE_URL + 'attendants/updatesitesofattedant?token=' + token,
                    params: filterData,
                    withCredentials: true
                }).then(function (res) {
                    //console.log(res)
                    toaster.pop('success', 'Attendants', res.data.message);
                    $rootScope.loading = false;
                    $modalInstance.close(siteID);

                }, function (error) {
                    if (error.data.validation_errors) {
                        angular.forEach(error.data.validation_errors, function (value, key) {
                            toaster.pop('error', 'Attendants', value[0]);
                        });
                    }else{
                        toaster.pop('error', 'Attendants', error.data.message);
                    }
                    $rootScope.loading = false;
                });
            }

            $scope.closeModal = function (siteID) {
                $modalInstance.close(siteID);
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
                    $scope.deleteAttendant(data[0].id, data[0].site_id);
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

/////////////////////////////////////////  Delete Attendant  ///////////////////////////////////////////////////////////
        $scope.deleteAttendant = function (id, site_id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    $rootScope.loading = false;
                    toaster.pop('success', "Attendants", res.data.message);
                    $scope.getAllAttendants(site_id);
                }

            }, function (error) {
                $rootScope.loading = false;
                toaster.pop('error', '', error.data.message);
            });
        };

///////////////////////////////////////////////  Listing Assign Site  //////////////////////////////////////////////////
        $scope.siteAssign = function (site_id, attendant_id) {

            $scope.user_site = [];
            $rootScope.loading = true;
            angular.forEach($scope.is_check_site[attendant_id], function (value, key) {
                if (value) {
                    $scope.user_site.push(key);
                }
            })
            var filterData = {
                attendant_id: attendant_id,
                site_ids: $scope.user_site.toString(),
            };
            if($scope.is_check_site[attendant_id][site_id]){
                filterData.new_site_id= site_id;
            }

            var token = $localStorage.token || '';
            $http({
                method: 'POST',
                url: $rootScope.API_BASE_URL + 'attendants/updatesitesofattedant?token=' + token,
                params: filterData,
                withCredentials: true
            }).then(function (res) {
                //console.log(res)
                $rootScope.loading = false;
                toaster.pop('success', 'Attendants', res.data.message);
                $scope.getAllAttendants(site_id);
            }, function (error) {
                if (error.data.validation_errors) {
                    angular.forEach(error.data.validation_errors, function (value, key) {
                        toaster.pop('error', 'Attendants', value[0]);
                    });
                }else{
                    toaster.pop('error', 'Attendants', error.data.message);
                    $scope.is_check_site[attendant_id][site_id] = !$scope.is_check_site[attendant_id][site_id];
                }
                $rootScope.loading = false;
            });
        }
    }]);