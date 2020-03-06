'use strict';

/* Controllers */
// ContactController controller
app
    .controller('ContactController', ['$scope', '$http', '$state', '$localStorage', 'CRUD', 'toaster', '$location', function ($scope, $http, $state, $localStorage, CRUD, toaster, $location) {

        var vm = this;
        vm.errorMessage = '';
        $scope.contact = {
            action: 'contactUs',
            task: 'send',
            captcha_confirmation: 'ECI'
        };

        $scope.save = function (isValid) {
            if (isValid) {
                var data = $scope.contact;
                CRUD.update('contactus', '', $.param(data)).then(function (res) {
                    toaster.pop('success', "", 'Form Submitted.');
                    $scope.contact = {
                        action: 'contactUs',
                        task: 'send',
                        captcha_confirmation: 'ECI'
                    };
                }).catch(function (res) {
                    if (res.status == 400) {
                        angular.forEach(res.data.validation_errors, function (value, key) {
                            toaster.pop('error', '', value[0]);
                        });
                    }
                });
            }
        }
    }]);