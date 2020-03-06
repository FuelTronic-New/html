'use strict';

/* Controllers */

app.controller('XeroController', ['$scope', '$http', '$state', '$localStorage', 'CRUD', '$location','toaster','$modal', function ($scope, $http, $state, $localStorage, CRUD, $location,toaster,$modal) {

    var url = 'xero';
    $scope.xero = [];
    $scope.xeroAccount = '';

    $scope.getBasic = function () {
        if ($location.search().oauth_token != '' && $location.search().oauth_token != undefined) {
            var data = {
                oauth_token: $location.search().oauth_token,
                oauth_verifier: $location.search().oauth_verifier,
                org: $location.search().org,
            };
            CRUD.getWithData('xero/callback', data).then(function (response) {
                if (response.data == 'success') {
                    $state.go('app.xero_api', {}, {reload: true});
                }
            }).catch(function (response) {
                console.log(response);
            });
        } else {
            CRUD.getAll(url).then(function (res) {
                    $scope.xero = res.data;
                if(res.data.code == 400){
                    toaster.pop('error','',$scope.xero.response);
                    console.log($scope.xero.response)
                }
                }, function (error) {
                    //delete $localStorage.token;
                    //$state.go('access.signin');
                console.log('reload start');
                $location.reload();

                })
                .catch(function (error) {
                    console.log('reload start2');
                    $location.reload();
                    //delete $localStorage.token;
                    //$state.go('access.signin');

                });
        }

    }
    $scope.wipeXero = function () {
        CRUD.getAll('xero/wipe').then(function (response) {
            if (response.data == 'success') {
                $state.go('app.xero_api', {}, {reload: true});
            }
        }).catch(function (response) {
            console.log('reload start4');
            $location.reload();
        });
    }
    $scope.refreshToken = function () {
        CRUD.getAll('xero/refreshtoken').then(function (response) {
            if (response.data == 'success') {
                $state.go('app.xero_api', {}, {reload: true});
            }
        }).catch(function (response) {
            console.log('reload start5');
            $location.reload();
        });

    }

    $scope.addNew = function () {
        var $modalInstance = $modal.open({
            templateUrl: 'addnewaccount.html',
            size: 'med',
            controller: ('addAccount', ['$scope','CRUD','$modal','$modalInstance','toaster', addAccount])
        });
    }

    function addAccount($scope, CRUD,$modal,$modalInstance,toaster) {
        $scope.account = {};
        $scope.addnewAccount = function () {
            CRUD.store('xero/addnewaccount', $.param($scope.account)).then(function (response) {
                toaster.pop('success','','Account added successfully.');
                $modalInstance.dismiss('cancel');
                $state.go('app.xero_api', {}, {reload: true});
            }).catch(function (response) {
                $modalInstance.dismiss('cancel');
                toaster.pop('error','','Whoops,Something went wrong!');
            });
        }
    };


    $scope.changeAccount = function (xeroAccount) {
        if(xeroAccount != '') {
        CRUD.store('xero/updateuseraccount',$.param({salesAccount : xeroAccount})).then(function (response) {
            console.log($scope.xeroAccount);
            $state.go('app.xero_api', {}, {reload: true});
        }).catch(function (response) {
            console.log('reload start567');
            $state.go('app.xero_api', {}, {reload: true});
        });
        }
    }
}]);