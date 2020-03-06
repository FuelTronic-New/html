'use strict';

/* Controllers */
// signin controller
app
    .controller('PayEasyController', ['$scope', '$http', '$state', '$localStorage', 'CRUD','toaster', function ($scope, $http, $state, $localStorage, CRUD,toaster) {

        var payeasycreds_url = 'getpayeasycredentials';

        $scope.getPayeasyCreds=function() {
            CRUD.getAll(payeasycreds_url).then(success).catch(failed);

            function success(res) {
                //console.log(res.data.hmackey)
                $scope.payeasy = res.data;
            }

            function failed(error) {
                $scope.payeasy={};
            }
        }

        $scope.formSubmit = function () {
            CRUD.update('savepayeasycredentials', '', $.param($scope.payeasy)).then(function (res) {
                toaster.pop('success', "", 'Updated Successfully');
            }).catch(function (res) {

            });
        };
        $scope.formReset= function () {
            $scope.getPayeasyCreds();
        }
    }]);