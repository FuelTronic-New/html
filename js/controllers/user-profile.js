'use strict';

/* Controllers */
// signin controller
app.controller('ProfileController', ['$scope', '$rootScope', '$http', '$state','CRUD', '$localStorage', '$filter', 'toaster', function ($scope, $rootScope, $http, $state,CRUD, $localStorage, $filter, toaster) {
    var token = $localStorage.token;
    $scope.getUser = function () {

        CRUD.getWithData('user').then(function (response) {
            $scope.userData = response.data;
            //$localStorage.user=response.data;
            $rootScope.user=JSON.parse(JSON.stringify(response.data));
        }).catch(function (errormsg) {

        });
    }
    $scope.updateProfile = function () {
        var params = {
            name: $scope.userData.name,
            //email: $scope.userData.email,
            //password: $scope.userData.password,
        };

        $scope.update('user/update', params);
    }
    $scope.updatePassword = function () {
        var params = {
            old_password: $scope.old_password,
            password: $scope.password,
            password_confirmation: $scope.password_confirmation,
        };

        $scope.update('user/change-password', params);
    }
    $scope.update = function (url, params) {
        CRUD.update(url,'', $.param(params)).then(function (response) {
            if (response.status == 200) {
                $scope.getUser();
                toaster.success('User',response.data.message);
            }
        }).catch(function (errormsg) {
            if(errormsg.data.validation_errors) {
                angular.forEach(errormsg.data.validation_errors, function (value, key) {
                    toaster.error('Error', value[0] || '');
                });
            }else{
                toaster.error('Error', errormsg.data.message || '');
            }


        });
    }
}])
