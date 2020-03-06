'use strict';

/* Controllers */
// signin controller
app
    .controller('JobController', ['$scope', '$http', '$state', 'CRUD', 'toaster', '$rootScope', '$modal', '$localStorage', function ($scope, $http, $state, CRUD, toaster, $rootScope, $modal, $localStorage) {

        var listurl = 'jobs';
        var createurl = 'jobs/create/';
        var viewurl = 'jobs/'; //  user id required
        var saveurl = 'jobs';
        var updateurl = 'jobs/update/'; // user id required
        var deleteurl = 'jobs/delete/'; // user id required

        $scope.isUpdateForm = false;
        $scope.rowCollection = [];
        $scope.selectedSite = '';
        $scope.sites = [];
        $scope.displayCollection = [];
        $scope.is_check_site = [];

        if ($state.is('app.jobs_edit')) {

            if ($state.params.id != '') {
                $rootScope.loading = true;
                CRUD.findById(viewurl, $state.params.id).then(function (response) {
                    $scope.isUpdateForm = true;
                    var data = response.data.data;
                    $scope.jobData = {
                        name: data.name,
                        description: data.description,
                        site_id: data.site_id,
                        tag_id: parseInt(data.tag_id),
                        tag_name: data.tag_name,
                        code: data.code,
                        fuel_allocation: data.fuel_allocation,
                    };
                    $rootScope.loading = false;
                }, function (error) {
                    $rootScope.loading = false;
                    //toaster.pop('error','',error.data.message);
                });
            }
            $scope.jobData = {};

        }

        $scope.getAllJobs = function (site_id) {
            $rootScope.loading = true;
            CRUD.getAll(listurl).then(success).catch(failed);
            function success(res) {
                $scope.jobs = res.data.data.jobs;
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
                    for (var row in $scope.jobs) {
                        if ($scope.jobs[row].jobSites.indexOf($scope.selectedSite) != -1) {
                            $scope.rowCollection.push($scope.jobs[row]);
                            $scope.displayCollection.push($scope.jobs[row]);
                        }
                    }
                }

                for (var row in $scope.jobs) {
                    if (!$scope.is_check_site[$scope.jobs[row].id]) {
                        $scope.is_check_site[$scope.jobs[row].id] = [];
                    }
                    for (var key in $scope.sites) {
                        if (!$scope.is_check_site[$scope.jobs[row].id][$scope.sites[key].id]) {
                            $scope.is_check_site[$scope.jobs[row].id][$scope.sites[key].id] = false;
                        }
                        $scope.is_check_site[$scope.jobs[row].id][$scope.sites[key].id] = ($scope.jobs[row].jobSites.indexOf($scope.sites[key].id) != -1);
                    }
                    //for (var key in $scope.jobs[row].jobSites) {
                    //    $scope.is_check_site[$scope.jobs[row].id][$scope.jobs[row].jobSites[key]] = true;
                    //}
                }
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error','',error.data.message);
                //vm.errorMessage = error.message;
            }

        };

        $scope.changeSite = function (site_id) {
            $localStorage.site_id = site_id;
            angular.forEach($scope.sites, function (value, key) {
                if (value.id == site_id) {
                    $rootScope.siteData = value;
                }
            });
            $scope.rowCollection = [];
            $scope.displayCollection = [];
            for (var row in $scope.jobs) {
                if ($scope.jobs[row].jobSites.indexOf(site_id) != -1) {
                    $scope.rowCollection.push($scope.jobs[row]);
                    $scope.displayCollection.push($scope.jobs[row]);
                }
            }

            //$scope.rowCollection = $scope.selectedSite.jobs;
            //$scope.displayCollection = $scope.selectedSite.jobs;
        }

        $scope.createJob = function (site_id) {
            $rootScope.site_id = site_id;
            $state.go('app.jobs_new');
        }

        $scope.create = function () {
            $rootScope.loading = true;
            $scope.jobData = {
                fuel_allocation: -1
            };
            if ($state.is('app.jobs_edit')) {
                CRUD.getWithData('jobs/' + $state.params.id + '/edit').then(success).catch(failed);
            }
            else {
                CRUD.getWithData(createurl+ $rootScope.site_id).then(success).catch(failed);
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

        $scope.save = function (isValid) {

            if (isValid) {
                var data = $scope.jobData;
                var filterData = {
                    name: data.name,
                    description: data.description,
                    tag_id: data.tag_id,
                    fuel_allocation: data.fuel_allocation,
                    code: data.code,
                };
                filterData.site_id = $localStorage.site_id;
                if ($state.is('app.jobs_edit')) {
                    //filterData.site_id = data.site_id;
                    $rootScope.loading = true;
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Jobs", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.jobs');
                    }, function (res) {
                        if (res.status == 400) {
                            if (res.data.validation_errors) {
                                angular.forEach(res.data.validation_errors, function (value, key) {
                                    toaster.pop('error', '', value[0]);
                                });
                            }else{
                                toaster.pop('error', 'Jobs', res.data.message);
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
                                toaster.pop('success', "Jobs", response.data.message);
                            }
                            $scope.jobData = {};
                            $rootScope.loading = false;
                            $state.go('app.jobs');

                        }, function (res) {
                            if (res.status == 400) {
                                if (res.data.validation_errors) {
                                    angular.forEach(res.data.validation_errors, function (value, key) {
                                        toaster.pop('error', '', value[0]);
                                    });
                                }else{
                                    toaster.pop('error', 'Jobs', res.data.message);
                                }
                            }
                            $rootScope.loading = false;
                        });
                    }
                }
            }

        }

        $scope.assignSite = function (ID, jobName, siteId) {
            var modalInstance = $modal.open({
                templateUrl: 'uploadSIGNFileModal.html',
                controller: ('sitesDetails', ['$scope', '$rootScope', '$localStorage', '$modalInstance', 'toaster', 'ID', 'jobName', 'siteId', sitesDetails]),
                size: 'med',
                resolve: {
                    ID: function () {
                        return ID;
                    },
                    siteId: function () {
                        return siteId;
                    },
                    jobName: function () {
                        return jobName;
                    }
                }
            });

            modalInstance.result.then(function (siteId) {
                $scope.getAllJobs(siteId);
            });

        }
        function sitesDetails($scope, $rootScope, $localStorage, $modalInstance, toaster, ID, jobName, siteId) {
            $rootScope.loading = true;
            CRUD.findById('jobs/getsites/', ID).then(function (res) {
                $scope.sitesData = res.data.data;
                for (var row in $scope.sitesData.jobSites) {
                    $scope.job_sites[$scope.sitesData.jobSites[row]] = true;
                }
                $rootScope.loading = false;
            });
            $scope.job_name = jobName;
            $scope.siteID = siteId;
            $scope.job_sites = [];
            $scope.user_site = [];

            $scope.saveUserJob = function (siteID) {
                //console.log($scope.userSites)
                $rootScope.loading = true;
                $scope.user_site = [];
                angular.forEach($scope.job_sites, function (value, key) {
                    if (value) {
                        $scope.user_site.push(key);
                    }
                })

                var filterData = {
                    job_id: ID,
                    site_ids: $scope.user_site.toString(),
                };

                var token = $localStorage.token || '';
                $http({
                    method: 'POST',
                    url: $rootScope.API_BASE_URL + 'jobs/updatesitesofjob?token=' + token,
                    params: filterData,
                    withCredentials: true
                }).then(function (res) {
                    //console.log(res)
                    toaster.pop('success', 'Jobs', res.data.message);
                    $rootScope.loading = false;
                    $modalInstance.close(siteID);
                }, function (error) {
                    if (error.data.validation_errors) {
                        angular.forEach(error.data.validation_errors, function (value, key) {
                            toaster.pop('error', 'Jobs', value[0]);
                        });
                    }else{
                        toaster.pop('error', 'Jobs', error.data.message);
                    }
                    $rootScope.loading = false;
                });

            }

            $scope.closeModal = function (siteID) {
                $modalInstance.close(siteID);
            }
        }

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
                    $scope.deleteJob(data[0].id, data[0].site_id);
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

        $scope.deleteJob = function (id, site_id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    toaster.pop('success', "Jobs", res.data.message);
                    $rootScope.loading = false;
                    $scope.getAllJobs(site_id);
                }

            }, function (error) {
                $rootScope.loading = false;
                toaster.pop('error', '', error.data.message);
            });

        };

        $scope.siteAssign = function (site_id, job_id) {
            $rootScope.loading = true;
            $scope.user_site = [];

            angular.forEach($scope.is_check_site[job_id], function (value, key) {
                if (value) {
                    $scope.user_site.push(key);
                }
            })

            var filterData = {
                job_id: job_id,
                site_ids: $scope.user_site.toString(),
            };

            var token = $localStorage.token || '';
            $http({
                method: 'POST',
                url: $rootScope.API_BASE_URL + 'jobs/updatesitesofjob?token=' + token,
                params: filterData,
                withCredentials: true
            }).then(function (res) {
                //console.log(res)
                $rootScope.loading = false;
                toaster.pop('success', 'Jobs', res.data.message);
                $scope.getAllJobs(site_id);
            }, function (error) {
                if (error.data.validation_errors) {
                    angular.forEach(error.data.validation_errors, function (value, key) {
                        toaster.pop('error', 'Jobs', value[0]);
                    });
                }else{
                    toaster.pop('error', 'Jobs', error.data.message);
                    $scope.is_check_site[job_id][site_id] = !$scope.is_check_site[job_id][site_id];
                }
                $rootScope.loading = false;
            });
        }
    }]);