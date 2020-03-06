'use strict';

app.controller('PasswordController', ['$scope', '$http', '$state', '$rootScope','$location', 'CRUD','toaster', function ($scope, $http, $state, $rootScope,$location, CRUD,toaster) {
    $scope.errormsg_status = false;
    var password_email = 'password/email';
    var password_reset = 'password/reset';

    $scope.authError = null;
    $scope.postEmail = function () {
        $scope.authError = null;
        var param = {email: $scope.email};
        CRUD.storeWithoutToken(password_email, $.param(param)).then(function (response) {
            $scope.isCollapsed = false;

            if (!response.data) {
                toaster.pop('success', "User", response.data.message);
            } else {
                $state.go('access.signin');
            }
        }, function (errormsg) {
            toaster.pop('error', "", errormsg.data.message);
        });
    };

    $scope.postReset = function () {
        $scope.authError = null;
        var param = {email: $scope.email,token:$location.search().token,password:$scope.password,password_confirmation:$scope.password_confirmation};
        CRUD.storeWithoutToken(password_reset, $.param(param)).then(function (response) {
            $scope.isCollapsed = false;

            if (!response.data) {
                //$scope.errormsg_status = true;
                toaster.pop('error', "", response);
                //$scope.authError = response;
            } else {
                $state.go('access.signin');
            }
        }, function (errormsg) {
            toaster.pop('error', "", errormsg.data.message);
            //$scope.authError = errormsg.data.message;
        });
    };


}]);