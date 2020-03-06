'use strict';

/* Controllers */
// Site controller
app
    .controller('SiteController', ['$scope', '$http', '$state', '$localStorage', 'CRUD', 'toaster','$filter','$location','$modal','$rootScope', function ($scope, $http, $state, $localStorage, CRUD, toaster,$filter,$location,$modal,$rootScope) {

        var listurl = 'sites';
        var createurl = 'sites/create';
        var viewurl = 'sites/'; //  user id required
        var saveurl = 'sites';
        var updateurl = 'sites/update/'; // user id required
        var deleteurl = 'sites/delete/'; // user id required

        $scope.isUpdateForm=false;
        $scope.rowCollection = [];
        $scope.displayCollection = [];

        if($state.is('app.sites_edit')) {
            if($state.params.id != ''){
                $rootScope.loading = true;
                CRUD.findById(viewurl,$state.params.id).then(function(response){
                    $scope.isUpdateForm=true;
                    var data=response.data.data;

                    $scope.siteData = {
                        name: data.name,
                        owner_id: data.owner_id,
                    };

                },function(error){
                    $rootScope.loading = false;
                    toaster.pop('error','',error.data.message);
                });
            }
            $scope.siteData={};

        }

        $scope.getAllSites = function () {
            $rootScope.loading = true;

            CRUD.getAll(listurl).then(success).catch(failed);
            function success(res) {
                $scope.rowCollection = res.data.data;
                $scope.displayCollection=res.data.data;
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error','','You are Logged out.');
            }
        };

        $scope.createSite = function(){
            $rootScope.loading = true;
            if($state.is('app.sites_edit')) {
                CRUD.getWithData('sites/'+$state.params.id+'/edit').then(success).catch(failed);
            }
            else{
                CRUD.getWithData(createurl).then(success).catch(failed);
            }
            function success(res) {
                $scope.users = res.data;
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //vm.errorMessage = error.message;
            }

        }

        $scope.save = function (isValid) {

            if(isValid)
            {
                var data=$scope.siteData;
                var filterData = {
                     name: data.name,
                     owner_id: data.owner_id,
                 };
                $rootScope.loading = true;
                if($state.is('app.sites_edit'))
                {
                    CRUD.update(updateurl, $state.params.id,  $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Site", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.sites');
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
                else{
                    $rootScope.loading = true;
                    CRUD.store(saveurl, $.param(filterData)).then(function (response) {
                        if(response.data.status == "success")
                        {
                            toaster.pop('success', "Site", response.data.message);
                        }
                        $rootScope.loading = false;
                        $scope.siteData={};
                        $state.go('app.sites');

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
                    $scope.deleteSite(data);
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

        $scope.deleteSite = function (id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if(res.data.status == "success")
                {
                    toaster.pop('success', "Site", res.data.message);
                    $rootScope.loading = false;
                    $scope.getAllSites();
                }

            }, function (error) {
                toaster.pop('error','',error.data.message);
                $rootScope.loading = false;
            });
        };

    }]);