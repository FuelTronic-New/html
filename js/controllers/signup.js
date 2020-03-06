'use strict';

// signup controller
app.controller('SignupFormController', ['$scope', '$http', '$state','$rootScope','CRUD','toaster', function($scope, $http, $state,$rootScope,CRUD,toaster) {
    $scope.user = {};
    $scope.authError = null;
    $rootScope.loading = false;
    $scope.signup = function() {
      $scope.authError = null;
      // Try to create
        var param={name: $scope.user.name, email: $scope.user.email, password: $scope.user.password};
        CRUD.storeWithoutToken('user/create',$.param(param)).then(function (response) {

            if(response.data.id != '')
            {
                toaster.pop('success','','Registration Successfully!')
                $state.go('access.signin');
            }
            if (!response.data.id) {
                toaster.pop('error', "", 'whoops something wrong');
            }

        }, function (res) {

            if(res.status == 400)
            {
                angular.forEach(res.data.validation_errors, function (value, key) {
                    toaster.pop('error', '', value[0]);
                });

            }
            $scope.authError = 'Server Error';
        });

    };
  }])
 ;