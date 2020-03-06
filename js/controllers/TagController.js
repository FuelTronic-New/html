'use strict';

/* Controllers */
// Tag controller
app.controller('TagController', ['$scope', '$http', '$state', '$localStorage', 'CRUD', 'toaster', '$filter', '$location', '$rootScope', '$modal', function ($scope, $http, $state, $localStorage, CRUD, toaster, $filter, $location, $rootScope, $modal) {

        var listurl = 'tags';
        var viewurl = 'tags/'; //  user id required
        var createurl = 'tags/create';
        var saveurl = 'tags';
        var updateurl = 'tags/update/'; // user id required
        var deleteurl = 'tags/delete/'; // user id required

        $scope.isUpdateForm = false;
        $scope.rowCollection = [];
        $scope.selectedSite = '';
        $scope.sites = [];
        $scope.displayCollection = [];
        $scope.is_check_site = [];

/////////////////////////////////////////  Tag Edit Form Data  ////////////////////////////////////////////////////////
        if ($state.is('app.tags_edit')) {
            if ($state.params.id != '') {
                $rootScope.loading = true;
                CRUD.findById(viewurl, $state.params.id).then(function (response) {
                    $scope.isUpdateForm = true;
                    var data = response.data.data;

                    $scope.tagData = {
                        type: data.type,
                        usage: data.usage,
                        name: data.name,
                    };
                    $rootScope.loading = false;
                }, function (error) {
                    $rootScope.loading = false;
                    //toaster.pop('error','',error.data.message);
                });
            }
            $scope.tagData = {};
        }

//////////////////////////////////////////////   Tags Listing  ///////////////////////////////////////////////////////
        $scope.getAllTags = function (site_id) {
            $rootScope.loading = true;
			$(".loading-spiner").show();
            CRUD.getAll(listurl).then(success).catch(failed);
            function success(res) {
                $scope.tags = res.data.data.tags;
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
                    for (var row in $scope.tags) {
                        if ($scope.tags[row].tagSites.indexOf($scope.selectedSite) != -1) {
                            $scope.rowCollection.push($scope.tags[row]);
                            $scope.displayCollection.push($scope.tags[row]);
                        }
                    }
                }

                for (var row in $scope.tags) {
                    if (!$scope.is_check_site[$scope.tags[row].id]) {
                        $scope.is_check_site[$scope.tags[row].id] = [];
                    }
                    for (var key in $scope.sites) {
                        if (!$scope.is_check_site[$scope.tags[row].id][$scope.sites[key].id]) {
                            $scope.is_check_site[$scope.tags[row].id][$scope.sites[key].id] = false;
                        }
                        $scope.is_check_site[$scope.tags[row].id][$scope.sites[key].id] = ($scope.tags[row].tagSites.indexOf($scope.sites[key].id) != -1);
                    }
                    //for (var key in $scope.tags[row].tagSites) {
                    //    console.log($scope.tags[row].tagSites)
                    //    $scope.is_check_site[$scope.tags[row].id][$scope.tags[row].tagSites[key]] = true;
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
            for (var row in $scope.tags) {
                if ($scope.tags[row].tagSites.indexOf(site_id) != -1) {
                    $scope.rowCollection.push($scope.tags[row]);
                    $scope.displayCollection.push($scope.tags[row]);
                }
            }

            //$scope.rowCollection = $scope.selectedSite.vehicles;
            //$scope.displayCollection = $scope.selectedSite.vehicles;
        }

///////////////////////////////////////////////  Create or Edit form data //////////////////////////////////////////////
        $scope.createTag = function (site_id) {
            $rootScope.site_id = site_id;
            $state.go('app.tags_new');
        }
        $scope.create = function () {
            $rootScope.loading = true;
			$(".loading-spiner").show();
            if ($state.is('app.tags_edit')) {
                CRUD.getWithData('tags/' + $state.params.id + '/edit').then(success).catch(failed);
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


/////////////////////////////////////////// Save Or Update Tag   /////////////////////////////////////////////////////
        $scope.save = function (isValid) {
            if (isValid) {
                var data = $scope.tagData;
                var filterData = {
                    type: data.type,
                    name: data.name,
                    usage: data.usage,
                };
                if ($state.is('app.tags_edit')) {
                    $rootScope.loading = true;
					$(".loading-spiner").show();
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Tag", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.tags');
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
						$(".loading-spiner").show();
                        CRUD.store(saveurl, $.param(filterData)).then(function (response) {

                            if (response.data.status == "success") {
                                toaster.pop('success', "Tag", response.data.message);
                            }
                            $rootScope.loading = false;
                            $scope.tagData = {};
                            $state.go('app.tags');

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
        }

///////////////////////////////////////////////  Modal Assign Site  //////////////////////////////////////////////////

        $scope.assignSite = function (ID, tagName, siteId) {
            var modalInstance = $modal.open({
                templateUrl: 'uploadSIGNFileModal.html',
                controller: ('sitesDetails', ['$scope', '$rootScope', '$localStorage', '$modalInstance', 'toaster', 'ID', 'tagName', 'siteId', sitesDetails]),
                size: 'med',
                resolve: {
                    ID: function () {
                        return ID;
                    },
                    siteId: function () {
                        return siteId;
                    },
                    tagName: function () {
                        return tagName;
                    }
                }
            });

            modalInstance.result.then(function (siteId) {
                $scope.getAllTags(siteId);
            });

        }
        function sitesDetails($scope, $rootScope, $localStorage, $modalInstance, toaster, ID, tagName, siteId) {
            $rootScope.loading = true;
			$(".loading-spiner").show();
            CRUD.findById('tags/getsites/', ID).then(function (res) {
                $scope.sitesData = res.data.data;
                for (var row in $scope.sitesData.tagSites) {
                    $scope.tag_sites[$scope.sitesData.tagSites[row]] = true;
                }
                $rootScope.loading = false;
            });
            $scope.tag_name = tagName;
            $scope.siteID = siteId;
            $scope.tag_sites = [];
            $scope.user_site = [];

            $scope.saveUserTags = function (siteId) {
                //console.log($scope.userSites)
                $rootScope.loading = true;
				$(".loading-spiner").show();
                angular.forEach($scope.tag_sites, function (value, key) {
                    if (value) {
                        $scope.user_site.push(key);
                    }
                })

                var filterData = {
                    tag_id: ID,
                    site_ids: $scope.user_site.toString(),
                };

                var token = $localStorage.token || '';
                $http({
                    method: 'POST',
                    url: $rootScope.API_BASE_URL + 'tags/updatesitesoftag?token=' + token,
                    params: filterData,
                    withCredentials: true
                }).then(function (res) {
                    //console.log(res)
                    toaster.pop('success', 'Tag', res.data.message);
                    $rootScope.loading = false;
                    $modalInstance.close(siteId);
                }, function (error) {
                    angular.forEach(error.data.validation_errors, function (value, key) {
                        toaster.pop('error', 'Tag', value[0]);
                    });
                    $rootScope.loading = false;
                });
            }

            $scope.closeModal = function (siteId) {
                $modalInstance.close(siteId);
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
                    $scope.deleteTag(data[0].id, data[0].site_id);
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

/////////////////////////////////////////////  Delete Tag  ///////////////////////////////////////////////////////////

        $scope.deleteTag = function (id, site_id) {
            $rootScope.loading = true;
			$(".loading-spiner").show();
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    toaster.pop('success', "Tag", res.data.message);
                    $rootScope.loading = false;
                    $scope.getAllTags(site_id);
                }

            }, function (error) {
                toaster.pop('error', '', error.data.message);
                $rootScope.loading = false;
            });

        };

///////////////////////////////////////////////  Listing Assign Site  //////////////////////////////////////////////////

        $scope.siteAssign = function (site_id, tag_id) {
            $rootScope.loading = true;
			$(".loading-spiner").show();
            $scope.user_site = [];

            angular.forEach($scope.is_check_site[tag_id], function (value, key) {
                if (value) {
                    $scope.user_site.push(key);
                }
            })

            var filterData = {
                tag_id: tag_id,
                site_ids: $scope.user_site.toString(),
            };

            var token = $localStorage.token || '';
            $http({
                method: 'POST',
                url: $rootScope.API_BASE_URL + 'tags/updatesitesoftag?token=' + token,
                params: filterData,
                withCredentials: true
            }).then(function (res) {
                //console.log(res)
                toaster.pop('success', 'Tag', res.data.message);
                $rootScope.loading = false;
                $scope.getAllTags(site_id);
            }, function (error) {
                angular.forEach(error.data.validation_errors, function (value, key) {
                    toaster.pop('error', 'Tag', value[0]);
                });
                $rootScope.loading = false;
            });

        }
    }]);