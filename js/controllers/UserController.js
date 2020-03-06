'use strict';

/* Controllers */
// signin controller
app
    .controller('UserController', ['$scope', '$http', '$state', '$localStorage', 'CRUD', 'toaster', '$filter', '$location','$modal','$rootScope', function ($scope, $http, $state, $localStorage, CRUD, toaster, $filter, $location,$modal,$rootScope) {

        var listurl = 'siteusers';
        var viewurl = 'siteusers/'; //  user id required
        var saveurl = 'siteusers';
        var updateurl = 'siteusers/update/'; // user id required
        var deleteurl = 'siteusers/delete/'; // user id required

        $scope.isUpdateForm = false;
        $scope.rowCollection = [];
        $scope.displayCollection = [];

        if ($state.is('app.users_edit')) {

            if ($state.params.id != '') {
                $rootScope.loading = true;
                CRUD.findById(viewurl, $state.params.id).then(function (response) {
                    $scope.isUpdateForm = true;
                    var data = response.data.data;

                    $scope.userDetails = {
                        name: data.name,
                        email: data.email,
                        role: data.role.toString(),
                    };
                    $rootScope.loading = false;
                }, function (error) {
                    $rootScope.loading = false;
                    //toaster.pop(error.data.message);
                });
            }
            $scope.userData = {};

        }

        $scope.getAllUsers = function () {
            $rootScope.loading = true;
            CRUD.getAll(listurl).then(success).catch(failed);
            function success(res) {

                $scope.rowCollection = res.data.data;
                $scope.displayCollection=res.data.data;
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error', "User", error.data.message);
            }

        };

        $scope.save = function (isValid) {

            if (isValid) {
                var data = $scope.userDetails;
                var filterData = {
                    name: data.name,
                    email: data.email,
                    password: data.password,
                    role: data.role,
                };

                if ($state.is('app.users_edit')) {
                    $rootScope.loading = true;
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "User", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.users');
                    }, function (res) {
                        if (res.status == 400) {
                            angular.forEach(res.data.validation_errors, function (value, key) {
                                toaster.pop('error', '', value[0]);
                            });
                        }
                        $rootScope.loading = false;
                        toaster.pop('error', "User", "Whoops Something Wrong");
                    });
                }
                else {
                    $rootScope.loading = true;
                    CRUD.store(saveurl, $.param(filterData)).then(function (response) {

                        if (response.data.status == "success") {
                            toaster.pop('success', "User", response.data.message);
                            $rootScope.loading = false;
                            $state.go('app.users');
                        }

                        $scope.userData = {};

                    }, function (res) {
                        if (res.status == 400) {
                            angular.forEach(res.data.validation_errors, function (value, key) {
                                toaster.pop('error', '', value[0]);
                                $rootScope.loading = false;
                            });
                        }

                    });
                }
            }
        }

        $scope.assignSite = function (ID,userName) {
                var modalInstance = $modal.open({
                    templateUrl: 'uploadSIGNFileModal.html',
                    controller: ('sitesDetails', ['$scope', '$rootScope', '$localStorage', '$modalInstance', 'toaster','ID','userName', sitesDetails]),
                    size: 'med',
                    resolve: {
                        ID: function () {
                            return ID;
                        },
                        userName: function () {
                            return userName;
                        }
                    }
                });

                modalInstance.result.then(function (imgSource) {

                });

            }
        function sitesDetails($scope, $rootScope, $localStorage, $modalInstance, toaster,ID,userName) {
            $rootScope.loading = true;
            CRUD.findById('siteusers/getsites/',ID).then(function(res){
                $scope.sitesData = res.data.data;
                for(var row in $scope.sitesData.userSites) {
                    $scope.userSites[$scope.sitesData.userSites[row]] = true;
                }
            });
            $scope.user_name = userName;
            $scope.userSites=[];
            $scope.user_site =[];
            $rootScope.loading = false;
            $scope.saveUserSite = function () {
                $rootScope.loading = true;
                //console.log($scope.userSites)
                var count = 0;
                angular.forEach($scope.userSites,function(value,key){
                    if(value){
                        $scope.user_site.push(key) ;
                        count ++;
                    }
                })
                if(count == 0){
                    toaster.pop('error','User','Please select site. ');
                    return false;
                }
                //console.log($scope.user_site);

                var filterData = {
                    user_id: ID,
                    site_ids: $scope.user_site.toString(),
                };

                var token = $localStorage.token || '';
                $http({
                    method: 'POST',
                    url: $rootScope.API_BASE_URL + 'siteusers/updatesitesofuser?token=' + token,
                    params: filterData,
                    withCredentials: true
                }).then(function (res) {
                        //console.log(res)
                    toaster.pop('success','User',res.data.message);
                    $rootScope.loading = false;
                    $modalInstance.close();
                },function(error){
                    angular.forEach(error.data.validation_errors,function(value,key){
                        toaster.pop('error','User',value[0]);
                    });
                    $rootScope.loading = false;
                });

            }

            $scope.closeModal = function(){
                $modalInstance.close();
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
                    $scope.deleteUser(data);
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

        $scope.deleteUser = function (id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    toaster.pop('success', "User", res.data.message);
                    $rootScope.loading = false;
                    $scope.getAllUsers();
                }

            }, function (error) {
                toaster.pop('error', "User", error.data.message);
                $rootScope.loading = false;
            });

        };

    }]);