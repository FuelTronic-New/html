'use strict';

/* Controllers */
// signin controller
app
    .controller('XeroInvoiceController', ['$scope', '$http', '$state', '$localStorage', 'CRUD', function ($scope, $http, $state, $localStorage, CRUD) {

        var url = 'xero/payeasyurl';
        $scope.data = {};
        $scope.getBasic=function() {
            CRUD.getAll(url).then(success).catch(failed);

            function success(res) {
                //console.log(res.data.hmackey)
                $scope.data = res.data;
            }

            function failed(error) {
                $scope.payeasy={};
            }
        }
    }]);