'use strict';

/* Controllers */

app.controller('MainAppController', ['$scope', '$rootScope', '$http', '$state', '$localStorage', 'CRUD', function ($scope, $rootScope, $http, $state, $localStorage, CRUD) {

    $scope.logoutUser = function() {
        $localStorage.$reset();
        $rootScope.user = '';
        $state.go('access.signin');
    }
    
}]);