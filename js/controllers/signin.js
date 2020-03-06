'use strict';

/* Controllers */
// signin controller
app.controller('SigninFormController', ['$scope', '$http', '$state','$localStorage','$rootScope', function ($scope,
                                                                               $http, $state, $localStorage,$rootScope) {
    $scope.user = {};
    $scope.authError = null;
    $rootScope.loading = false;
    $scope.login = function () {

        $scope.authError = null;
        // Try to login
        $http({
            method: 'POST',
            url: $rootScope.API_BASE_URL+'user/authenticate',
            data: $.param({email: $scope.user.email, password: $scope.user.password}),
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            //data: {email: $scope.user.email, password: $scope.user.password}
        }).then(function (response) {
            $localStorage.token = response.data.token;
            $localStorage.user = response.data.user;
            $rootScope.user = response.data.user;
            $scope.$storage = $localStorage;
            //console.log($scope.$storage);return false;
            $state.go('app.dashboard');

        }, function (errormsg) {
            $scope.authError = errormsg.data.error;
        });
    };
}])
;