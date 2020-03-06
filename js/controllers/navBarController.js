'use strict';

/* Controllers */
// signin controller
app.controller('navBarController', ['$scope', '$routeScope','$localStorage', function ($scope,$routeScope,$localStorage) {

    $scope.isLoggedIn=false;
    $scope.username='';


    $scope.getUser=function(){
        if($routeScope.user != '' && $routeScope.user != undefined)
        {
            $scope.username=$routeScope.user.FullName;
        }

    }

}])
;